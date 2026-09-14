<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Mailcoach\Domain\Audience\Models\EmailList;
use Spatie\Mailcoach\Domain\Audience\Models\Subscriber;

class SyncCommunityPlusMembersJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public const LIST_NAME = 'SG Community Plus Members';

    /**
     * @param  array<int, array{email: string, first_name: ?string, last_name: ?string}>  $subscribers
     */
    public function __construct(public array $subscribers) {}

    public function handle(): void
    {
        $emailList = EmailList::firstOrCreate(['name' => self::LIST_NAME]);

        Subscriber::query()
            ->where('email_list_id', $emailList->id)
            ->subscribed()
            ->eachById(fn (Subscriber $subscriber) => $subscriber->unsubscribe(), 500);

        foreach ($this->subscribers as $subscriber) {
            $emailList->subscribeSkippingConfirmation($subscriber['email'], [
                'first_name' => $subscriber['first_name'] ?? null,
                'last_name' => $subscriber['last_name'] ?? null,
            ]);
        }
    }
}
