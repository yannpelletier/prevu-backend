<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if ( $request->get('read')) {
            $user->unreadNotifications->markAsRead();
            $notifications = $user->notifications;
            return NotificationResource::collection($notifications);
        } else {
            return [
                'count' => $user->notifications()->count(),
                'unread_count' => $user->unreadNotifications()->count(),
            ];
        }
    }

    /**
     * Remove the asset from storage.
     *
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function deleteAll(Request $request)
    {
        $user = Auth::user();
        $user->notifications()->delete();
    }
}
