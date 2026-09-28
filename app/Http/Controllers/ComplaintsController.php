<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\ComplaintSubmitted;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ComplaintsController extends Controller
{
    private const NOTIFY_ONCE_PER_MINUTES = 60;

    /**
     * Show the form for creating a new resource.
     *
     * @return array<string, mixed>
     */
    public function reasons(): array
    {
        return [
            'reasons' => getSiteConfig('complaints'),
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): Response
    {
        // Validate user input
        $request->validate([
            'complainable_type' => 'required|string|in:writings,comments,users',
            'complainable_id' => 'required|integer',
            'reasons' => 'required|array|min:1|max:10',
            'reasons.*' => ['string', Rule::in($this->allowedReasons())],
            'comment' => 'nullable|string|max:255',
        ]);

        // Resolve the reported resource
        $id = (int) $request->input('complainable_id');
        $complainable = match ((string) $request->input('complainable_type')) {
            'writings' => Writing::find($id),
            'comments' => Comment::find($id),
            'users' => User::find($id),
            default => null,
        };

        if ($complainable === null) {
            abort(404);
        }

        // One email per reported item and hour is enough to bring admins in;
        // later complaints are still recorded for them to review
        $isFirstRecentComplaint = Complaint::whereMorphedTo('complainable', $complainable)
            ->where('created_at', '>', Carbon::now()->subMinutes(self::NOTIFY_ONCE_PER_MINUTES))
            ->doesntExist();

        $complaint = new Complaint;
        $complaint->complainable()->associate($complainable);
        $complaint->reasons = $request->input('reasons');
        $complaint->comment = $request->input('comment');
        $complaint->save();

        if ($isFirstRecentComplaint === true) {
            $recipients = getSiteConfig('emails.admin');
            Notification::route('mail', $recipients)->notify(new ComplaintSubmitted);
        }

        return response()->noContent();
    }

    /**
     * Mark a complaint as dealt with, noting what was done, then return to the admin table.
     */
    public function close(Complaint $complaint): RedirectResponse
    {
        request()->validate([
            'closed_comment' => 'nullable|string|max:255',
        ]);

        $complaint->closed_at = Carbon::now();
        $complaint->closed_comment = request('closed_comment');
        $complaint->save();

        Inertia::flash(['message' => 'complaints.complaint-closed', 'color' => 'success']);

        return back();
    }

    /**
     * The reasons a user may pick, whether they are configured as plain
     * strings or as value/label pairs.
     *
     * @return array<int, string>
     */
    private function allowedReasons(): array
    {
        return collect((array) getSiteConfig('complaints'))
            ->map(fn (mixed $reason): mixed => is_array($reason) ? ($reason['value'] ?? null) : $reason)
            ->filter(fn (mixed $reason): bool => is_string($reason))
            ->values()
            ->all();
    }
}
