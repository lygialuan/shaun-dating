<?php


namespace Packages\ShaunSocial\Story\Repositories\Api;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Chat\Models\ChatMessageItem;
use Packages\ShaunSocial\Chat\Repositories\Api\ChatRepository;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\UserFollowNotificationCron;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Support\Facades\Utility;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Story\Http\Resources\StoryDetailResource;
use Packages\ShaunSocial\Story\Http\Resources\StoryItemResource;
use Packages\ShaunSocial\Story\Http\Resources\StoryResource;
use Packages\ShaunSocial\Story\Http\Resources\StorySongResource;
use Packages\ShaunSocial\Story\Models\Story;
use Packages\ShaunSocial\Story\Models\StoryItem;
use Packages\ShaunSocial\Story\Models\StorySong;
use Packages\ShaunSocial\Story\Models\StoryView;
use Packages\ShaunSocial\Story\Notification\StoryEndNotification;
use Packages\ShaunSocial\Story\Notification\StoryUserFollowNotification;
use Packages\ShaunSocial\Core\Enum\UserVerifyStatus;

class StoryRepository
{
    use HasUserList;
    
    protected $chatRepository;

    public function __construct(ChatRepository $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    public function search_song($query)
    {
        $songs = Cache::remember('song_search_'.$query, config('shaun_core.cache.time.short'), function () use ($query) {
            return StorySong::getCacheSearch('name_'.$query, StorySong::where('name', 'LIKE', '%'.$query.'%')->where('is_active', true)->orderBy(DB::raw("LOCATE('".$query."', name)"))->limit(setting('feature.item_per_page')));
        });
        
        return StorySongResource::collection($songs);
    }

    public function get($viewer, $page, $filters = [])
    {
        $today = Carbon::today();

        $builder = Story::query()
            ->join('users', 'users.id', '=', 'stories.user_id')
            ->where('stories.user_id', '!=', $viewer->id)
            ->whereIn('stories.user_privacy', [
                config('shaun_core.privacy.user.everyone'),
                config('shaun_core.privacy.user.my_follower'),
            ])
            ->orderByDesc('stories.last_updated_at')
            ->select('stories.*');

        $builder->where(function ($q) use ($filters, $today) {
            if (!empty($filters['age']['min'])) {
                $q->whereDate(
                    'users.birthday',
                    '<=',
                    $today->copy()->subYears((int)$filters['age']['min'])
                );
            }
            if (!empty($filters['age']['max'])) {
                $q->whereDate(
                    'users.birthday',
                    '>',
                    $today->copy()->subYears(((int)$filters['age']['max']) + 1)
                );
            }
            $q->orWhereNull('users.birthday');
        });

        if (!empty($filters['gender']) && $filters['gender'] != 0) {
            $builder->where('users.gender_id', $filters['gender']);
        }

        if (!empty($filters['verifiedProfiles']) && $filters['verifiedProfiles'][0] == "show") {
            $builder->where('users.verify_status', UserVerifyStatus::OK->value);
        }

        if (!empty($filters['attributeValuesFilter'])) {
            $builder->attribute($filters['attributeValuesFilter']);
        }

        if (!empty($filters['interestAttributeValuesFilter'])) {
            $builder->interestAttributes($filters['interestAttributeValuesFilter']);
        }

        if (!empty($filters['location'])) {
            $builder->where(function ($query) use ($filters) {
                if (!empty($filters['location'][0]['id'])) {
                    $query->where(
                        'users.dating_addresses_id',
                        $filters['location'][0]['id']
                    );
                }
                if (!empty($filters['location'][0]['name'])) {
                    $query->orWhereFullText(
                        'users.dating_addresses_fulltext',
                        $filters['location'][0]['name']
                    );
                }
            });
        }

        $cacheName = 'story_'.$viewer->id.'_'.md5(json_encode($filters)).'_page_'.$page;

        $results = Cache::remember($cacheName, config('shaun_core.cache.time.short'), function () use ($builder, $page) { 
            return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
        });
        
        if ($page == 1) {
            $story = Story::findByField('user_id', $viewer->id);
            if($story){
                $results->prepend($story);
            }
        }

        return StoryResource::collection($this->filterUserList($results, $viewer));
    }

    public function store($data, $photo ,$viewer)
    {
        $story = Story::findByField('user_id', $viewer->id);
        if (! $story) {
            $story = Story::create([
                'user_id' => $viewer->id,
                'user_privacy' => $viewer->privacy,
                'last_updated_at' => now()
            ]);
        } else {
            $story->update([
                'last_updated_at' => now()
            ]);
        }
        $items = $story->getItems();

        switch ($data['type'])
        {
            case 'text':
                $item = StoryItem::create([
                    'content' => $data['content'],
                    'user_id' => $viewer->id,
                    'story_id' => $story->id,
                    'type' => 'text',
                    'song_id' => $data['song_id'],
                    'background_id' => $data['background_id'],
                    'content_color' => $data['content_color'],
                    'is_active' => true,
                ]);
                break;
            case 'photo':
                $storageFile = File::storePhoto($photo, [
                    'parent_type' => 'story_item_photo',
                    'user_id' => $viewer->id,
                    'extension' => $photo->getClientOriginalExtension(),
                    'name' => $photo->getClientOriginalName()
                ]);
                $item = StoryItem::create([
                    'user_id' => $viewer->id,
                    'story_id' => $story->id,
                    'type' => 'photo',
                    'song_id' => $data['song_id'],
                    'photo_id' => $storageFile->id,
                    'is_active' => true,
                ]);
                break;
            case 'video':
                $item = StoryItem::findByField('id', $data['item_id']);
                if (! empty($data['song_id'])) {
                    $song = StorySong::findByField('id', $data['song_id']);
                    $result = Utility::addSongToVideoFromVideoModel($item->getVideo(), $song->getFile('file_id'));
                    if (! $result) {
                        throw new MessageHttpException(__('Error when convert video.'));
                    }
                }
                $item->update([
                    'song_id' => $data['song_id'],
                    'story_id' => $story->id,
                    'is_active' => true,
                ]);

                break;
        }

        if (count($items)) {
            $items->push($item);
        } else {
            $items = collect([$item]);
        }
        
        $story->setItems($items);

        return new StoryResource($story);
    }

    public function detail($id)
    {
        $story = Story::findByField('id', $id);
        return new StoryDetailResource($story);
    }

    public function store_view_item($id, $viewer)
    {
        $storyItem = StoryItem::findByField('id', $id);
        if (! $storyItem->getView($viewer->id)) {
            StoryView::create([
                'user_id' => $viewer->id,
                'story_item_id' => $storyItem->id,
                'story_id' => $storyItem->story_id
            ]);
        }
    }

    public function delete($id, $deleteAuto = false)
    {
        $storyItem = StoryItem::findByField('id', $id);
        $story = $storyItem->getStory();
        if ($story) {
            $items = $story->getItems();
            $items = $items->filter(function ($value, $key) use ($storyItem){
                return $value->id != $storyItem->id;
            });

            if (count($items)) {
                $story->update([
                    'last_updated_at' => $items->last()->created_at
                ]);
            } else {
                $story->delete();
            }
        }
        if ($deleteAuto) {
            //add notify when auto delete
            $user = $storyItem->getUser();
            if ($user && $user->checkNotifySetting('story_end')) {
                Notification::send($user, $user, StoryEndNotification::class, $storyItem, ['is_system' => true], 'shaun_story', false);
            }

            $storyItem->update([
                'story_id' => 0
            ]);
        } else {
            $storyItem->delete();
        }
        
    }

    public function get_view($id, $page, $viewer)
    {
        $storyItem = StoryItem::findByField('id', $id);
        $users = StoryView::getCachePagination('story_viewer_'.$id, StoryView::where('story_item_id', $id)->where('user_id', '!=', $storyItem->user_id), $page);
        $usersNextPage = StoryView::getCachePagination('story_viewer_'.$id, StoryView::where('story_item_id', $id)->where('user_id', '!=', $storyItem->user_id), $page  + 1);

        $users = $users->map(function ($item, $key) {
            return User::findByField('id', $item->user_id);
        });

        return [
            'items' => UserResource::collection($this->filterUserList($users, $viewer, 'id')),
            'has_next_page' => count($usersNextPage) ? true : false
        ];
    }

    public function my($viewer, $page)
    {
        $storyItems = StoryItem::getCachePagination('story_item_user_'.$viewer->id, StoryItem::where('user_id', $viewer->id)->where('is_active', true)->orderBy('id', 'DESC'), $page);
        $storyItemsNextPage = StoryItem::getCachePagination('story_item_user_'.$viewer->id, StoryItem::where('user_id', $viewer->id)->where('is_active', true)->orderBy('id', 'DESC'), $page + 1);

        return [
            'items' => StoryItemResource::collection($storyItems),
            'has_next_page' => count($storyItemsNextPage) ? true : false
        ];
    }

    public function store_message($data, $viewer)
    {
        $storyItem = StoryItem::findByField('id', $data['id']);
        $result = $this->chatRepository->store_room($storyItem->user_id, $viewer);
        
        $item = ChatMessageItem::create([
            'user_id' => $viewer->id,
            'subject_type' => 'story_items',
            'subject_id' => $data['id'],            
        ]);

        $this->chatRepository->store_room_message([
            'type' => 'story_reply',
            'content' => $data['content'],
            'items' => [$item->id],
            'room_id' => $result['id']
        ], $viewer);
    }

    public function detail_item($id, $viewer)
    {
        $storyItem = StoryItem::findByField('id', $id);
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = $viewer ? $viewer->isModerator() : false;
        $canSendMessage = $storyItem->getUser()->canSendMessage($viewerId) || $isAdmin;

        return [
            'user' => $storyItem->getUserResource(),
            'item' => new StoryItemResource($storyItem),
            'canMessage' => $viewerId == $storyItem->user_id ? false : $canSendMessage,
            'canShare' => true
        ];
    }

    public function detail_in_list($stortId)
    {
        $story = Story::findByField('id', $stortId);
        
        return new StoryResource($story);
    }

    public function share_message($data, $viewer)
    {
        foreach ($data['user_ids'] as $userId) {
            $result = $this->chatRepository->store_room($userId, $viewer);
            
            $item = ChatMessageItem::create([
                'user_id' => $viewer->id,
                'subject_type' => 'story_items',
                'subject_id' => $data['id'],            
            ]);
    
            $this->chatRepository->store_room_message([
                'type' => 'story_share',
                'content' => $data['content'],
                'items' => [$item->id],
                'room_id' => $result['id']
            ], $viewer);
        }

    }

    public function upload_video($file, $isConverted, $viewerId)
    {
        $result = Utility::storeVideo($file, $viewerId, $isConverted, true, 'story.video_max_duration');
        if ($result['status']) {
            $video = $result['video'];
            $storyItem = StoryItem::create([
                'content' => '',
                'user_id' => $viewerId,
                'type' => 'video',
                'video_id' => $video->id
            ]);

            return ['status' => true, 'item' => new StoryItemResource($storyItem)];
        }
        
        return $result;
    }
}
