<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\ComplaintSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class ComplaintsController extends Controller
{
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
     *
     * @return array<int, mixed>
     */
    public function store(Request $request): array
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

        $complaint = new Complaint;
        $complaint->complainable()->associate($complainable);
        $complaint->reasons = $request->input('reasons');
        $complaint->comment = $request->input('comment');
        $complaint->save();

        // Schedule email notification
        $recipients = getSiteConfig('emails.admin');
        Notification::route('mail', $recipients)->notify(new ComplaintSubmitted);

        return [];
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
