<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Core\Http\Resources\Hashtag\HashtagResource;
use Packages\ShaunSocial\Core\Models\Hashtag;
use Packages\ShaunSocial\Core\Models\HashtagTrending;
use Packages\ShaunSocial\Core\Models\UserHashtagSuggest;
use Packages\ShaunSocial\Core\Traits\CacheSearchPagination;

class HashtagRepository
{
    use CacheSearchPagination;

    public function get($hashtag)
    {
        $item = Hashtag::findByField('name', $hashtag);

        return new HashtagResource($item);
    }

    public function filterHastagList($hashtags, $viewer = null)
    {
        return $hashtags->filter(function ($value, $key) use ($viewer) {
            return $viewer ? !$viewer->getHashtagFollow($value->name) && $value->is_active : $value->is_active;
        });
    }

    public function suggest_search($viewer, $query, $page)
    {
        $isSuggest = false;
        if ($this->checkSuggest($viewer)) {
            $isSuggest = true;
            $builder = $this->getBuilderUserSuggest($viewer);
        } else {
            $builder = Hashtag::where('is_active', true)->orderBy('post_count')->orderBy('name', 'ASC');
        }

        if ($query) {
            $builder->where('name', 'LIKE', '%'.$query.'%');
        }

        $hashtags = $this->getCacheSearchPagination('hashtag_search_'.$query, $builder, $page);
        $hashtagsNextPage = $this->getCacheSearchPagination('hashtag_search_'.$query, $builder, $page + 1);

        if ($isSuggest) {
            $hashtagIds = $hashtags->pluck('hashtag_id');
            $hashtags = $hashtagIds->map(function ($item, $key) {
                return Hashtag::findByField('id', $item);
            });
        }

        return [
            'items' => HashtagResource::collection($this->filterHastagList($hashtags, $isSuggest ? $viewer : null)),
            'has_next_page' => count($hashtagsNextPage) ? true : false
        ];
    }

    protected function addConditionUserFollow($builder, $viewer)
    {
        if ($viewer->hashtag_follow_count) {
            if ($viewer->hashtag_follow_count > config('shaun_core.follow.user.max_query_join')) {
                $builder->whereNotIn('id', function($select) use ($viewer) {
                   $select->from('hashtag_follows')
                    ->select('hashtag_id')
                    ->where('user_id', $viewer->id);
                });
            } else {
                $hashtagFollowers = $viewer->getHashtagFollows()->pluck('hashtag_id')->toArray();
                $builder->whereNotIn('id',$hashtagFollowers);
            }
        }
    }

    public function suggest_signup($viewer, $query)
    {
        $builder = Hashtag::where('is_active', true)->limit(setting('feature.item_per_page'));

        if ($query) {
            $builder->where('name', 'LIKE', '%'.$query.'%')->orderBy(DB::raw("LOCATE('".$query."', name)"));
            $hashtags = Cache::remember('hashtag_suggest_signup_'.$query, config('shaun_core.cache.time.short'), function () use ($builder) {
                return $builder->get();
            });

            return HashtagResource::collection($hashtags);
        } else {
            $result = $this->trending($viewer);
            if (! count($result)) {
                $hashtags = Cache::remember('hashtag_suggest_signup_'.$query, config('shaun_core.cache.time.short'), function () use ($builder) {
                    return $builder->get();
                });

                if (count($hashtags) > setting('feature.item_per_page')) {
                    $hashtags = $hashtags->random(setting('feature.item_per_page'));
                }

                return HashtagResource::collection($hashtags);
            }
            
            return $result;
        }
    }

    public function trending($viewer, $limit = null)
    {
        if (! $limit) {
            $limit = setting('feature.item_per_page');
        }

        $builder = HashtagTrending::where('is_active', true)->orderBy('count', 'DESC')->groupBy('hashtag_id')->selectRaw('hashtag_id, count(*) as count')->limit($limit);
        $hashtags = Cache::remember('hashtag_trending', config('shaun_core.cache.time.trending'), function () use ($builder, $limit) {
            $hashtags = $builder->get();

            if (!count($hashtags)) {
                return [];
            }

            $hashtagIds = $hashtags->pluck('hashtag_id')->toArray();
            $hashtagArray = Hashtag::whereIn('id', $hashtagIds)->get()->mapWithKeys(function ($item, $key) {
                return [$item->id => $item];
            });

            return $hashtags->map(function ($item, $key) use ($hashtagArray) {
                if (! isset($hashtagArray[$item->hashtag_id])) {
                    return false;
                }
                return $hashtagArray[$item->hashtag_id];
            });
        });
        
        return HashtagResource::collection($hashtags);
    }

