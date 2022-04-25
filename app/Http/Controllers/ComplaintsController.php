<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Complaint;
use App\Notifications\ComplaintSubmitted;
use App\Reply;
use App\User;
use App\Writing;
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
    public function create($type, $id)
    {
        if (! in_array($type, ['writings', 'comments', 'replies', 'users'])) {
            abort(404);
        }

        $params = [
            'complainable_type' => $type,
            'complainable_id' => $id,
            'modal_id' => "complaint-{$type}-{$id}",
            'reasons' => getSiteConfig('complaints'),
        ];

        return view('complaints.create', [
            'params' => $params,
        ]);
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
            'complainable_type' => 'required|string|in:writings,comments,replies,users',
            'complainable_id' => 'required|integer',
            'reasons' => 'required|array',
            'comment' => 'nullable|string|max:255',
        ]);

        $id = request('complainable_id');

        // Update accordingly
        if ('writings' == request('complainable_type')) {
            $complaint->complainable()->associate(Writing::find($id));
        } elseif ('comments' == request('complainable_type')) {
            $complaint->complainable()->associate(Comment::find($id));
        } elseif ('replies' == request('complainable_type')) {
            $complaint->complainable()->associate(Reply::find($id));
        } elseif ('users' == request('complainable_type')) {
            $complaint->complainable()->associate(User::find($id));
        }

        $complaint->reasons = request('reasons');
        $complaint->comment = request('comment');
        $complaint->save();

        // Schedule email notification
        $recipients = getSiteConfig('emails.admin');
        Notification::route('mail', $recipients)->notify(new ComplaintSubmitted);

        return [
            'message' => __('We received your complaint request successfully and will be processing it soon.'),
            'id' => $complaint->id,
        ];
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
