<?php

namespace App\Http\Controllers;

use App\Notifications\ContactFormSubmitted;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ContactsController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $params = [
            'title' => getPageTitle([
                __('Contact form'),
            ]),
        ];

        return view('contact.index', [
            'params' => $params,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate user input
        request()->validate([
            'name' => 'required|string|min:3|max:40',
            'email' => 'required|string|email|max:45',
            'subject' => 'required|string|min:3|max:40',
            'message' => 'required|string|min:100',
            'captcha' => 'required|captcha'
        ]);

        $name = request('name');
        $email = request('email');
        $subject = request('subject');
        $message = request('message');

        // Schedule email notification
        $recipients = getSiteConfig('emails.admin');
        Notification::route('mail', $recipients)->notify(new ContactFormSubmitted($name, $email, $subject, $message));

        // Set session flash message
        $flash = __('Your message was successfully scheduled, and will be sent shortly.');
        $request->session()->flash('flash', $flash);

        // Redirect back to the contact form
        return redirect(route(('contact.create')));
    }

    public function reloadCaptcha()
    {
        return response()->json(['captcha'=> captcha_src()]);
    }
}
