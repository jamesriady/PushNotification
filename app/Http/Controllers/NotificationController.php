<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use Notification;
use App\Notifications\PushDemo;
use App\Events\ReadAll;
use App\Events\Read;
use NotificationChannels\WebPush\PushSubscription;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct() {
    	$this->middleware('auth')->except('dismiss');
    }

    public function index() {
    	$user = Auth::user();
    	$query = $user->unreadNotifications();
    	$notifications = $query->get()->each(function($n) {
    		$n->created = $n->created_at->toIso8601String();
    	});
    	$total = $user->unreadNotifications->count();
    	return response()->json([
    		"notifications" => $notifications,
    		"total" => $total
    	], 200);
    }

    public function sendNotification() {
        // Notification::send(User::all(), new PushDemo);
    	Auth::user()->notify(new PushDemo);

    	return response()->json("Notification sent", 200);
    }

    public function readAll() {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        event(new ReadAll($user));
    }

    public function read($id) {
        $user = Auth::user();
        $notification = $user->unreadNotifications->where("id", $id)->first();
        $notification->markAsRead();
        event(new Read($user, $notification->id));
    }

    public function dismiss($id, Request $request) {
        $subscription = PushSubscription::findByEndpoint($request->endpoint);
        if (!$subscription) {
            return response()->json("Subscription not found!", 404);
        }
        $notification = $subscription->subscribable->unreadNotifications()->where("id", $id)->first();
        if (!$notification) {
            return response()->json("Notification not found!", 404);
        }
        $notification->markAsRead();
        event(new Read($subscription->subscribable, $notification->id));
    }
}
