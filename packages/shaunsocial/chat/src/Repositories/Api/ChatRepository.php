<?php


namespace Packages\ShaunSocial\Chat\Repositories\Api;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Chat\Events\MessageSentEvent;
use Packages\ShaunSocial\Chat\Events\MessageUnsentEvent;
use Packages\ShaunSocial\Chat\Events\RoomAcceptEvent;
use Packages\ShaunSocial\Chat\Events\RoomSeenEvent;
use Packages\ShaunSocial\Chat\Events\RoomSeenSelfEvent;
use Packages\ShaunSocial\Chat\Events\RoomUnreadEvent;
use Packages\ShaunSocial\Chat\Http\Resources\ChatItemResource;
use Packages\ShaunSocial\Chat\Http\Resources\ChatMessageResource;
use Packages\ShaunSocial\Chat\Http\Resources\ChatRoomDetailResource;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Chat\Models\ChatRoomMember;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Chat\Http\Resources\ChatRoomResource;
use Packages\ShaunSocial\Chat\Jobs\SendMessageJob;
use Packages\ShaunSocial\Chat\Models\ChatMessage;
use Packages\ShaunSocial\Chat\Models\ChatMessageItem;
use Packages\ShaunSocial\Chat\Models\ChatMessageUser;
use Packages\ShaunSocial\Chat\Notification\ChatRequestNotification;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Models\Audio;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\UserNotification;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Support\Facades\Utility;
use Packages\ShaunSocial\UserPage\Models\UserPageNotificationCron;
use Packages\ShaunSocial\Wallet\Models\WalletTransaction;
use Packages\ShaunSocial\AiChatProfiles\Models\AiChatSuggestion;

class ChatRepository
{
    use HasUserList;
    protected $userRepository = null;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function get($viewer, $isRequest ,$page)
    {
        $builder = ChatRoomMember::where('user_id', $viewer->id)->orderBy('last_updated_at', 'DESC');
        $key = 'chat_'.$viewer->id;

        if ($isRequest) {
            $key = 'chat_request_'.$viewer->id;
            $builder->where('status', 'sent');
        } else {
            $builder->where('status', 'accepted');
        }
        
        $chatRooms = ChatRoomMember::getCachePagination($key, $builder, $page);
        $chatRoomsNextPage = ChatRoomMember::getCachePagination($key, $builder, $page + 1);

        $rooms = $chatRooms->map(function ($item, $key) {
            return ChatRoom::findByField('id', $item->room_id);
        });

        //check user block
        $rooms = $rooms->filter(function ($room) use ($viewer) {
            return $room->canView($viewer->id);
        });

        return [
            'items' => ChatRoomResource::collection($rooms),
            'has_next_page' => count($chatRoomsNextPage) ? true : false
        ];
    }

    public function detail($roomId)
    {
        $room = ChatRoom::findByField('id', $roomId);
        return new ChatRoomDetailResource($room);
    }

    public function get_room_message($roomId, $viewer, $page)
    {
        $builder = ChatMessageUser::where('room_id', $roomId)->where('user_id', $viewer->id)->where('is_delete', false)->orderBy('id', 'DESC');
        $chatMessageUsers = ChatMessageUser::getCachePagination('chat_message_'.$roomId.'_'.$viewer->id, $builder, $page);
        $chatMessageUsersNextPage = ChatMessageUser::getCachePagination('chat_message_'.$roomId.'_'.$viewer->id, $builder, $page + 1);
        $messages = $chatMessageUsers->map(function ($item, $key) {
            return ChatMessage::findByField('id', $item->message_id);
        });

        return [
            'items' => ChatMessageResource::collection($messages),
            'has_next_page' => count($chatMessageUsersNextPage) ? true : false
        ];
    }

