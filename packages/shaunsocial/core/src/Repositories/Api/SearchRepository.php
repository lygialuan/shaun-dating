<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Http\Resources\Hashtag\HashtagResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\Hashtag;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Core\Http\Resources\Post\PostResource;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Advertising\Traits\Utility as AdvertisingUtility;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Group\Enum\GroupStatus;
use Packages\ShaunSocial\Group\Http\Resources\GroupResource;
use Packages\ShaunSocial\Group\Models\Group;
use Packages\ShaunSocial\Group\Traits\Utility as GroupUtility;
use Packages\ShaunSocial\Core\Models\SearchHistory;
use Packages\ShaunSocial\Core\Http\Resources\SearchHistory\SearchHistoryResource;

class SearchRepository
{
    use HasUserList, AdvertisingUtility, Utility, GroupUtility;

    protected $postRepository = null;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function search($query ,$viewer)
    {
        $hashtags = Hashtag::getCacheSearch('name_'.$query, Hashtag::where('name', 'LIKE', '%'.$query.'%')->where('is_active', true)->orderBy(DB::raw("LOCATE('".$query."', name)"))->limit(setting('feature.item_per_page')));
        
        if (count($hashtags) > config('shaun_core.search.number_item_suggest')) {
            $hashtags = $hashtags->slice(0, config('shaun_core.search.number_item_suggest'));
        }

        $users = Cache::remember('user_search_name_'.$query, config('shaun_core.cache.time.short'), function () use ($query) {
            return User::where('name', 'LIKE', '%'.$query.'%')->where('is_active', true)->where('is_page', false)->orderBy(DB::raw("LOCATE('".$query."', name)"))->limit(setting('feature.item_per_page'))->get();
        });

        if (count($users) > config('shaun_core.search.number_item_suggest')) {
            $users = $users->slice(0, config('shaun_core.search.number_item_suggest'));
        }

        $pages = collect();

        if (setting('shaun_user_page.enable')) {
            $pages = Cache::remember('user_page_search_name_'.$query, config('shaun_core.cache.time.short'), function () use ($query) {
                return User::where('name', 'LIKE', '%'.$query.'%')->where('is_active', true)->where('is_page', true)->orderBy(DB::raw("LOCATE('".$query."', name)"))->limit(setting('feature.item_per_page'))->get();
            });
        }

        if (count($pages) > config('shaun_core.search.number_item_suggest')) {
            $pages = $pages->slice(0, config('shaun_core.search.number_item_suggest'));
        }

        $groups = Cache::remember('group_search_name_'.$query, config('shaun_core.cache.time.short'), function () use ($query) {
            return Group::where('name', 'LIKE', '%'.$query.'%')->where('status', GroupStatus::ACTIVE)->orderBy(DB::raw("LOCATE('".$query."', name)"))->limit(setting('feature.item_per_page'))->get();
        });

        return [
            'hashtags' => HashtagResource::collection($hashtags),
            'users' => UserResource::collection($this->filterUserList($users, $viewer, 'id')),
            'pages' => UserResource::collection($this->filterUserList($pages, $viewer, 'id')),
            'groups' => GroupResource::collection($this->filterGroupList($groups, $viewer))
        ];
    }

