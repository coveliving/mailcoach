<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\WelcomeNotification\WelcomeNotification;

class QueuedWelcomeNotification extends WelcomeNotification implements ShouldQueue
{
    use Queueable;
}
