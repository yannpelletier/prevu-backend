<?php

namespace App\Http\Controllers;

use App\Events\FeedbackSent;
use App\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['message' => 'required|max:10000|min:1']);
        Feedback::create(['message' => $request->get('message'), 'user_id' => Auth::user()->id]);
        event(new FeedbackSent(Auth::user(), $request->get('message')));
    }
}
