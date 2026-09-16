<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Mailcoach\Domain\Audience\Models\EmailList;
use Spatie\Mailcoach\Domain\Audience\Models\Tag;

class DeleteCovePaidMembershipTag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-cove-paid-membership-tag';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes the cove-paid-membership tag from SG Tenants';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $sgTenants = EmailList::where('name', 'SG Tenants')->firstOrFail();

        Tag::query()
            ->where('email_list_id', $sgTenants->id)
            ->named('cove-paid-membership')
            ->delete();
    }
}
