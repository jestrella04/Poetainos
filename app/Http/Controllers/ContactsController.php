<?php

namespace App\Http\Controllers;

use App\Notifications\ContactFormSubmitted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class ContactsController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('forms/PoContactForm', [
            'meta' => [
                'title' => getPageTitle([__('Contact form')]),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return array<int, mixed>
     */
    public function store(Request $request): array
    {
        // The captcha key travels inside the rule string, so keep only the characters a captcha key is made of
        $captchaKey = (string) preg_replace('/[^A-Za-z0-9.\/$]/', '', (string) $request->input('key'));

        // Validate user input
        $request->validate([
            'name' => 'required|string|min:3|max:40',
            'email' => 'required|string|email|max:45',
            'subject' => 'required|string|min:3|max:40',
            'message' => 'required|string|min:100|max:2000',
            'key' => 'required|string|min:1',
            'captcha' => 'required|captcha_api:'.$captchaKey.',math',
        ]);

        $name = (string) $request->input('name');
        $email = (string) $request->input('email');
        $subject = (string) $request->input('subject');
        $message = (string) $request->input('message');

        // Schedule email notification
        $recipients = getSiteConfig('emails.admin');
        Notification::route('mail', $recipients)->notify(new ContactFormSubmitted($name, $email, $subject, $message));

        // Redirect back to the contact form
        return [];
    }

    public function reloadCaptcha(): JsonResponse
    {
        return response()->json(['captcha' => captcha_src()]);
    }
}
