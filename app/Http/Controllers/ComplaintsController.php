<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Complaint;
use App\Notifications\ComplaintSubmitted;
use App\Models\User;
use App\Models\Writing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ComplaintsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function reasons()
    {
        return  [
            'reasons' => getSiteConfig('complaints'),
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Complaint $complaint)
    {
        // Validate user input
        request()->validate([
            'complainable_type' => 'required|string|in:writings,comments,users',
            'complainable_id' => 'required|integer',
            'reasons' => 'required|array|min:1',
            'comment' => 'nullable|string|max:255',
        ]);

        $id = request('complainable_id');

        // Update accordingly
        if ('writings' == request('complainable_type')) {
            $complaint->complainable()->associate(Writing::find($id));
        } elseif ('comments' == request('complainable_type')) {
            $complaint->complainable()->associate(Comment::find($id));
        } elseif ('users' == request('complainable_type')) {
            $complaint->complainable()->associate(User::find($id));
        }

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
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Complaint $complaint)
    {
        // Ensure user has the proper permission
        $this->authorize('update', $complaint);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
