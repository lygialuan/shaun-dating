<?php


namespace Packages\ShaunSocial\Advertising\Console\Commands;

use Illuminate\Console\Command;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingReportStatus;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingStatus;
use Packages\ShaunSocial\Advertising\Models\Advertising;
use Packages\ShaunSocial\Advertising\Models\AdvertisingReport;
use Packages\ShaunSocial\Advertising\Notification\AdvertisingActiveNotification;
use Packages\ShaunSocial\Advertising\Notification\AdvertisingCompleteNotification;
use Packages\ShaunSocial\Core\Support\Facades\Notification;

class AdvertisingReportRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_advertising:report_run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run report for advertising.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //check send notify ads
        $advertisings = Advertising::where('status', AdvertisingStatus::ACTIVE)->where('notify_sent', false)->where('start', '<=', now()->format('Y-m-d'))->limit(setting('feature.item_per_page'))->get();
        $advertisings->each(function($advertising) {
            if ($advertising->status != AdvertisingStatus::STOP && setting('shaun_advertising.enable')) {
                Notification::send($advertising->getUser(), $advertising->getUser(), AdvertisingActiveNotification::class, $advertising, ['is_system' => true], 'shaun_advertising', false);
            }
            $advertising->update(['notify_sent'=> true]);
        });

        //check report per day
        $reports = AdvertisingReport::whereIn('status', [AdvertisingReportStatus::PROCESS, AdvertisingReportStatus::STOP])->where('date', '<', now()->format('Y-m-d'))->limit(setting('feature.item_per_page'))->get();
        $reports->each(function($report) {
            $report->onDone();
        });
        
        //check report after done
        $advertisings = Advertising::where('check_done', true)->limit(setting('feature.item_per_page'))->get();
        $advertisings->each(function($advertising) {
            if ($advertising->canDone()) {
                $result = $advertising->onDone();
                if ($result['status'] && $advertising->status != AdvertisingStatus::STOP && setting('shaun_advertising.enable')) {
                    Notification::send($advertising->getUser(), $advertising->getUser(), AdvertisingCompleteNotification::class, $advertising, ['is_system' => true], 'shaun_advertising', false);
                } 
            }
            $advertising->update(['check_done' => false]);
        });

        //check report real time
        $reports = AdvertisingReport::where('status', AdvertisingReportStatus::PROCESS)->where('check_done', true)->limit(setting('feature.item_per_page'))->get();
        $reports->each(function($report) {
            if ($report->canStop()) {
                $report->onStop();
            } else {
                $report->update(['check_done' => false]);
            }
        });
    }
}