    public function store_room_message($data, $viewer)
    {
        $room = ChatRoom::findByField('id', $data['room_id']);

        $data['user_id'] = $viewer->id;
        $items = null;

        if ($data['type'] == 'text') {
            $urls = [];
            preg_match_all(config('shaun_core.regex.link'), $data['content'], $urls);
            if (!empty($urls[0][0])) {
                $link = Utility::parseLink($urls[0][0], $viewer->id);

                if ($link) {
                    $chatMessageItem = ChatMessageItem::create([
                        'user_id' => $viewer->id,
                        'subject_type' => $link->getSubjectType(),
                        'subject_id' => $link->id,
                    ]);

                    $chatMessageItem->setSubject($link);
                    $data['type'] = 'link';
                    $items = collect([$chatMessageItem]);
                }

            }
        }

        $message = ChatMessage::create($data);
        if (! empty($data['client_message_id'])) {
            $message->setClientMessageId($data['client_message_id']);
        }

        if ($data['type'] == 'send_fund') {
            $transaction = WalletTransaction::findByField('id', $data['transaction_id']);
            $chatMessageItem = ChatMessageItem::create([
                'user_id' => $viewer->id,
                'subject_type' => $transaction->getSubjectType(),
                'subject_id' => $transaction->id,
                'message_id' => $message->id
            ]);
            $chatMessageItem->setSubject($transaction);
            $message->setItems(collect([$chatMessageItem]));
        }

        if (count($data['items'])) {
            $messageItems = [];
            foreach ($data['items'] as $key => $item) {
                $messageItem = ChatMessageItem::findByField('id', $item);
                $messageItem->update([
                    'message_id' => $message->id,
                    'order' => $key,
                ]);
                $messageItems[] = $messageItem;
            }
            $message->setItems(collect($messageItems));
        }

        if ($items) {
            foreach ($items as $item) {
                $item->update([
                    'message_id' => $message->id
                ]);
            }
            $message->setItems($items);
        }

        $room->update([
            'last_message_id' => $message->id
        ]);

        $room->setLastMessage($message);

        $membersUser = $room->getMembersUser();
        foreach ($membersUser as $member) {
            if ($member->id) {
                //add message to memeber
                ChatMessageUser::create([
                    'user_id' => $member->id,
                    'room_id' => $room->id,
                    'message_id' => $message->id,
                    'is_read' => $member->id == $viewer->id ? true : false,
                ]);
            }
        }

        $response = [
            'room' => new ChatRoomResource($room),
            'message' => new ChatMessageResource($message)
        ];

        $members = $room->getMembers();
        foreach ($members as $member) {
            $user = $member->getUser();
            if ($user->id) {
                if ($member->canChangeStatusSent()) {
                    $member->update([
                        'status' => 'sent'
                    ]);
    
                    // send notify
                    if ($user->checkNotifySetting('chat_request')) {
                        Notification::send($user, $viewer, ChatRequestNotification::class, null, ['params' => ['room_id' => $room->id]], 'shaun_chat');
                    }
                }

                $member->update([
                    'last_updated_at' => now()
                ]);
            }
            if ($member->status == 'deleted') {
                $member->update(['status' => 'accepted']);
            }
        }
        
        foreach ($members as $member) {
            //check sent notify to user
            $user = $member->getUser();
            if ($user->id) {
                $default = App::getLocale();
                App::setLocale($user->language);

                $room->setMembers($members);
            
                $room->setViewer($user);
                $roomResource = new ChatRoomResource($room);
                $messageResource = new ChatMessageResource($message);
                shaunBroadcast(new MessageSentEvent($user, ['status' => $member->status,'message' => $messageResource, 'room' => $roomResource ,'chat_count' => $user->chat_count]));

                App::setLocale($default);
            }

            //check sent push notify
            if ($user->id != $viewer->id) {
                if (config('shaun_core.core.queue')) {
                    dispatch((new SendMessageJob($member, $message))->onQueue(config('shaun_core.queue.notification')));
                } else {
                    SendMessageJob::dispatchSync($member, $message);
                }

                //push notify to admin page
                if ($user->isPage() && ! $user->isOnline()) {
                    UserPageNotificationCron::add($user->id, 'message_notify');
                }
            }
        }

        $room->setViewer($viewer);

        // AiChatSuggestion::where('room_id', $data['room_id'])->where('profile_id', $data['user_id'])->delete();

        return $response;
    }

