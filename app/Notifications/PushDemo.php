<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PushDemo extends Notification
{

    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    public function via($notifiable)
    {
        return ['database', 'broadcast', WebPushChannel::class];
    }

    public function toArray($notifiable)
    {
        return [
            "title" => "Hello World!",
            "body" => "Thankyou for trusting us",
            "action_url" => "https://laravel.com",
            "created" => Carbon::now()->toIso8601String()
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('I am Notification Title')
            ->icon('/notification-icon.png')
            ->body('Great, Push Notifications work!')
            ->action('View App', "view_app")
            ->data([
                'id' => $notification->id,
                'urls' => [
                    "view_app" => "https://laravel.com"
                ]
            ]);
    }
    
}