<?php

namespace Packages\ShaunSocial\Dating\Repositories\Api;

use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Dating\Models\DatingAttribute;
use Packages\ShaunSocial\Dating\Models\DatingInterestAttribute;
use Packages\ShaunSocial\Dating\Models\DatingSearchHistory;
use Packages\ShaunSocial\Dating\Models\DatingAddress;
use Packages\ShaunSocial\Dating\Models\DatingSwipe;
use Packages\ShaunSocial\Dating\Http\Resources\DatingAttributeResource;
use Packages\ShaunSocial\Dating\Http\Resources\DatingInterestAttributeResource;
use Packages\ShaunSocial\Dating\Http\Resources\DatingSuggestionLocationResource;
use Packages\ShaunSocial\Dating\Http\Resources\DatingSwipeResource;
use Packages\ShaunSocial\Dating\Notification\DatingUserLikeNotification;
use Packages\ShaunSocial\Dating\Support\Facades\Dating;
use Packages\ShaunSocial\Chat\Repositories\Api\ChatRepository;
use Packages\ShaunSocial\Core\Models\UserActionLog;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Support\Facades\Notification;

class DatingRepository
{
    protected $chatRepository;

    public function __construct(ChatRepository $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    public function getAttributes()
    {
        $attributes = DatingAttribute::getAttributeList();
        return DatingAttributeResource::collection($attributes);
    }

    public function saveAttributes($data, $viewer)
    {
        $attributes = implode(' ', $data);
        $viewer->attributes = $attributes;
        $viewer->save();
    }

    public function getInterestAttributes()
    {
        $attributes = DatingInterestAttribute::getAttributeList();
        return DatingInterestAttributeResource::collection($attributes);
    }

    public function saveInterestAttributes($data, $viewer)
    {
        $attributes = implode(' ', $data);
        $viewer->interest_attributes = $attributes;
        $viewer->save();
    }

    public function saveFilter($data, $viewer)
    {
        DatingSearchHistory::updateOrCreate(
            ['user_id' => $viewer->id],
            ['query' => json_encode($data)] 
        );
    }

    public function suggestionLocations($keyword){
        $builder = DatingAddress::where('is_active', true);
        if ($keyword) {
            $builder->where('address', 'LIKE', '%'.$keyword.'%');
            $suggestionLocations = Cache::remember('suggestion_locations_'.$keyword, config('shaun_core.cache.time.short'), function () use ($builder) {
                return $builder->get();
            });

            return DatingSuggestionLocationResource::collection($suggestionLocations);
        }
    }

    public function swipe($user, $targetId, $action){
        if ($user->id == $targetId) return false;

        $swipe = DatingSwipe::where('user_id', $user->id)->where('target_user_id', $targetId)->first();
        if (!$swipe) {
            DatingSwipe::create([
                'user_id' => $user->id,
                'target_user_id' => $targetId,
                'action' => $action,
            ]);
        } elseif ($action !== 'viewed') {
            $swipe->update(['action' => $action]);
        }

        $roomId = 0;
        if ($action === 'like') {
            DatingSwipe::convertDislikeToViewed($targetId, $user->id);

            UserActionLog::create(['user_id' => $user->id, 'type' => 'dating_swipe']);

            Notification::send(User::findByField('id', $targetId), $user, DatingUserLikeNotification::class, '', [], 'shaun_dating', false);

            $checkMatch = Dating::checkMatch($user->id, $targetId);
            if($checkMatch){
                $roomId = $this->chatRepository->store_room_dating($targetId, $user);
                $data['type']              = 'text';
                $data['content']           =  __("Congratulation! It's a match!");
                $data['room_id']           = $roomId;
                $data['items']             = [];
                $data['parent_message_id'] = 0;
                $data['client_message_id'] = Dating::makeClientId();
                $this->chatRepository->store_room_message($data, $user);
                $this->chatRepository->store_room_status($roomId, 'accept', $user);
            }
        }
        return $roomId;
    }
    
    public function getUserActions($viewer, $page, $action)
    {
        $builder = DatingSwipe::query()->orderByDesc('id');

        match ($action) {
            'liked_me'  => $builder->where('target_user_id', $viewer->id)->where('action', 'like'),
            'i_liked'   => $builder->where('user_id', $viewer->id)->where('action', 'like'),
            'viewed'    => $builder->where('user_id', $viewer->id),
            'viewed_me' => $builder->where('target_user_id', $viewer->id),
            default => abort(400, 'Invalid action'),
        };

        $cacheKey    = "get_dating_swipe_{$action}_{$viewer->id}";
        $items       = DatingSwipe::getCachePagination($cacheKey, $builder, $page);
        $hasNextPage = DatingSwipe::getCachePagination($cacheKey, $builder, $page + 1);

        return [
            'items' => DatingSwipeResource::collection($items),
            'has_next_page' => $hasNextPage,
        ];
    }

    public function queryAdminsCanManageProfilePictures(string $permissionKey)
    {
        return User::query()->whereExists(function ($query) use ($permissionKey) {
            $query->selectRaw(1)
                ->from('roles')
                ->whereColumn('roles.id', 'users.role_id')
                ->where(function ($q) use ($permissionKey) {
                    $q->where('roles.is_supper_admin', 1)
                        ->orWhere(function ($sub) use ($permissionKey) {
                            $sub->where('roles.is_moderator', 1)
                                ->whereExists(function ($perm) use ($permissionKey) {
                                    $perm->selectRaw(1)
                                        ->from('role_permissions')
                                        ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                                        ->whereColumn('role_permissions.role_id', 'roles.id')
                                        ->where('permissions.key', $permissionKey)
                                        ->where('role_permissions.value', 1);
                                });
                        });
                });
        });
    }

    public function hasPendingReviewPhotos(): bool
    {
        return User::where('has_reviewed_photos', false)->exists();
    }
}