    public function store_room($userId, $viewer)
    {
        $code = getCodeFromTwoUser($viewer->id, $userId);
        $lock = Cache::lock('chat_store_room_'.$code, config('shaun_core.cache.time.lock'));
        if (! $lock->get()) {
            throw new MessageHttpException(__('Duplicate create room.'));
        }
        $room = ChatRoom::getRoomTwoUser($viewer->id, $userId);
        $created = false;
        if (!$room) {
            $created = true;
            $room = ChatRoom::create([
                'code' => $code
            ]);

            //add memeber
            $users = [$viewer->id, $userId];
            $user = User::findByField('id', $userId);

            foreach ($users as $id) {
                ChatRoomMember::create([
                    'room_id' => $room->id,
                    'user_id' => $id,
                    'status' => $id == $viewer->id ? 'accepted' : 'created',
                    'is_owner' => $id == $viewer->id,
                    'user_name' => $id == $viewer->id ? $viewer->getName() : $user->getName(),
                    'last_updated_at' => now()
                ]);
            }
        }
        if (! $created) {
            $member = $room->getMember($viewer->id);

            if ($member->status != 'accepted') {
                $this->store_room_status($room->id, 'accept', $viewer);
            }
        }

        return new ChatRoomDetailResource($room);
    }

    public function get_request_count($viewer)
    {
        $requestCount = ChatRoomMember::getRequestCount($viewer->id);
        return [
            'request_count' => $requestCount
        ];
    }

    public function store_room_seen($roomId, $viewer)
    {
        $room = ChatRoom::findByField('id', $roomId);

        $member = $room->getMember($viewer->id);
        if ($member->message_count > 0) {
            $user = $member->getUser();
            $count = $member->getUserChatCount();
            $user->update([
                'chat_count' => $count
            ]);
            
            $member->update([
                'message_count' => 0
            ]);

            ChatMessageUser::where('user_id', $viewer->id)->where('room_id', $roomId)->where('is_read', false)->update([
                'is_read' => true
            ]);

            Cache::forget(ChatMessageUser::getCacheLastMessageIdSeenKey($roomId, $viewer->id));

            shaunBroadcast(new RoomSeenSelfEvent($user, ['room_id' => $roomId, 'chat_count' => $user->chat_count]));

            $members = $room->getMembersUser();
            foreach ($members as $member) {
                shaunBroadcast(new RoomSeenEvent($member, ['room_id' => $roomId, 'user_id' => $viewer->id]));
            }
        }
    }

    public function store_room_status($roomId, $action, $viewer)
    {
        $room = ChatRoom::findByField('id', $roomId);
        $member = $room->getMember($viewer->id);
        $members = $room->getMembers();
        $memberOther = $members->first(function ($member, $key) use ($viewer) {
            return ($viewer->id != $member->user_id);
        });

        switch ($action){
            case 'accept':
                $member->update([
                    'status' => 'accepted'
                ]);
                shaunBroadcast(new RoomAcceptEvent($memberOther->getUser(), ['room_id' => $roomId]));
                break;
            case 'delete' :
            case 'block' :
                $member->update([
                    'status' => 'cancelled'
                ]);

                if ($action == 'delete') {
                    $this->clear_room_message($roomId, $viewer);
                }

                if ($action == 'block') {
                    $this->userRepository->store_block(['action' => 'add', 'id' => $memberOther->user_id], $viewer);
                }

                break;
        }        
        $notify = UserNotification::where('from_id', $memberOther->user_id)->where('user_id', $viewer->id)->where('class', ChatRequestNotification::class)->first();
        if ($notify) {
            $notify->delete();
        }
    }

