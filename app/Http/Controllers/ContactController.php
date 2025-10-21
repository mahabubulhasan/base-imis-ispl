<?php

// app/Http/Controllers/ContactController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        // Validate the form data
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'message' => __('required'),
        ]);

        // Prepare email data
        $emailData = [
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'messageContent' => $request->message,
        ];

        
        // Send email
        Mail::send('emails.contact', $emailData, function ($message) use ($emailData) {
            $message->to('mattermost@innovativesolution.com.np')
                    ->subject($emailData['subject'])
                    ->from($emailData['email'], $emailData['name']);
        });

        return back()->with('success', 'Your message has been sent successfully!');
    }
}
