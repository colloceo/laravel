<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * About Us Page
     */
    public function about()
    {
        return view('pages.about');
    }

    /**
     * Privacy Policy Page
     */
    public function privacy()
    {
        return view('pages.privacy');
    }

    /**
     * Contact Us Page (GET)
     */
    public function contactShow()
    {
        return view('pages.contact');
    }

    /**
     * Handle Contact Form Submission (POST)
     */
    public function contactSend(Request $request)
    {
        // 1. Validate data
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // 2. (Optional) Send Email using Mailables
        // Mail::to(config('mail.from.address'))
        //     ->send(new ContactFormMail($validated));

        // 3. Redirect back with a success message
        return back()->with('success', 'Thank you for your message! We will get back to you shortly.');
    }
}
