<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Mailcoach\Domain\Audience\Enums\TagType;
use Spatie\Mailcoach\Domain\Audience\Models\EmailList;
use Spatie\Mailcoach\Domain\Audience\Models\Subscriber;
use Spatie\Mailcoach\Domain\Audience\Models\Tag;

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
        $tagName = 'ex-tenant';

        EmailList::query()->each(function (EmailList $emailList) use ($tagName) {
            $tag = Tag::query()
                ->where('email_list_id', $emailList->id)
                ->named($tagName)
                ->first()
                ?? Tag::create([
                    'name' => $tagName,
                    'email_list_id' => $emailList->id,
                    'type' => TagType::Default,
                ]);

            Subscriber::query()
                ->where('email_list_id', $emailList->id)
                ->withExtraAttributes('move_out_date', '!=', null)
                ->withExtraAttributes('move_out_date', '<', now()->toDateString())
                ->whereDoesntHave('tags', fn ($query) => $query->where('mailcoach_tags.id', $tag->id))
                ->select('id')
                ->chunkById(1000, function ($subscribers) use ($tag) {
                    DB::table('mailcoach_email_list_subscriber_tags')->insertOrIgnore(
                        $subscribers->map(fn (Subscriber $subscriber) => [
                            'subscriber_id' => $subscriber->id,
                            'tag_id' => $tag->id,
                        ])->all()
                    );
                });
        });
    }
}
