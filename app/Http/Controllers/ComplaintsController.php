<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\ComplaintSubmitted;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;

class ComplaintsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function reasons()
    {
        return [
            'reasons' => getSiteConfig('complaints'),
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store()
    {
        $complaint = new Complaint;

        // Validate user input
        request()->validate([
            'complainable_type' => 'required|string|in:writings,comments,users',
            'complainable_id' => 'required|integer',
            'reasons' => 'required|array|min:1',
            'comment' => 'nullable|string|max:255',
        ]);

        $id = request('complainable_id');

        // Resolve the reported resource
        $complainable = match (request('complainable_type')) {
            'writings' => Writing::find($id),
            'comments' => Comment::find($id),
            'users' => User::find($id),
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit(Complaint $complaint)
    {
        // Ensure user has the proper permission
        $this->authorize('update', $complaint);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
