<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Http\Resources\Post\ItemResource;
use Packages\ShaunSocial\Core\Http\Resources\Post\PollVoteResource;
use Packages\ShaunSocial\Core\Http\Resources\Post\PostResource;
use Packages\ShaunSocial\Core\Http\Resources\Post\PollResource;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\PostItem;
use Packages\ShaunSocial\Core\Models\Poll;
use Packages\ShaunSocial\Core\Models\PollItem;
use Packages\ShaunSocial\Core\Models\PollVote;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingStatisticType;
use Packages\ShaunSocial\Core\Jobs\PostQueueJob;
use Packages\ShaunSocial\Core\Models\History;
use Packages\ShaunSocial\Core\Models\PostHome;
use Packages\ShaunSocial\Core\Models\PostQueue;
use Packages\ShaunSocial\Core\Models\UserHashtagSuggest;
use Packages\ShaunSocial\Core\Notification\Post\PostVideoNotification;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Advertising\Traits\Utility as AdvertisingUtility;
use Packages\ShaunSocial\Core\Enum\PostPaidType;
use Packages\ShaunSocial\Core\Http\Resources\Utility\StorageFileResource;
use Packages\ShaunSocial\Core\Models\StorageFile;
use Packages\ShaunSocial\Core\Support\Facades\Utility;
use Packages\ShaunSocial\Core\Traits\Utility as TraitsUtility;
use Packages\ShaunSocial\Group\Notification\GroupPostVideoPendingNotification;
use Packages\ShaunSocial\Group\Traits\Utility as GroupUtility;

class PostRepository
{
    use HasUserList, AdvertisingUtility, TraitsUtility, GroupUtility;

    public function upload_photo($file, $viewerId)
    {
        $storageFile = File::storePhoto($file, [
            'parent_type' => 'post_item',
            'user_id' => $viewerId,
        ], true);

        $postItem = PostItem::create([
            'user_id' => $viewerId,
            'subject_type' => $storageFile->getSubjectType(),
            'subject_id' => $storageFile->id,
        ]);

        $storageFile->update([
            'parent_id' => $postItem->id,
        ]);

        $postItem->setSubject($storageFile);

        return new ItemResource($postItem);
    }

    public function upload_thumb($file, $viewerId)
    {
        $storageFile = File::storePhoto($file, [
            'parent_type' => 'post_review',
            'user_id' => $viewerId,
        ], true);

        return new StorageFileResource($storageFile);
    }

    public function getHomeBuilder($viewer)
    {
        $builder = PostHome::orderBy('created_at', 'DESC')->where('show', true);
        if (! $viewer || ! $viewer->isModerator()) {
            $builder->where(function($query) use ($viewer) {
                if (setting('site.home_feed_type')) {
                    $query->where(function($query) use ($viewer) {
                        $query->orWhere(function($query) use ($viewer) {
                            $query->where('user_id', $viewer->id);
                            if ($viewer->following_count) {
                                if ($viewer->following_count > config('shaun_core.follow.user.max_query_join')) {
                                    $query->orWhere(function($query) use ($viewer) {
                                        $query->whereIn('user_id', function($select) use ($viewer) {
                                            $select->from('user_follows')
                                                ->select('follower_id')
                                                ->where('user_id', $viewer->id);
                                        });
                                        $query->where('user_privacy','!=',config('shaun_core.privacy.user.only_me'));
                                        $query->where('has_source', false);
                                    });
                                } else {                    
                                    $userFollowers = $viewer->getFollows()->pluck('follower_id')->toArray();
                                    $query->orWhere(function($select) use ($userFollowers) {
                                        $select->whereIn('user_id',$userFollowers);
                                        $select->where('user_privacy','!=',config('shaun_core.privacy.user.only_me'));
                                        $select->where('has_source', false);
                                    });
                                }
                            }
                            
                            if ($viewer->hashtag_follow_count) {
                                $hashtagFollowers = $viewer->getHashtagFollows()->pluck('hashtag_id')->join(' ');
                                $query->orWhere(function($query) use ($hashtagFollowers) {
                                    $query->whereFullText('hashtags',$hashtagFollowers);
                                    $query->where('user_privacy',config('shaun_core.privacy.user.everyone'));
                                    $query->where('has_source', false);
                                });
                            }
                        });

                        $this->addBuilderPostHome($query, $viewer);
                    });
                } else {
                    $query->where(function($query) use ($viewer) {
                        $query->orWhere(function($query) use ($viewer) {
                            $query->where('user_id', $viewer->id);
                            $query->where('has_source', false);
                        });
                        $query->orWhere(function($query) use ($viewer) {
                            $query->where('user_privacy',config('shaun_core.privacy.user.everyone'));
                            $query->where('has_source', false);
                        });
                        
                        if ($viewer->following_count) {
                            if ($viewer->following_count > config('shaun_core.follow.user.max_query_join')) {
                                $query->orWhere(function($query) use ($viewer) {
                                    $query->whereIn('user_id', function($select) use ($viewer) {
                                        $select->from('user_follows')
                                            ->select('follower_id')
                                            ->where('user_id', $viewer->id);
                                    });
                                    $query->where('user_privacy','!=',config('shaun_core.privacy.user.only_me'));
                                    $query->where('has_source', false);
                                });
                            } else {                    
                                $userFollowers = $viewer->getFollows()->pluck('follower_id')->toArray();
                                $query->orWhere(function($select) use ($userFollowers) {
                                    $select->whereIn('user_id',$userFollowers);
                                    $select->where('user_privacy','!=',config('shaun_core.privacy.user.only_me'));
                                    $select->where('has_source', false);
                                });
                            }
                        }

                        $this->addBuilderPostHome($query, $viewer);
                    });
                }
            });
        }

        return $builder;
    }

