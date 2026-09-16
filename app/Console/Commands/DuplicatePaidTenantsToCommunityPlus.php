<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Mailcoach\Domain\Audience\Models\EmailList;
use Spatie\Mailcoach\Domain\Audience\Models\Subscriber;

class DuplicatePaidTenantsToCommunityPlus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:duplicate-paid-tenants-to-community-plus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Duplicates subscribed SG Tenants members tagged cove-paid-membership into SG Community Plus Members, copying their attributes and tags';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $sgTenants = EmailList::where('name', 'SG Tenants')->firstOrFail();
        $communityPlus = EmailList::firstOrCreate(['name' => 'SG Community Plus Members']);

        Subscriber::query()->where('email_list_id', $communityPlus->id)->delete();

        Subscriber::query()
            ->where('email_list_id', $sgTenants->id)
            ->subscribed()
            ->whereHas('tags', fn ($query) => $query->named('cove-paid-membership'))
            ->eachById(function (Subscriber $subscriber) use ($communityPlus) {
                $tags = $subscriber->tags->pluck('name')
                    ->reject(fn (string $name) => $name === 'cove-paid-membership')
                    ->all();

                Subscriber::createWithEmail($subscriber->email, $subscriber->only(['first_name', 'last_name']))
                    ->withExtraAttributes($subscriber->extra_attributes->toArray())
                    ->tags($tags)
                    ->skipConfirmation()
                    ->subscribeTo($communityPlus);
            }, 500);
    }
}
