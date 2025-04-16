<?php

namespace App\Http\Controllers\VitalAid;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{


    public function getNotifications(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'unread_notifications' => $user->unreadNotifications
        ]);
    }

    public function markNotificationAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

}