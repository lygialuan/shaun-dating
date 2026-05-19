<?php


namespace Packages\ShaunSocial\Advertising\Traits;

use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingStatisticType;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingStatus;
use Packages\ShaunSocial\Advertising\Models\Advertising;
use Packages\ShaunSocial\Advertising\Models\AdvertisingDelivery;

trait Utility
{
    public function addAdvertisings($posts, $page, $viewer, $uniqueKey)
    {
        if (! setting('shaun_advertising.enable')) {
            return $posts;
        }

        if (! count($posts)) {
            return $posts;
        }

        $number = getPostNumberShowAdvertising();
        $limit = (count($posts) / $number) + 1;
        $builder = Advertising::where('status', AdvertisingStatus::ACTIVE)->where('start', '<=', now()->format('Y-m-d'))->where('end', '>=', now()->format('Y-m-d'))->where('date_stop', '!=', convertDateToInteger(now()))->orderBy('sort_count', 'ASC')->orderBy('id', 'DESC')->limit($limit);
        $cacheKey = 'advertising_post_'.$uniqueKey.'_'.$page;
        $advertisingDelivery = AdvertisingDelivery::findByField('key', $uniqueKey);
        if (! $advertisingDelivery) {
            $advertisingDelivery = AdvertisingDelivery::create([
                'key' => $uniqueKey
            ]);
        }

        if ($page == 1) {
            $ids = [];
        } else {
            $ids = $advertisingDelivery->getIds();
        }
        
        if ($viewer) {
            if (! $viewer->hasPermission('advertising.show_ads')) {
                return $posts;
            }
            if ($viewer->gender_id) {
                $builder->where(function($query) use ($viewer) {
                    $query->orWhere('gender_id', $viewer->gender_id);
                    $query->orWhere('gender_id', 0);
                });
            } else {
                $builder->where('gender_id', 0);
            }

            $age = $viewer->getAge();
            if ($age !== null) {
                $builder->where(function($query) use ($age) {
                    $query->orWhere(function($query) use ($age) {
                        $query->where('age_from', '<=', $age);
                        $query->where('age_to', '>=', $age);
                    });
                    $query->orWhere(function($query) {
                        $query->where('age_from', 0);
                        $query->where('age_to', 0);
                    });
                });
            } else {
                $builder->where('age_from', 0);
                $builder->where('age_to', 0);
            }

            $hashtagRelative = $viewer->getHastagRelative();
            if ($hashtagRelative) {
                $builder->where(function($query) use ($hashtagRelative) {
                    $query->orWhereFullText('hashtags',implode(' ',$hashtagRelative));
                    $query->orWhere('hashtags', '');
                });
            } else {
                $builder->where('hashtags', '');
            }

            $builder->where(function($query) use ($viewer) {
                if ($viewer->city_id) {
                    $query->orWhere('city_id', $viewer->city_id);
                } else {
                    if ($viewer->state_id) {
                        $query->orWhere('state_id', $viewer->state_id);
                    }
                }
                
                if ($viewer->country_id) {
                    if ($viewer->zip_code) {
                        $query->orWhere(function($query) use ($viewer) {
                            $query->where('country_id', $viewer->country_id);
                            $query->where('zip_code', $viewer->zip_code);
                        });
                    }

                    $query->orWhere(function($query) use ($viewer) {
                        $query->where('country_id', $viewer->country_id);
                        $query->where('state_id', 0);
                        $query->where('city_id', 0);
                        $query->where('zip_code', '');
                    });
                }
                
                $query->orWhere(function($query) use ($viewer) {
                    $query->where('country_id', 0);
                    $query->where('state_id', 0);
                    $query->where('city_id', 0);
                    $query->where('zip_code', '');
                });
            });

            $builder->where('user_id', '!=', $viewer->id);
        } else {
            $builder->where('gender_id', 0);
            $builder->where('age_from', 0);
            $builder->where('age_to', 0);
            $builder->where('hashtags', '');
            $builder->where('country_id', 0);
            $builder->where('state_id', 0);
            $builder->where('city_id', 0);
            $builder->where('zip_code', '');
        }
        if ($ids) {
            $cacheKey .= '_'.md5(json_encode($ids));
            $builder->whereNotIn('id', $ids);
        }
        
        $advertisings = Cache::remember($cacheKey, config('shaun_core.cache.time.short'), function () use ($builder) {            
            return $builder->get();
        });

        $result = collect();
        $check = false;
        if (count($advertisings)) {
            $count = count($posts);
            $advertisings = $advertisings->values();
            foreach ($posts as $key => $post) {
                $result->push($post);
                $key = $key + 1;
                $index = $key % $number;
                $advertisingIndex = null;
                if (! $index) {
                    $advertisingIndex = intval($key / $number) - 1;
                } elseif ($key == $count) {
                    $advertisingIndex = intval(floor($key / $number));
                }
                
                if ($advertisingIndex !== null && isset($advertisings[$advertisingIndex])) {
                    $advertising = $advertisings[$advertisingIndex];
                    $lock = Cache::lock('advertising_sort_count_'.$advertising->id, config('shaun_advertising.sort_count_lock_time'));
                    if ($lock->get()) {
                        $advertising->update([
                            'sort_count' => $advertising->sort_count + 1
                        ]);
                    }
                    $post = $advertising->getPost();
                    $post->addStatistic('post_reach', $viewer);
                    $post->addAdvertisingStatistic(AdvertisingStatisticType::VIEW, $viewer ? $viewer->id : 0);
                    $post->setIsAdvertising(true);
                    $result->push($post);
                    $check = true;
                    $ids[] = $advertising->id;
                }
            }
        } else {
            $result = $posts;
        }

        if ($check) {
            $advertisingDelivery->update([
                'ids' => json_encode($ids)
            ]);
        }

        return $result;
    }
}
