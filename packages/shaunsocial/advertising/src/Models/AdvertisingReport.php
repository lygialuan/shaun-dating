<?php


namespace Packages\ShaunSocial\Advertising\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingReportStatus;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingStatisticType;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingStatus;
use Packages\ShaunSocial\Advertising\Notification\AdvertisingReportNotification;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class AdvertisingReport extends Model
{
    use HasCacheQueryFields, HasCachePagination;

    protected $fillable = [
        'advertising_id',
        'view_count',
        'click_count',
        'date',
        'status',
        'view_amount',
        'click_amount',
        'total_amount',
        'currency',
        'check_done'
    ];

    public function getListCachePagination()
    {
        return [
            'advertising_report_'.$this->advertising_id,
        ];
    }


    public function getListFieldPagination()
    {
        return [
            'status'
        ];
    }

    protected $cacheQueryFields = [
        'id',
        'date'
    ];

    protected $casts = [
        'status' => AdvertisingReportStatus::class,
        'date' => 'date'
    ];

    public function getAdvertising()
    {
        return Advertising::findByField('id', $this->advertising_id);;
    }

    public function canAddStatistic()
    {
        return $this->status == AdvertisingReportStatus::PROCESS;
    }

    public function getStatistic()
    {
        $viewCount = AdvertisingStatistic::where('advertising_id', $this->advertising_id)->whereRaw("DATE_FORMAT(created_at, '%Y %m %d') = ?", [date('Y m d', strtotime($this->date))])->where('type', AdvertisingStatisticType::VIEW)->count();
        $clickCount = AdvertisingStatistic::where('advertising_id', $this->advertising_id)->whereRaw("DATE_FORMAT(created_at, '%Y %m %d') = ?", [date('Y m d', strtotime($this->date))])->where('type', AdvertisingStatisticType::CLICK)->count();

        $advertising = $this->getAdvertising();
        $viewAmount = $viewCount * $advertising->amount_per_view;
        $clickAmount = $clickCount * $advertising->amount_per_click;
        $total = $viewAmount + $clickAmount;

        return [$viewCount, $clickCount, $viewAmount, $clickAmount, $total];
    }

    public function canStop()
    {
        $advertising = $this->getAdvertising();
        [$viewCount, $clickCount, $viewAmount, $clickAmount, $total] = $this->getStatistic();
        return $total >= $advertising->daily_amount;
    }

    public function onStop()
    {
        $this->update([
            'status' => AdvertisingReportStatus::STOP,
            'check_done' => false
        ]);

        $advertising = $this->getAdvertising();
        $advertising->update([
            'date_stop' => convertDateToInteger($this->date)
        ]);
    }

    public static function getReportByAdvertisingAndDate($advertisingId, $date)
    {
        return Cache::remember(self::getCacheAdvertisingAndDateKey($advertisingId, $date), config('shaun_core.cache.time.model_query'), function () use ($advertisingId, $date) {
            $report = self::where('advertising_id', $advertisingId)->where('date', $date)->first();

            return is_null($report) ? false : $report;
        });
    }

    public static function getCacheAdvertisingAndDateKey($advertisingId, $date)
    {
        return 'advertising_report_'.$advertisingId.'_'.$date;
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheAdvertisingAndDateKey($this->advertising_id, date('Y-m-d', strtotime($this->date))));
    }

    public function onDone($force = false)
    {
        if ($this->status == AdvertisingReportStatus::DONE) {
            return;
        }

        [$viewCount, $clickCount, $viewAmount, $clickAmount, $total] = $this->getStatistic();
        $advertising = $this->getAdvertising();
        if ($total > $advertising->daily_amount) {
            $total = $advertising->daily_amount;
        }
        $this->update([
            'view_count' => $viewCount,
            'click_count' => $clickCount,
            'view_amount' => $viewAmount,
            'click_amount' => $clickAmount,
            'total_amount' => $total,
            'status' => AdvertisingReportStatus::DONE,
            'check_done' => false
        ]);
        if (! $force) {
            $advertising->update([
                'check_done' => true,
                'view_count' => $advertising->view_count + $viewCount,
                'click_count' => $advertising->click_count + $clickCount,
                'total_delivery_amount' => $advertising->total_delivery_amount + $total
            ]);
            if ($advertising->status != AdvertisingStatus::STOP && setting('shaun_advertising.enable')) {
                Notification::send($advertising->getUser(), $advertising->getUser(), AdvertisingReportNotification::class, $advertising, ['is_system' => true, 'params' => ['view_count' => $viewCount, 'click_count' => $clickCount]], 'shaun_advertising', false);
            }
        }
    }

    public static function booted()
    {
        parent::booted();

        static::created(function ($report) {
            $report->clearCache();
        });

        static::updated(function ($report) {
            $report->clearCache();
        });
    }
}