    public function get($query, $type, $page, $viewer, $key, $isHashtag = false)
    {
        $viewerId = $viewer ? $viewer->id : 0;
        $hashtag = null;
        if ($isHashtag) {
            $hashtag = Hashtag::findByField('name', $query);
            if (! $hashtag) {
                return collect();
            }
        }

        switch ($type) {
            case 'post':
                $results = Cache::remember('search_text_'.md5($viewerId.'_'.$type.'_'.$query.'_'.$page), config('shaun_core.cache.time.short'), function () use ($query, $page, $isHashtag, $hashtag, $viewer) {
                    if ($isHashtag) {
                        $builder = Post::whereFullText('hashtags', $hashtag->id)->where('show', true);
                    } else {
                        $builder = Post::whereFullText('content_search',$query)->where('show', true);
                    }

                    $this->postRepository->addConditionUserPrivacy($builder, $viewer);
                    
                    return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->orderBy('created_at', 'DESC')->where('has_source', false)->get();
                });
                $posts = $this->filterUserList($results, $viewer, 'user_id', ['privacy', 'active']);
                $posts->each(function($post) use ($viewer){
                    $post->addStatistic('post_reach', $viewer);
                });

                $posts = $this->addAdvertisings($posts, $page, $viewer, $key);

                return PostResource::collection($posts);
            case 'user':
                $results = Cache::remember('search_text_'.md5($viewerId.'_'.$type.'_'.$query.'_'.$page), config('shaun_core.cache.time.short'), function () use ($query, $page, $isHashtag, $hashtag) {
                    $builder = User::where('is_active', true)->where('is_page', false);
                    if ($isHashtag) {
                        $users = $builder->whereFullText('hashtags', $hashtag->id);
                    } else {
                        $users = $builder->where('name', 'LIKE', '%'.$query.'%');
                    }
                    
                    return $users->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->orderBy(DB::raw("LOCATE('".$query."', name)"))->get();
                });
                
                return UserResource::collection($this->filterUserList($results, $viewer, 'id', ['active']));
                break;
            case 'page':
                $results = Cache::remember('search_text_'.md5($viewerId.'_'.$type.'_'.$query.'_'.$page), config('shaun_core.cache.time.short'), function () use ($query, $page, $isHashtag, $hashtag) {
                    $builder = User::where('is_active', true)->where('is_page', true);
                    if ($isHashtag) {
                        $users = $builder->whereFullText('page_hashtags', $hashtag->id);
                    } else {
                        $users = $builder->where('name', 'LIKE', '%'.$query.'%');
                    }
                    
                    return $users->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->orderBy(DB::raw("LOCATE('".$query."', name)"))->get();
                });
                
                return UserResource::collection($this->filterUserList($results, $viewer, 'id', ['active']));
                break;
            case 'group':
                $results = Cache::remember('search_text_'.md5($viewerId.'_'.$type.'_'.$query.'_'.$page), config('shaun_core.cache.time.short'), function () use ($query, $page, $isHashtag, $hashtag) {
                    $builder = Group::where('status', GroupStatus::ACTIVE);
                    if ($isHashtag) {
                        $users = $builder->whereFullText('hashtags', $hashtag->id);
                    } else {
                        $users = $builder->where('name', 'LIKE', '%'.$query.'%');
                    }
                    
                    return $users->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->orderBy(DB::raw("LOCATE('".$query."', name)"))->get();
                });
                
                return GroupResource::collection($this->filterGroupList($results, $viewer));
                break;
        }
    }

    public function get_search_histories($viewer) 
    {
        $historySearches = SearchHistory::getByUser($viewer->id);
        return SearchHistoryResource::collection($historySearches);
    }

    public function store_search_history($viewer, $query) 
    {
        if (SearchHistory::getCountByUser($viewer->id) > config('shaun_core.core.suggest_search_limit')) {
            $searchHistory = SearchHistory::getByUser($viewer->id)->last();
            if ($searchHistory) {
                SearchHistory::where('user_id', $viewer->id)->orderBy('id', 'DESC')->where('id', '<', $searchHistory->id)->delete();
            }
        }
        
        $historySearch = SearchHistory::where('user_id', $viewer->id)->where('query', $query)->first();
        if ($historySearch) {
            $historySearch->delete();
        }
        
        $historySearch  = SearchHistory::create([
            'user_id' => $viewer->id,
            'query' => $query
        ]);
    }

    public function delete_search_history($historySearchId) 
    {
        $historySearch = SearchHistory::findByField('id', $historySearchId);
        $historySearch->delete();
    }
}
