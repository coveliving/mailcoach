<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Mailcoach\Domain\Audience\Models\Subscriber;
use Spatie\Mailcoach\Domain\Audience\Models\Tag;

class RemoveExTenantTag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-ex-tenant-tag';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will remove the ex-tenant tag from tenants whose move out date is in the future';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Tag::query()->named('ex-tenant')->each(function (Tag $tag) {
            // ponytail: single DELETE, chunk it if the future-move-out set ever gets big enough to lock
            DB::table('mailcoach_email_list_subscriber_tags')
                ->where('tag_id', $tag->id)
                ->whereIn('subscriber_id', Subscriber::query()
                    ->where('email_list_id', $tag->email_list_id)
                    ->withExtraAttributes('move_out_date', '>', now()->toDateString())
                    ->select('id')
                )
                ->delete();
        });
    }
}