    public function getBuilderUserSuggest($viewer)
    {
        $builder = UserHashtagSuggest::where('is_active', true)->where('user_id', $viewer->id)->orderBy('count')->groupBy('hashtag_id')->selectRaw('hashtag_id, count(*) as count');
        $this->addConditionUserFollow($builder, $viewer);
        return $builder;
    }

    public function checkSuggest($viewer)
    {
        $builder = UserHashtagSuggest::where('is_active', true)->where('user_id', $viewer->id);
        $this->addConditionUserFollow($builder,$viewer);
        $count = Cache::remember('user_hashtag_suggest_count_'.$viewer->id, config('shaun_core.cache.time.suggest_check'), function () use ($builder) {
            return $builder->distinct('hashtag_id')->count('hashtag_id');
        });

        if ($count > config('shaun_core.core.user_suggest_check_limit')) {
            return true;
        }

        return false;
    }

    public function suggest($viewer, $limit = null)
    {
        if (! $viewer) {
            return collect();
        }

        if (! $limit) {
            $limit = setting('feature.item_per_page');
        }

        if ($this->checkSuggest($viewer)) {
            $builder = $this->getBuilderUserSuggest($viewer);
            $builder->limit(config('shaun_core.core.number_item_random'));
            $hashtags = Cache::remember('user_hashtag_suggest_'.$viewer->id, config('shaun_core.cache.time.trending'), function () use ($builder) {
                $hashtags = $builder->get();                
    
                return $hashtags;
            });
            $hashtagIds = $hashtags->pluck('hashtag_id');
            $hashtags = $hashtagIds->map(function ($item, $key) {
                return Hashtag::findByField('id', $item);
            });
        } else {
            $builder = Hashtag::where('is_active', true)->orderBy('post_count')->orderBy('name', 'ASC')->limit(config('shaun_core.core.number_item_random'));
        
            $this->addConditionUserFollow($builder, $viewer);
    
            $hashtags = Cache::remember('hashtag_suggest_'.$viewer->id, config('shaun_core.cache.time.trending'), function () use ($builder) {
                $hashtags = $builder->get();                
    
                return $hashtags;
            });    
        }
        $hashtags = $this->filterHastagList($hashtags, $viewer);
        if (count($hashtags) > $limit) {
            $hashtags = $hashtags->random($limit);
        }
        
        return HashtagResource::collection($hashtags);
    }

    public function trending_search($query, $page)
    {
        $builder = HashtagTrending::where('is_active', true)->orderBy('count')->groupBy('hashtag_id')->selectRaw('hashtag_id, count(*) as count');

        if ($query) {
            $builder->where('name', 'LIKE', '%'.$query.'%');
        }

        $hashtags = $this->getCacheSearchPagination('hashtag_search_'.$query, $builder, $page);
        $hashtagsNextPage = $this->getCacheSearchPagination('hashtag_search_'.$query, $builder, $page + 1);

        $hashtagIds = $hashtags->pluck('hashtag_id');

        return [
            'items'=> HashtagResource::collection($hashtagIds->map(function ($item, $key) {
                return Hashtag::findByField('id', $item);
            })),
            'has_next_page' => count($hashtagsNextPage) ? true : false
        ];
    }

    public function search($query, $isCreate = false)
    {
        $hashtags = Hashtag::getCacheSearch('name_'.$query, Hashtag::where('name', 'LIKE', '%'.$query.'%')->where('is_active', true)->orderBy(DB::raw("LOCATE('".$query."', name)"))->limit(setting('feature.item_per_page')));
        if ($isCreate) {
            if (checkHashtag($query)) {
                $hashtagCreate = new Hashtag([
                    'name' => $query,
                    'post_count' => 0,
                    'follow_count' => 0
    
                ]);
                $hashtag = $hashtags->first(function ($item, $key) use ($query) {
                    return $item->name == $query;
                });            
                if (! $hashtag) {
                    $hashtags->prepend($hashtagCreate);
                }
            }
        }
        return HashtagResource::collection($hashtags);
    }
}