<?php


namespace Packages\ShaunSocial\Advertising\Repositories\Api;

use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingAgeType;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingReportStatus;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingStatus;
use Packages\ShaunSocial\Advertising\Http\Controllers\Resources\AdvertisingDetailResource;
use Packages\ShaunSocial\Advertising\Http\Controllers\Resources\AdvertisingReportResource;
use Packages\ShaunSocial\Advertising\Http\Controllers\Resources\AdvertisingResource;
use Packages\ShaunSocial\Advertising\Models\Advertising;
use Packages\ShaunSocial\Advertising\Models\AdvertisingReport;
use Packages\ShaunSocial\Core\Models\Hashtag;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\PostItem;
use Packages\ShaunSocial\Wallet\Support\Facades\Wallet;

class AdvertisingRepository
{
    public function store($data, $viewer)
    {
        $result = ['status' => true];
        if ($data['age_type'] == AdvertisingAgeType::ANY->value) {
            $data['age_from'] = 0;
            $data['age_to'] = 0;
        }

        $hashtagArray = [];
        if ($data['hashtags']) {
            foreach ($data['hashtags'] as $hashtag) {
                $item = Hashtag::firstOrCreate([
                    'name' => $hashtag,
                ]);
                $hashtagArray[] = $item->id;
            }
        }
        
        if (!empty($data['id'])) {
            $advertising = Advertising::findByField('id', $data['id']);
            unset($data['id']);
            $post = $advertising->getPost();
            $items = $post->getItems();
            $mentions = $post->mentions;

            $post->update([
                'content' => $data['content'],
                'type' => $data['type']
            ]);
            
            $post->updateMention();
            $post->sendMentionNotificationWhenEdit($mentions);
            //delete item
            if ($items) {
                $items->each(function($item) use ($data){
                    if (! in_array($item->id, $data['items'])) {
                        $item->delete();
                    }
                });
            }

            $post->clearHasCachePagination();
            $data['hashtags'] = Arr::join(array_unique($hashtagArray), ' ');
            $advertising->update($data);
        } else {
            $data['user_id'] = $viewer->id;
            //add transaction
            DB::beginTransaction();
            $result['status'] = false;
            try {
                $data['is_ads'] = true;
                $data['show'] = false;
                unset($data['hashtags']);
                $post = Post::create($data);
                
                $post->doAfterCreate();

                $data['post_id'] = $post->id;
                $data['timezone'] = $viewer->timezone;
                $data['start'] = getDateFromTimeZone($data['start'], $viewer->timezone);
                $data['end'] = getDateFromTimeZone($data['end'], $viewer->timezone). ' 23:59:59';
                $data['hashtags'] = Arr::join(array_unique($hashtagArray), ' ');
                $advertising = Advertising::create($data);
                $userResult = Wallet::buyItemFromSite($advertising);
                if ($userResult['status']) {
                    $result['status'] = true;

                    $period = CarbonPeriod::create($data['start'], $data['end']);

                    foreach ($period as $date) {
                        AdvertisingReport::create([
                            'advertising_id' => $advertising->id,
                            'date' => $date,
                            'amount' => $data['daily_amount'],
                            'currency' => getWalletTokenName()
                        ]);
                        $date->format('Y-m-d');
                    }
                    DB::commit();
                } else {
                    $result['message'] = __("You don't have enough balance");
                    DB::rollBack();
                }
                
            } catch (Exception $e) {
                $result['message'] = $e->getMessage();
                DB::rollBack();
            }

            if (! $result['status']) {
                return $result;
            }
        }

        if (count($data['items'])) {
            $postItems = [];
            foreach ($data['items'] as $key => $item) {
                $postItem = PostItem::findByField('id', $item);
                $postItem->update([
                    'post_id' => $post->id,
                    'post_queue_id' => 0,
                    'order' => $key,
                ]);
                $postItems[] = $postItem;
            }
            $post->setItems(collect($postItems));
        }

        return $result;
    }

