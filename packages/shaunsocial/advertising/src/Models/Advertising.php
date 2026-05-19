<?php


namespace Packages\ShaunSocial\Advertising\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingReportStatus;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingStatus;
use Packages\ShaunSocial\Core\Models\Hashtag;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Wallet\Support\Facades\Wallet;
use Packages\ShaunSocial\Wallet\Traits\HasItemFromSite;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingAgeType;
use Packages\ShaunSocial\Core\Models\City;
use Packages\ShaunSocial\Core\Models\Country;
use Packages\ShaunSocial\Core\Models\Gender;
use Packages\ShaunSocial\Core\Models\State;
use Packages\ShaunSocial\Core\Models\User;

class Advertising extends Model
{
    use HasCacheQueryFields, HasItemFromSite, IsSubject, HasUser, HasCachePagination;

    protected $fillable = [
        'user_id',
        'name',
        'sort_count',
        'post_id',
        'gender_id',
        'age_from',
        'age_to',
        'hashtags',
        'start',
        'end',
        'daily_amount',
        'vat',
        'total_delivery_amount',
        'amount_per_click',
        'amount_per_view',
        'check_done',
        'status',
        'currency',
        'timezone',
        'view_count',
        'click_count',
        'country_id',
        'state_id',
        'city_id',
        'zip_code',
        'notify_sent',
        'date_stop',
        'age_type'
    ];

    protected $cacheQueryFields = [
        'id',
        'post_id'
    ];

    public function getListCachePagination()
    {
        return [
            'advertising_all_'.$this->user_id,
            'advertising_active_'.$this->user_id,
            'advertising_stop_'.$this->user_id,
            'advertising_done_'.$this->user_id,
        ];
    }

    public function getGender()
    {
        if ($this->gender_id) {
            return Gender::findByField('id', $this->gender_id);
        }

        return null;
    }

    public function getListFieldPagination()
    {
        return [
            'name',
            'status',
            'total_delivery_amount',
            'amount_per_click',
            'amount_per_view'
        ];
    }

    protected $casts = [
        'status' => AdvertisingStatus::class,
        'start' => 'datetime',
        'end' => 'datetime',
        'notify_sent' => 'boolean',
        'age_type' => AdvertisingAgeType::class
    ];

    public function canEdit($viewerId)
    {
        if ($this->status == AdvertisingStatus::DONE) {
            return false;
        }

        if ($viewerId != $this->user_id) {
            return false;
        }

        return true;
    }

    public function canStop()
    {
        return $this->status == AdvertisingStatus::ACTIVE;
    }

    public function canEnable()
    {
        return $this->status == AdvertisingStatus::STOP && $this->getUser()->id;
    }

    public function canComplete()
    {
        return $this->status != AdvertisingStatus::DONE;
    }

    public function getUser()
    {
        $user = User::findByField('id', $this->user_id);
        return $user ?? getDeleteUser();
    }

    public function getPost()
    {
        return Post::findByField('id', $this->post_id);
    }

    public function getAmount($includeVat = true)
    {
        $day = subDate($this->start, $this->end);
        $amount = $day*$this->daily_amount;
        if ($includeVat) {
            $amount += ($amount*$this->vat) / 100;
        }
        return $amount;
    }

    public function getWalletTypeExtra()
    {
        return 'buy_advertising';
    }

    public function getWalletTypeExtraRefund()
    {
        return 'refund_advertising';
    }

    public function canAddStatistic()
    {
        if ($this->status == AdvertisingStatus::ACTIVE) {
            $time = time();
            if ($this->start->timestamp < $time && $time < $this->end->timestamp) {
                $date = date('Y-m-d');
                $advertisingReport = AdvertisingReport::getReportByAdvertisingAndDate($this->id, $date);
                return $advertisingReport->canAddStatistic();
            }
        }
        return false;
    }

    public function addCheckDoneForReport()
    {
        $date = date('Y-m-d');
        $advertisingReport = AdvertisingReport::getReportByAdvertisingAndDate($this->id, $date);
        if (! $advertisingReport->check_done) {
            $advertisingReport->update([
                'check_done' => true
            ]);
        }
    }

    public function canDone()
    {
        if ($this->status != AdvertisingStatus::DONE) {
            $count = AdvertisingReport::where('advertising_id', $this->id)->where('status', AdvertisingReportStatus::PROCESS)->count();
            return ! $count;
        }
        return false;
    }