    public function get_by_ids($ids, $viewer)
    {
        $builder = Post::whereIn('id', array_filter(array_unique(explode(',', $ids))));
        $posts = Cache::remember('post_get_by_ids_'.md5($ids), config('shaun_core.cache.time.short'), function () use ($builder) {            
            return $builder->limit(setting('feature.item_per_page'))->get();
        });

        $posts = $this->filterPostList($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        return PostResource::collection($posts);
    }

    public function filterPostList($posts, $viewer, $hasSource = false)
    {
        return $posts->filter(function ($value, $key) use ($viewer, $hasSource) {
            if ($hasSource) {
                if ($value->has_source) {
                    return true;
                }
            }
            return $this->fitlerUser($value->user_id, $viewer, ['privacy', 'active']);            
        });
    }

    public function store_queue($data, $viewer)
    {
        $post = PostQueue::create([
            'user_id' => $viewer->id,
            'type' => $data['type'],
            'content' => $data['content'],
            'comment_privacy' => ! empty($data['comment_privacy']) ? $data['comment_privacy'] : '',
            'content_warning_categories' => ! empty($data['content_warning_categories']) ? $data['content_warning_categories']  : '',
            'source_type' => $data['source_type'] ?? '',
            'source_id' => $data['source_id'] ?? 0,
            'thumb_file_id' => $data['thumb_file_id'] ?? 0,
            'is_paid' => $data['is_paid'] ?? false,
            'content_amount' => $data['content_amount'] ?? 0,
            'paid_type' => $data['paid_type'] ?? PostPaidType::PAYPERVIEW
        ]);

        foreach ($data['items'] as $key => $item) {
            $postItem = PostItem::findByField('id', $item);
            $postItem->update([
                'post_queue_id' => $post->id,
                'order' => $key,
            ]);
        }

        if (config('shaun_core.core.queue')) {
            dispatch((new PostQueueJob($post))->onQueue(config('shaun_core.queue.post_queue')));
        }

        return [
            'queue' => true
        ];
    }

    public function store_now($data, $viewer)
    {
        $data['user_id'] = $viewer->id;
        $data['privacy'] = $viewer->privacy;
        if(in_array($data['type'], ['photo', 'video']) && isset($data['content_warning_categories']) && is_array($data['content_warning_categories'])){
            $data['content_warning_categories'] = Arr::join(array_filter(array_unique($data['content_warning_categories'])), ' ');
        } else {
            $data['content_warning_categories'] = '';
        }
        
        $post = Post::create($data);

        $post->doAfterCreate();

        if (!empty($data['thumb_file_id'])) {
            $file = StorageFile::findByField('id', $data['thumb_file_id']);
            $file->update([
                'parent_id' => $post->id
            ]);
        }

        switch ($post->type) {
            case 'share_item':
                $postItem = PostItem::create([
                    'user_id' => $viewer->id,
                    'subject_type' => $data['subject_type'],
                    'subject_id' => $data['subject_id'],
                    'post_id' => $post->id
                ]);
                $post->setItems(collect([$postItem]));
                break;
            case 'poll':
                $poll = Poll::create([
                    'user_id' => $viewer->id,
                    'post_id' => $post->id,
                    'close_minute' => $data['close_minute']
                ]);
    
                $postItem = PostItem::create([
                    'user_id' => $viewer->id,
                    'subject_type' => $poll->getSubjectType(),
                    'subject_id' => $poll->id,
                    'post_id' => $post->id
                ]);
                $pollItems = collect();
                foreach ($data['items'] as $item) {
                    $pollItem = PollItem::create([
                        'name' => $item,
                        'user_id' => $viewer->id,
                        'poll_id' => $poll->id
                    ]);
    
                    $pollItems->add($pollItem);
                }
    
                $poll->setItems($pollItems);
                $postItem->setSubject($poll);
                $post->setItems(collect([$postItem]));
                break;
            default:
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
                break;
        }
        $post->makeThumbPurre();
        
        //check pending
        if (! $post->show) {
            return [
                'pending' => true
            ];
        }

        Post::setCacheQueryFieldsResult('id', $post->id, $post);

        $post->sendNotification();
        if ($post->has_source) {
            $post->setIn('source', true);
        }
        return new PostResource($post);
    }

    public function store($data, $viewer)
    {
        if (count($data['items']) && ! in_array($data['type'],['poll'])) {
            $checkQueue = false;
            foreach ($data['items'] as $key => $item) {
                $postItem = PostItem::findByField('id', $item);
                if ($postItem->checkNeedQueue()) {
                    $checkQueue = true;
                    break;    
                }                
            }

            if ($checkQueue) {
                return $this->store_queue($data, $viewer);
            }
        }
        
        return $this->store_now($data, $viewer);
    }

    public function profile($userId, $page, $viewer)
    {
        $posts = collect();
    
        if ($page == 1) {
            $pinPosts = Post::getPinProfile($userId);
            $pinPosts->each(function($post) use ($posts){
                $postNew = Post::findByField('id', $post->id);
                $postNew->setIn('profile', true);
                $posts->add($postNew);
            });
        }

        $results = Post::getCachePagination('user_profile_'.$userId, Post::where('user_id', $userId)->orderBy('created_at', 'DESC'), $page)->where('has_source', false)->where('show', true);

        foreach ($results as $post) {
            if (! $post->pin_profile_date) {
                $posts->push($post);
            }
        }

        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        return PostResource::collection($posts);
    }

    public function profile_media($userId, $page, $viewer)
    {
        $posts = Post::getCachePagination('user_profile_media_'.$userId, Post::where('user_id', $userId)->whereIn('type', array('photo', 'video'))->orderBy('created_at', 'DESC'), $page)->where('has_source', false);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });
        return PostResource::collection($posts);
    }
    
    public function addConditionUserPrivacy($builder, $viewer)
    {
        if ($viewer) {
            if ($viewer->following_count) {
                if ($viewer->following_count > config('shaun_core.follow.user.max_query_join')) {
                    $builder->where(function($query) use ($viewer) {
                        $query->orWhere('user_id', $viewer->id);
                        $query->orWhere('user_privacy', config('shaun_core.privacy.user.everyone'));
                        $query->orWhere(function($query) use ($viewer) {
                            $query->whereIn('user_id', function($select) use ($viewer) {
                                $select->from('user_follows')
                                    ->select('follower_id')
                                    ->where('user_id', $viewer->id);
                            });
                            $query->where('user_privacy','!=',config('shaun_core.privacy.user.only_me'));
                        });
                    });
                } else {
                    $userFollowers = $viewer->getFollows()->pluck('follower_id')->toArray();
                    $builder->where(function($query) use ($userFollowers, $viewer) {
                        $query->orWhere('user_id', $viewer->id);
                        $query->orWhere('user_privacy', config('shaun_core.privacy.user.everyone'));
                        $query->orWhere(function($query) use ($userFollowers) {
                            $query->whereIn('user_id',$userFollowers);
                            $query->where('user_privacy','!=',config('shaun_core.privacy.user.only_me'));
                        });
                    });                
                }
            } else {
                $builder->where(function($query) use ($viewer) {
                    $query->orWhere('user_id', $viewer->id);
                    $query->orWhere('user_privacy', config('shaun_core.privacy.user.everyone'));
                }); 
            }
        } else {
            $builder->where('user_privacy', config('shaun_core.privacy.user.everyone'));
        }
    }

    public function home($viewer, $page, $key)
    {
        $builder = $this->getHomeBuilder($viewer);
        
        $results = Cache::remember('post_user_home_'.$viewer->id.'_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
            return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
        });

        $posts = collect();
        if ($page == 1) {
            $pinPosts = Post::getPinHome();
            $pinPosts->each(function($post) use ($posts){
                $postNew = Post::findByField('id', $post->id);
                $postNew->setIn('home', true);
                $posts->add($postNew);
            });
        }

        foreach ($results as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post && ($post->has_source || ! $post->pin_date)) {
                $posts->push($post);
            }
        }
        $posts = $this->filterPostListForSource($posts, $viewer);
        $posts = $this->filterPostList($posts, $viewer, true);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });
        
        $posts = $this->addAdvertisings($posts, $page, $viewer, $key);
        
        return PostResource::collection($posts);
    }

    public function delete($id)
    {
        $post = Post::findByField('id', $id);
        
        $post->delete();
    }

    public function get($id, $viewer, $ip)
    {
        $post = Post::findByField('id', $id);
        $post->addStatistic('post_reach', $viewer);
        //add ads statistic
        $post->addAdvertisingStatistic(AdvertisingStatisticType::CLICK, $viewer ? $viewer->id : 0, $ip);
        if ($viewer) {
            $hashtags = $post->getHashtags();

            if ($hashtags) {
                foreach ($hashtags as $hashtag) {
                    if (! $viewer->getHashtagFollow($hashtag->name)) {
                        UserHashtagSuggest::create([
                            'name' => $hashtag->name,
                            'hashtag_id' => $hashtag->id,
                            'is_active' => $hashtag->is_active,
                            'user_id' => $viewer->id
                        ]);
                    }
                }
                Cache::forget('user_hashtag_suggest_'.$viewer->id);
            }
        }        

        return new PostResource($post);
    }

    public function fetch_link($url, $viewerId)
    {
        $link = Utility::parseLink($url, $viewerId);

        if ($link) {
            $postItem = PostItem::create([
                'user_id' => $viewerId,
                'subject_type' => $link->getSubjectType(),
                'subject_id' => $link->id,
            ]);

            $postItem->setSubject($link);

            return new ItemResource($postItem);
        }

        return null;
    }

    public function delete_item($itemId)
    {
        $item = PostItem::findByField('id', $itemId);
        $item->delete();
    }

    public function hashtag($hashtag, $page, $viewer)
    {
        $posts = Post::getCachePagination('hashtag_'.$hashtag->name, Post::whereFullText('hashtags', $hashtag->id)->orderBy('created_at', 'DESC'), $page)->where('has_source', false);

        $posts = $this->filterPostList($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        return PostResource::collection($posts);
    }

    public function getConditionForTrendding()
    {
        $builder = PostHome::addSelect(DB::raw('(total_count*'.config('shaun_core.trending_point.reach').' - DATEDIFF(CURRENT_DATE,created_at)*'.config('shaun_core.trending_point.diff_day').') as trending_order, post_id'))->where('show', true)->where('has_source', false);
        $builder->orderBy('trending_order', 'DESC')->orderBy('created_at', 'DESC');

        return $builder;
    }

    public function discovery($viewer, $page, $key)
    {
        $builder = $this->getConditionForTrendding();

        $this->addConditionUserPrivacy($builder, $viewer);
        $userId = $viewer ? $viewer->id : 0;

        $results = Cache::remember('post_user_discovery_'.$userId.'_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
            return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
        });

        $posts = collect();
        foreach ($results as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post) {
                $posts->push(Post::findByField('id', $result->post_id));
            }
        }
        $posts = $this->filterPostList($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        $posts = $this->addAdvertisings($posts, $page, $viewer, $key);
        
        return PostResource::collection($posts);
    }

    public function watch($viewer, $page, $key)
    {
        $builder = $this->getConditionForTrendding();
        $builder->where('type', 'video');

        $this->addConditionUserPrivacy($builder, $viewer);
        $userId = $viewer ? $viewer->id : 0;

        $results = Cache::remember('post_user_watch_'.$userId.'_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
            return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
        });

        $posts = collect();
        foreach ($results as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post) {
                $posts->push(Post::findByField('id', $result->post_id));
            }
        }
        $posts = $this->filterPostList($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        $posts = $this->addAdvertisings($posts, $page, $viewer, $key);

        return PostResource::collection($posts);
    }

    public function media($viewer, $page)
    {
        $builder = $this->getConditionForTrendding();
        $builder->whereIn('type', array('photo', 'video'));
        
        $this->addConditionUserPrivacy($builder, $viewer);
        $userId = $viewer ? $viewer->id : 0;

        $results = Cache::remember('post_user_media_'.$userId.'_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
            return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
        });

        $posts = collect();
        foreach ($results as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post) {
                $posts->push(Post::findByField('id', $result->post_id));
            }
        }

        $posts = $this->filterPostList($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        return PostResource::collection($posts);
    }

    public function store_edit($id, $content, $viewer)
    {
        $post = Post::findByField('id', $id);
        $mentions = $post->mentions;

        History::create([
            'user_id' => $viewer->id,
            'content' => $post->getMentionContent(null),
            'mentions' => $post->mentions,
            'subject_type' => $post->getSubjectType(),
            'subject_id' => $post->id,
        ]);

        $post->update([
            'content' => $content,
            'is_edited' => true
        ]);
        
        $post->updateMention();
        $post->sendMentionNotificationWhenEdit($mentions);

        return new PostResource($post);
    }

    public function upload_video($file , $isConverted, $convertNow, $viewerId)
    {
        $result = Utility::storeVideo($file, $viewerId, $isConverted, $convertNow, '');
        if ($result['status']) {
            $video = $result['video'];
            $postItem = PostItem::create([
                'user_id' => $viewerId,
                'subject_type' => $video->getSubjectType(),
                'subject_id' => $video->id,
                'has_queue' => ! $video->is_converted
            ]);

            $postItem->setSubject($video);

            return ['status' => true, 'item' => new ItemResource($postItem)];
        }
        
        return $result;
    }

    public function run_queue($postQueue)
    {
        $items = $postQueue->getItems();
        $items->each(function($item) {
            $item->runQueue();
        });

        $item = $items->first(function ($item, $key) {
            return $item->checkNeedQueue();
        });

        if ($item) {
            $postQueue->delete();
            return;
        }
        $data = [
            'content' => $postQueue->content,
            'type' => $postQueue->type,
            'items' => $items->pluck('id')->all(),
            'content_warning_categories' => $postQueue->content_warning_categories,
            'comment_privacy' => $postQueue->comment_privacy,
            'source_type' => $postQueue->source_type,
            'source_id' => $postQueue->source_id,
            'thumb_file_id' => $postQueue->thumb_file_id,
            'is_paid' => $postQueue->is_paid,
            'content_amount' => $postQueue->content_amount,
            'paid_type' => $postQueue->paid_type,
        ];
        
        $postResource = $this->store_now($data, $postQueue->getUser());
        if (empty($postResource['pending'])) {
            $post = Post::findByField('id', $postResource['id']);            
            //send notify
            Notification::send($postQueue->getUser(), $postQueue->getUser(), PostVideoNotification::class, $post, ['is_system' => true], 'shaun_core', false);
        }  else {
            if ($postQueue->source_type == 'groups') {
                $group = $postQueue->getSource();
                if ($group) {
                    Notification::send($postQueue->getUser(), $postQueue->getUser(), GroupPostVideoPendingNotification::class, $group, ['is_system' => true], 'shaun_group', false);
                }
            }
        }
        
        $postQueue->setCanDeleteItem(false);
        $postQueue->setStorageFields([]);
        $postQueue->delete();
    }

    public function upload_file($file, $viewerId)
    {
        $storageFile = File::store($file, [
            'parent_type' => 'message_item',
            'user_id' => $viewerId,
            'extension' => $file->getClientOriginalExtension(),
			'name' => $file->getClientOriginalName()
        ]);

        $postItem = PostItem::create([
            'user_id' => $viewerId,
            'subject_type' => $storageFile->getSubjectType(),
            'subject_id' => $storageFile->id,
        ]);

        $storageFile->update([
            'parent_id' => $postItem->id
        ]);

        $postItem->setSubject($storageFile);

        return new ItemResource($postItem);
    }

    public function store_vote_poll($data, $viewerId)
    {
        $poll = Poll::findByField('id', $data['poll_id']);
        $pollItems = $poll->getItems();

        $votes = PollVote::getVotes($data['poll_id'], $viewerId);
        $pollVoteIdOld = 0;
        if(count($votes)){
            $poll->vote_count = $poll->vote_count - 1;
            $pollVoteOld = $votes->first();
            $pollVoteIdOld = $pollVoteOld->poll_item_id;
            $pollVoteOld->delete();
        }
        $pollVoteNew = false;
        if($data['action'] == 'add'){
            $poll->vote_count = $poll->vote_count + 1;
            $pollVoteNew = PollVote::create([
                'user_id' => $viewerId,
                'poll_id' => $data['poll_id'],
                'poll_item_id' => $data['poll_item_id']
            ]);
        }
        $pollItems->each(function ($item, $key) use ($poll,$pollVoteIdOld, $pollVoteNew, $viewerId){
            if ($pollVoteNew && $pollVoteNew->poll_item_id == $item->id) {
                $item->vote_count = $item->vote_count + 1; 
                $item->setVote($viewerId, $pollVoteNew);
            }
            if ($pollVoteIdOld && $item->id == $pollVoteIdOld) {
                $item->vote_count = $item->vote_count - 1;
                $item->setVote($viewerId, null);
            }
            $item->setPoll($poll);
        });
        $poll->setItems($pollItems);
        
        return new PollResource($poll);
    }

    public function get_poll_item_vote($pollItemId, $viewer, $page)
    {
        $builder = PollVote::where('poll_item_id', $pollItemId)->orderBy('id', 'DESC');
        
        $pollVotes = PollVote::getCachePagination('poll_item_votes_'.$pollItemId, $builder, $page);

        $pollVotesNextPage = PollVote::getCachePagination('poll_item_votes_'.$pollItemId, $builder, $page + 1);

        return [
            'items' => PollVoteResource::collection($this->filterUserList($pollVotes, $viewer)),
            'has_next_page' => count($pollVotesNextPage) ? true : false
        ];
    }
    
    public function store_comment_privacy($id, $commentPrivacy)
    {
        $post = Post::findByField('id', $id);

        $post->update([
            'comment_privacy' => $commentPrivacy
        ]);
        
        return new PostResource($post);
    }
    
    public function store_content_warning($id, $contentWarning)
    {
        $post = Post::findByField('id', $id);

        if(in_array($post['type'], ['photo', 'video', 'vibb'])){
            $contentWarningData = Arr::join(array_filter(array_unique($contentWarning)), ' ');
        } else {
            $contentWarningData = '';
        }

        $post->update([
            'content_warning_categories' => $contentWarningData
        ]);
        
        return new PostResource($post);
    }

    public function document($viewer, $page)
    {
        $builder = $this->getConditionForTrendding();
        $builder->whereIn('type', array('file'));
        
        $this->addConditionUserPrivacy($builder, $viewer);
        $userId = $viewer ? $viewer->id : 0;

        $results = Cache::remember('post_user_file_'.$userId.'_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
            return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
        });

        $posts = collect();
        foreach ($results as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post) {
                $posts->push(Post::findByField('id', $result->post_id));
            }
        }

        $posts = $this->filterPostList($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        return PostResource::collection($posts);
    }

    public function store_stop_comment($id, $stop)
    {
        $post = Post::findByField('id', $id);
        $post->update([
            'stop_comment' => $stop
        ]);
    }

    public function store_pin_home($id, $action)
    {
        $post = Post::findByField('id', $id);
        if ($action == 'pin') {
            $post->update(['pin_date' => now()->timestamp]);
        } else {
            $post->update(['pin_date' => 0]);
        }

        $post->clearCache();
    }

    public function store_pin_profile($id, $action)
    {
        $post = Post::findByField('id', $id);
        if ($action == 'pin') {
            $post->update(['pin_profile_date' => now()->timestamp]);
        } else {
            $post->update(['pin_profile_date' => 0]);
        }

        $post->clearCache();
    }

    public function get_new_home($id, $viewer)
    {
        $builder = $this->getHomeBuilder($viewer);
        if ($id) {
            $post = Post::findByField('id', $id);
            $builder->where('created_at','>', $post->created_at->addSecond());
        }
        
        $post = Cache::remember('get_new_home'.$viewer->id, config('shaun_core.cache.time.short'), function () use ($builder) {            
            return $builder->first();
        });

        return [
            'new_post' => $post ? true : false
        ];
    }
}
