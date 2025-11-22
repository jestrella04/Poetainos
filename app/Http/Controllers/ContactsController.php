<?php

namespace App\Http\Controllers;

use App\Notifications\ContactFormSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class ContactsController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('forms/PoContactForm', [
            'meta' => [
                'title' => __('Contact form')
            ]
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
            'key' => 'required|string|min:1',
            'captcha' => 'required|captcha_api:' . request('key') . ',math'
        ]);

        $name = request('name');
        $email = request('email');
        $subject = request('subject');
        $message = request('message');

        // Schedule email notification
        $recipients = getSiteConfig('emails.admin');
        Notification::route('mail', $recipients)->notify(new ContactFormSubmitted($name, $email, $subject, $message));

        // Redirect back to the contact form
        return [];
    }

    public function reloadCaptcha()
    {
        return response()->json(['captcha' => captcha_src()]);
    }
}