    public function store_room_notify($roomId, $action, $viewer)
    {
        $room = ChatRoom::findByField('id', $roomId);
        $member = $room->getMember($viewer->id);

        switch ($action){
            case 'add':
                $member->update([
                    'enable_notify' => true
                ]);
                break;
            case 'remove' :
                $member->update([
                    'enable_notify' => false
                ]);
                break;
        }
    }

    public function clear_room_message($roomId, $viewer)
    {  
        $messageUser = ChatMessageUser::where('room_id', $roomId)->where('user_id', $viewer->id)->where('is_delete', false)->orderBy('id','DESC')->first();
        if ($messageUser) {
            $messageUser->update([
                'is_delete' => true
            ]);
        }
        ChatMessageUser::where('room_id', $roomId)->where('user_id', $viewer->id)->where('is_delete', false)->update(['is_delete' => true]);
    }

    public function upload_photo($file, $viewerId)
    {
        $storageFile = File::storePhoto($file, [
            'parent_type' => 'message_item',
            'user_id' => $viewerId,
        ]);

        $chatMessageItem = ChatMessageItem::create([
            'user_id' => $viewerId,
            'subject_type' => $storageFile->getSubjectType(),
            'subject_id' => $storageFile->id,
        ]);

        $storageFile->update([
            'parent_id' => $chatMessageItem->id,
        ]);

        $chatMessageItem->setSubject($storageFile);

        return new ChatItemResource($chatMessageItem);
    }

    public function delete_message_item($itemId)
    {
        $item = ChatMessageItem::findByField('id', $itemId);
        $item->delete();
    }

    public function store_room_unseen($roomId, $viewer)
    {
        $viewerId = $viewer->id;
        $chatMessage = ChatMessage::where('room_id', $roomId)->where('user_id','!=', $viewerId)->orderBy('id')->first();
        if ($chatMessage) {
            $chatMessageUser = ChatMessageUser::where('message_id', $chatMessage->id)->where('user_id', $viewerId)->first();  
            $chatMessageUser->updateRoomCount();
            shaunBroadcast(new RoomUnreadEvent($viewer, ['room_id' => $roomId ,'chat_count' => $viewer->chat_count]));
        }
    }

    public function check_room_online($roomIds, $viewer)
    {
        $roomIds = array_slice($roomIds,0, config('shaun_chat.max_group_check_online'));
        $respone = [];
        foreach ($roomIds as $roomId) {
            $room = ChatRoom::findByField('id', $roomId);
            $respone[$roomId] = $room->isOnline($viewer->id);
        }

        return $respone;
    }

    public function search_room($viewer, $isRequest ,$query)
    {
        $builder = ChatRoomMember::where('user_id', '!=', $viewer->id)->where('user_name', 'LIKE', '%'.$query.'%')->orderBy('last_updated_at', 'DESC');
        $key = 'chat_search_'.$viewer->id;

        if ($isRequest) {
            $key = 'chat_search_request_'.$viewer->id;
        }

        $builder->whereIn('room_id', function($select) use ($viewer, $isRequest) {
            $select->from('chat_room_members')
             ->select('room_id')
             ->where('user_id', $viewer->id);
            if ($isRequest) {
                $select->where('status', 'sent');
            } else {
                $select->where('status', 'accepted');
            }
        });

        $builder->limit(config('shaun_chat.limit_search_room'));

        $chatRooms = Cache::remember($key.'_'.$query, config('shaun_chat.cache.time.search'), function () use ($builder) {
            return $builder->get();
        });

        $rooms = $chatRooms->map(function ($item, $key) {
            return ChatRoom::findByField('id', $item->room_id);
        });

        //check user block
        $rooms = $rooms->filter(function ($room) use ($viewer) {
            return $room->canView($viewer->id);
        });

        return ChatRoomResource::collection($rooms);
    }

    public function store_active_room($roomId, $viewer)
    {
        $room = ChatRoom::findByField('id', $roomId);
        $member = $room->getMember($viewer->id);
        if (checkAppApi()) {
            $member->setAppActive(true);
        } else {
            $member->setWebActive(true);
        }
    }
    
