<?php


namespace Packages\ShaunSocial\Vibb\Repositories\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Vibb\Http\Resources\VibbSongResource;
use Packages\ShaunSocial\Core\Http\Resources\Post\ItemResource;
use Packages\ShaunSocial\Vibb\Models\VibbSong;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\PostItem;
use Packages\ShaunSocial\Core\Models\PostHome;
use Packages\ShaunSocial\Core\Models\UserFollowNotificationCron;
use Packages\ShaunSocial\Core\Notification\Post\PostUserFollowNotification;
use Illuminate\Support\Arr;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Resources\Post\PostResource;
use Packages\ShaunSocial\Core\Repositories\Api\PostRepository;
use Packages\ShaunSocial\Core\Support\Facades\Utility;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Vibb\Models\VibbPostSong;

class VibbRepository
{   
    use HasUserList;
    protected $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function search_song($query)
    {
        $songs = Cache::remember('song_search_'.$query, config('shaun_core.cache.time.short'), function () use ($query) {
            return VibbSong::getCacheSearch('name_'.$query, VibbSong::where('name', 'LIKE', '%'.$query.'%')->where('is_active', true)->orderBy(DB::raw("LOCATE('".$query."', name)"))->limit(setting('feature.item_per_page')));
        });
        
        return VibbSongResource::collection($songs);
    }

    public function upload_video($file, $isConverted, $viewerId)
    {
        $result = Utility::storeVideo($file, $viewerId, $isConverted, true, 'vibb.video_max_duration');
        if ($result['status']) {
            $video = $result['video'];
            $postItem = PostItem::create([
                'user_id' => $viewerId,
                'subject_type' => $video->getSubjectType(),
                'subject_id' => $video->id,
            ]);

            $postItem->setSubject($video);

            return ['status' => true, 'item' => new ItemResource($postItem)];
        }
        
        return $result;
    }

    public function store($data, $viewer)
    {
        $postItem = PostItem::findByField('id', $data['item_id']);
        
        if (empty($data['is_converted']) && ! empty($data['song_id'])) {
            $subject = $postItem->getSubject();
            $song = VibbSong::findByField('id', $data['song_id']);
            $result = Utility::addSongToVideoFromVideoModel($subject, $song->getFile('file_id'));
            if (! $result) {
                throw new MessageHttpException(__('Error when convert video.'));
            }
        }
        
        $data['type'] = 'vibb';
        $data['user_id'] = $viewer->id;
        $data['privacy'] = $viewer->privacy;
        if (is_array($data['content_warning_categories'])) {
            $data['content_warning_categories'] = Arr::join(array_filter(array_unique($data['content_warning_categories'])), ' ');
        } else {
            $data['content_warning_categories'] = '';
        }
        
        $post = Post::create($data);

        $postItems = collect();
        $postItem->update([
            'post_id' => $post->id,
            'post_queue_id' => 0,
            'order' => 1,
        ]);
        $postItems->add($postItem);

        if (! empty($data['song_id'])) {
            $song = VibbSong::findByField('id', $data['song_id']);
            $song->update([
                'use_count' => $song->use_count + 1
            ]);

            $postSong = VibbPostSong::create([
                'name' => $song->name,
                'post_id' => $post->id,
                'song_id' => $song->id
            ]);

            $songItem = PostItem::create([
                'post_id' => $post->id,
                'user_id' => $viewer->id,
                'subject_type' => 'vibb_post_songs',
                'subject_id' => $postSong->id,
                'order' => 2,
            ]);

            $postItems->add($songItem);
        }

        $post->setItems($postItems);

        // send notify
        $post->sendMentionNotification($post->getUSer());
        
        //Send notify to follower
        if ($viewer->checkUserEnableFollowNotification()) {
            UserFollowNotificationCron::create([
                'user_id' => $viewer->id,
                'subject_type' => $post->getSubjectType(),
                'subject_id' => $post->id,
                'class'=> PostUserFollowNotification::class
            ]);
        }

        Post::setCacheQueryFieldsResult('id', $post->id, $post);

        return new PostResource($post);
    }

    public function for_you($viewer, $page)
    {
        $builder = $this->postRepository->getConditionForTrendding();
        $builder->whereIn('type', array('vibb'));
        
        $this->postRepository->addConditionUserPrivacy($builder, $viewer);
        $userId = $viewer ? $viewer->id : 0;

        $results = Cache::remember('vibb_for_you_'.$userId.'_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
            return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
        });

        $posts = collect();
        foreach ($results as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post) {
                $posts->push(Post::findByField('id', $result->post_id));
            }
        }

        $posts = $this->postRepository->filterPostList($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        return PostResource::collection($posts);
    }

    public function following($viewer, $page)
    {
        $builder = PostHome::orderBy('created_at', 'DESC')->where('show', true)->where('type', 'vibb');
        $builder->where(function($query) use ($viewer) {
            if (setting('site.home_feed_type')) {
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
                        });
                    } else {                    
                        $userFollowers = $viewer->getFollows()->pluck('follower_id')->toArray();
                        $query->orWhere(function($select) use ($userFollowers) {
                            $select->whereIn('user_id',$userFollowers);
                            $select->where('user_privacy','!=',config('shaun_core.privacy.user.only_me'));
                        });
                    }
                }
                
                if ($viewer->hashtag_follow_count) {
                    $hashtagFollowers = $viewer->getHashtagFollows()->pluck('hashtag_id')->join(' ');
                    $query->orWhere(function($query) use ($hashtagFollowers) {
                        $query->whereFullText('hashtags',$hashtagFollowers);
                        $query->where('user_privacy',config('shaun_core.privacy.user.everyone'));
                    });
                }
            } else {
                $query->where('user_id', $viewer->id);
                $query->orWhere('user_privacy',config('shaun_core.privacy.user.everyone'));
                if ($viewer->following_count) {
                    if ($viewer->following_count > config('shaun_core.follow.user.max_query_join')) {
                        $query->orWhere(function($query) use ($viewer) {
                            $query->whereIn('user_id', function($select) use ($viewer) {
                                $select->from('user_follows')
                                    ->select('follower_id')
                                    ->where('user_id', $viewer->id);
                            });
                            $query->where('user_privacy','!=',config('shaun_core.privacy.user.only_me'));
                        });
                    } else {                    
                        $userFollowers = $viewer->getFollows()->pluck('follower_id')->toArray();
                        $query->orWhere(function($select) use ($userFollowers) {
                            $select->whereIn('user_id',$userFollowers);
                            $select->where('user_privacy','!=',config('shaun_core.privacy.user.only_me'));
                        });
                    }
                }
            }
        });
        
        $results = Cache::remember('vibb_following_'.$viewer->id.'_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
            return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
        });

        $posts = collect();
        foreach ($results as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post) {
                $posts->push($post);
            }            
        }
        $posts = $this->postRepository->filterPostList($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        return PostResource::collection($posts);
    }

    public function profile($userId, $page, $viewer)
    {
        $posts = Post::getCachePagination('user_profile_vibb_'.$userId, Post::where('user_id', $userId)->where('type', 'vibb')->orderBy('id', 'DESC'), $page);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });
        return PostResource::collection($posts);
    }

    public function my($page, $viewer)
    {
        return $this->profile($viewer->id, $page, $viewer);
    }
}
