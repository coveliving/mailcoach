<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Mailcoach\Domain\Audience\Models\EmailList;
use Spatie\Mailcoach\Domain\Audience\Models\Subscriber;

class TagMovedOutTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tag-moved-out-tenants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will add ex-tenant tag to tenants who have moved out';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        EmailList::query()->each(function (EmailList $emailList) {
            Subscriber::query()
                ->where('email_list_id', $emailList->id)
                ->withExtraAttributes('move_out_date', '!=', null)
                ->withExtraAttributes('move_out_date', '<', now()->toDateString())
                ->whereDoesntHave('tags', fn ($query) => $query->named('ex-tenant'))
                ->chunkById(500, fn ($subscribers) => $subscribers->each->addTag('ex-tenant'));
        });
    }
}
