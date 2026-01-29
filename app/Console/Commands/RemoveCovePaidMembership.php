<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Mailcoach\Domain\Audience\Models\EmailList;
use Spatie\Mailcoach\Domain\Audience\Models\Subscriber;

class RemoveCovePaidMembership extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-cove-paid-membership';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $emailListName = 'SG Tenants';
        $tagName = 'cove-paid-membership';

        $emailList = EmailList::query()->where('name', $emailListName)->first();
        $tag = $emailList->tags()->where('name', $tagName)->first();
        $tag->subscribers()
            ->cursor()
            ->each(fn (Subscriber $subscriber) => $subscriber->removeTag($tagName));
    }
}
