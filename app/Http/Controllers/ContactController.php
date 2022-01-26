<?php

namespace App\Http\Controllers;

use App\Contact;
use App\Events\ContactSent;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate(['message' => 'required|max:10000|min:1', 'full_name' => 'max:255', 'email' => 'required|max:255|email']);
        $contact = Contact::create($validatedData);
        event(new ContactSent($contact));
    }
}
