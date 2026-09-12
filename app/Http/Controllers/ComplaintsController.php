<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\ComplaintSubmitted;
use Illuminate\Support\Facades\Notification;

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
    public function store(): array
    {
        $complaint = new Complaint;

        // Validate user input
        request()->validate([
            'complainable_type' => 'required|string|in:writings,comments,users',
            'complainable_id' => 'required|integer',
            'reasons' => 'required|array|min:1',
            'comment' => 'nullable|string|max:255',
        ]);

        $id = (int) request('complainable_id');
        $type = (string) request('complainable_type');

        // Resolve the reported resource
        $complainable = match ($type) {
            'writings' => Writing::find($id),
            'comments' => Comment::find($id),
            'users' => User::find($id),
            default => null,
        };

        if ($complainable === null) {
            abort(404);
        }

        $complaint->complainable()->associate($complainable);

        $complaint->reasons = request('reasons');
        $complaint->comment = request('comment');
        $complaint->save();

        // Schedule email notification
        $recipients = getSiteConfig('emails.admin');
        Notification::route('mail', $recipients)->notify(new ComplaintSubmitted);

        return [];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(): void
    {
        //
    }
}