    public function store_boot($data, $viewer)
    {
        $data['user_id'] = $viewer->id;
        if ($data['age_type'] == AdvertisingAgeType::ANY->value) {
            $data['age_from'] = 0;
            $data['age_to'] = 0;
        }
        $post = Post::findByField('id', $data['id']);
        unset($data['id']);

        $hashtagArray = [];
        if ($data['hashtags']) {
            foreach ($data['hashtags'] as $hashtag) {
                $item = Hashtag::firstOrCreate([
                    'name' => $hashtag,
                ]);
                $hashtagArray[] = $item->id;
            }
        }

        //add transaction
        DB::beginTransaction();
        $result['status'] = false;

        try {
            $post->update([
                'is_ads' => true,
                'show' => false
            ]);
            unset($data['hashtags']);

            $data['is_ads'] = true;
            $data['post_id'] = $post->id;
            $data['timezone'] = $viewer->timezone;
            $data['start'] = getDateFromTimeZone($data['start'], $viewer->timezone);
            $data['end'] = getDateFromTimeZone($data['end'], $viewer->timezone). ' 23:59:59';
            $data['hashtags'] = Arr::join(array_unique($hashtagArray), ' ');
            $advertising = Advertising::create($data);
            $userResult = Wallet::buyItemFromSite($advertising);
            if ($userResult['status']) {
                $result['status'] = true;

                $period = CarbonPeriod::create($data['start'], $data['end']);

                foreach ($period as $date) {
                    AdvertisingReport::create([
                        'advertising_id' => $advertising->id,
                        'date' => $date,
                        'amount' => $data['daily_amount'],
                        'currency' => getWalletTokenName()
                    ]);
                    $date->format('Y-m-d');
                }
                DB::commit();
            } else {
                $result['message'] = __("You don't have enough balance");
                DB::rollBack();
            }
            
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            DB::rollBack();
        }

        return $result;
    }

    public function get($page, $status, $viewer)
    {
        $builder = Advertising::where('user_id', $viewer->id)->orderBy('id', 'DESC');

        switch ($status) {
            case 'active':
                $builder->where('status', AdvertisingStatus::ACTIVE);
                break;
            case 'stop':
                $builder->where('status', AdvertisingStatus::STOP);
                break;
            case 'done':
                $builder->where('status', AdvertisingStatus::DONE);
                break;
        }

        $advertisings = Advertising::getCachePagination('advertising_'.$status.'_'.$viewer->id, $builder, $page);
        $advertisingsNextPage = Advertising::getCachePagination('advertising_'.$status.'_'.$viewer->id, $builder, $page + 1);

        return [
            'items' => AdvertisingResource::collection($advertisings),
            'has_next_page' => count($advertisingsNextPage) ? true : false
        ];
    }

    public function store_stop($id, $viewer)
    {
        $advertising = Advertising::findByField('id', $id);
        $advertising->update([
            'status' => AdvertisingStatus::STOP
        ]);

        $post = $advertising->getPost();
        if ($post) {
            $post->update(['show' => true]);
        }
    }

    public function store_enable($id, $viewer)
    {
        $advertising = Advertising::findByField('id', $id);
        $advertising->update([
            'status' => AdvertisingStatus::ACTIVE
        ]);

        $post = $advertising->getPost();
        if ($post) {
            $post->update(['show' => false]);
        }
    }

    public function store_complete($id, $viewer)
    {
        $advertising = Advertising::findByField('id', $id);
        
        $result = $advertising->onDone(true);

        return $result;
    }

    public function get_detail($id)
    {
        $advertising = Advertising::findByField('id', $id);
        
        return new AdvertisingDetailResource($advertising);
    }

    public function get_report($id, $page)
    {
        $builder = AdvertisingReport::where('advertising_id', $id)->where('status',AdvertisingReportStatus::DONE)->orderBy('id', 'DESC');

        $reports = AdvertisingReport::getCachePagination('advertising_report_'.$id, $builder, $page);
        $reportsNextPage = AdvertisingReport::getCachePagination('advertising_report_'.$id, $builder, $page + 1);

        return [
            'items' => AdvertisingReportResource::collection($reports),
            'has_next_page' => count($reportsNextPage) ? true : false
        ];
    }
}