    public function store_inactive_room($roomId, $viewer)
    {
        $room = ChatRoom::findByField('id', $roomId);
        $member = $room->getMember($viewer->id);
        if (checkAppApi()) {
            $member->setAppActive(false);
        } else {
            $member->setWebActive(false);
        }
    }

    public function unsent_room_message($messageId, $viewer)
    {
        $message = ChatMessage::findByField('id', $messageId);
        $message->update(['is_delete' => true]);

        $room = ChatRoom::findByField('id', $message->room_id);
        $members = $room->getMembers();
        foreach ($members as $member) {
            $user = $member->getUser();
            if ($user->id) {
                shaunBroadcast(new MessageUnsentEvent($user, ['message' => $message]));
            }
        }
    }

    public function upload_file($file, $viewerId)
    {
        $storageFile = File::store($file, [
            'parent_type' => 'message_item',
            'user_id' => $viewerId,
            'extension' => $file->getClientOriginalExtension(),
			'name' => $file->getClientOriginalName()
        ]);

        $chatMessageItem = ChatMessageItem::create([
            'user_id' => $viewerId,
            'subject_type' => $storageFile->getSubjectType(),
            'subject_id' => $storageFile->id,
        ]);

        $storageFile->update([
            'parent_id' => $chatMessageItem->id
        ]);

        $chatMessageItem->setSubject($storageFile);

        return new ChatItemResource($chatMessageItem);
    }

    public function delete_room($roomId, $viewer)
    {  
        $this->clear_room_message($roomId, $viewer);

        $room = ChatRoom::findByField('id', $roomId);
        $member = $room->getMember($viewer->id);
        $member->update(['status' => 'deleted']);
    }

    public function store_audio($roomId, $file, $parentMessageId, $viewer)
    {
        $duration = Utility::getAudioDuration($file->path(), 'chat.send_audio_max_duration', $viewer);

        $storageFile = File::store($file, [
            'parent_type' => 'audio',
            'user_id' => $viewer->id,
            'extension' => $file->getClientOriginalExtension(),
			'name' => $file->getClientOriginalName()
        ]);

        $audio = Audio::create([
            'file_id' => $storageFile->id,
            'duration' => $duration
        ]);

        $storageFile->update([
            'parent_id' => $audio->id
        ]);

        $chatMessageItem = ChatMessageItem::create([
            'user_id' => $viewer->id,
            'subject_type' => $audio->getSubjectType(),
            'subject_id' => $audio->id,
        ]);
        ChatMessageItem::setCacheQueryFieldsResult('id', $chatMessageItem->id, $chatMessageItem);
        
        $chatMessageItem->setSubject($audio);

        return $this->store_room_message([
            'type' => 'audio',
            'parent_message_id' => $parentMessageId,
            'content' => '',
            'items' => [$chatMessageItem->id],
            'room_id' => $roomId
        ], $viewer);
    }

    public function store_room_dating($userId, $viewer)
    {
        $code = getCodeFromTwoUser($viewer->id, $userId);
        $lock = Cache::lock('chat_store_room_dating'.$code, config('shaun_core.cache.time.lock'));
        if (! $lock->get()) {
            throw new MessageHttpException(__('Duplicate create room dating.'));
        }
        $room = ChatRoom::getRoomTwoUser($viewer->id, $userId);
        if (!$room) {
            $room = ChatRoom::create([
                'code' => $code
            ]);

            //add memeber
            $users = [$viewer->id, $userId];
            $user = User::findByField('id', $userId);

            foreach ($users as $id) {
                ChatRoomMember::create([
                    'room_id' => $room->id,
                    'user_id' => $id,
                    'status' => 'accepted',
                    'is_owner' => $id == $viewer->id,
                    'user_name' => $id == $viewer->id ? $viewer->getName() : $user->getName(),
                    'last_updated_at' => now()
                ]);
            }
        }

        return $room->id;
    }
}
