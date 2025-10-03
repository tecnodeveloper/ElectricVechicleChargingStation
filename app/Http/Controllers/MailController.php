<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;


class MailController extends Controller
{
    public function sendEmail()
    {
        $toEmail = "recluzedev@gmail.com";
        $subject = "Nexl Tech";
        $message = "Nothing is here";
        $request = Mail::to($toEmail)->send(new WelcomeEmail($message, $subject));
        dd($request);
    }
}