    public function getRefundAmount()
    {
        return $this->total_delivery_amount;
    }

    public function onDone($force = false)
    {
        if ($this->status == AdvertisingStatus::DONE) {
            return;
        }

        $viewCount = AdvertisingReport::where('advertising_id', $this->id)->where('status', AdvertisingReportStatus::DONE)->sum('view_count');
        $clickCount = AdvertisingReport::where('advertising_id', $this->id)->where('status', AdvertisingReportStatus::DONE)->sum('click_count');
        $totalDeliveryAmount = AdvertisingReport::where('advertising_id', $this->id)->where('status', AdvertisingReportStatus::DONE)->sum('total_amount');
        DB::beginTransaction();
        try {
            if ($force) {
                AdvertisingReport::where('advertising_id', $this->id)->where('date', '>', date('Y-m-d'))->whereIn('status', [AdvertisingReportStatus::PROCESS, AdvertisingReportStatus::STOP])->update(['status' => AdvertisingStatus::DONE]);
                $reports = AdvertisingReport::where('advertising_id', $this->id)->where('date', '<=', date('Y-m-d'))->whereIn('status', [AdvertisingReportStatus::PROCESS, AdvertisingReportStatus::STOP])->get();
                $reports->each(function($report) use (&$viewCount, &$clickCount, &$totalDeliveryAmount) {
                    $report->onDone(true);
                    $viewCount += $report->view_count;
                    $clickCount += $report->click_count;
                    $totalDeliveryAmount += $report->total_amount;
                });
            }
            $result = ['status' => true];
            $total = $this->getAmount(false);
            if ($totalDeliveryAmount < $total) {
                $result = ['status' => false];
                $amount =  $total - $totalDeliveryAmount;
                $this->total_delivery_amount = $amount;

                    $userResult = Wallet::refundFromSite($this);
                    if ($userResult['status']) {
                        $result['status'] = true;
                        DB::commit();
                    } else {
                        $result['message'] = __("You don't have enough balance");
                        DB::rollBack();
                    }

            } else {
                DB::commit();
                $totalDeliveryAmount = $total;
            }

        } catch (Exception $e) {
            $result = ['status' => false, 'message' => $e->getMessage()];
            DB::rollBack();
        }

        if ($result['status']) {
            $this->update([
                'total_delivery_amount' => $totalDeliveryAmount,
                'view_count' => $viewCount,
                'click_count' => $clickCount,
                'status' => AdvertisingStatus::DONE
            ]);

            $post = $this->getPost();
            if ($post) {
                $post->update([
                    'is_ads' => false,
                    'show' => true
                ]);
            }
        }

        return $result;
    }

    public function getStatusText()
    {
        $status = AdvertisingStatus::getAll();
        return $status[$this->status->value];
    }

    public function getHashtags()
    {
        $hashtags = [];
        if ($this->hashtags) {
            $collection = Str::of($this->hashtags)->explode(' ');
            $hashtags = $collection->map(function ($value, $key) {
                return Hashtag::findByField('id', $value);
            });
        }

        return $hashtags;
    }

    public function getAddessFull()
    {
        $address = '';

        if ($this->city_id) {
            $city = City::findByField('id', $this->city_id);
            if ($city) {
                $address .= $address ? ', '.$city->getTranslatedAttributeValue('name') : $city->getTranslatedAttributeValue('name');
            }
        }

        if ($this->state_id) {
            $state = State::findByField('id', $this->state_id);
            if ($state) {
                $address .= $address ? ', '.$state->getTranslatedAttributeValue('name') : $state->getTranslatedAttributeValue('name');
            }
        }

        if ($this->zip_code) {
            $address .= $address ? ', '.$this->zip_code : $this->zip_code;
        }

        if ($this->country_id) {
            $country = Country::findByField('id', $this->country_id);
            if ($country) {
                $address .= $address ? ', '.$country->getTranslatedAttributeValue('name') : $country->getTranslatedAttributeValue('name');
            }
        }

        return $address;
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($advertising) {
            $advertising->amount_per_click = setting('shaun_advertising.amount_per_click');
            $advertising->amount_per_view = setting('shaun_advertising.amount_per_view');
            $advertising->vat = setting('shaun_advertising.vat');
            $advertising->currency = getWalletTokenName();

            if (! $advertising->age_from)  {
                $advertising->age_from = 0;
            }

            if (! $advertising->age_to)  {
                $advertising->age_to = 0;
            }
        });
    }
}
