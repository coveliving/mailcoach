<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Mailcoach\Domain\Audience\Models\EmailList;
use Spatie\Mailcoach\Domain\Audience\Models\Subscriber;
use Spatie\Mailcoach\Domain\Audience\Support\EmailAddressNormalizer;

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

        // Emails that had already opted out before this sync ran - never force-resubscribe these.
        $optedOut = Subscriber::query()
            ->where('email_list_id', $emailList->id)
            ->unsubscribed()
            ->pluck('email')
            ->all();

        Subscriber::query()
            ->where('email_list_id', $emailList->id)
            ->subscribed()
            ->eachById(fn (Subscriber $subscriber) => $subscriber->unsubscribe(), 500);

        foreach ($this->subscribers as $subscriber) {
            $email = EmailAddressNormalizer::normalize($subscriber['email']);

            if (in_array($email, $optedOut, true)) {
                continue;
            }

            $emailList->subscribeSkippingConfirmation($email, [
                'first_name' => $subscriber['first_name'] ?? null,
                'last_name' => $subscriber['last_name'] ?? null,
            ]);
        }
    }
}
