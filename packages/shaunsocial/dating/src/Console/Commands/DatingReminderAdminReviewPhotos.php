<?php
namespace Packages\ShaunSocial\Dating\Console\Commands;

use Illuminate\Console\Command;
use Packages\ShaunSocial\Dating\Support\Facades\Dating;

class DatingReminderAdminReviewPhotos extends Command
{
    protected $signature = 'shaun_dating:reminder_admin_review_photos';
    protected $description = 'Reminder admin review photos';

    protected $datingRepository;

    public function handle()
    {
        Dating::reminderAdminReviewPhotos();
    }
}