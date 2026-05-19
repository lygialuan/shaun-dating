<?php


namespace Packages\ShaunSocial\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasContentTranslate;
use Packages\ShaunSocial\Core\Traits\HasUser;

class ChatMessage extends Model
{
    use HasCacheQueryFields, HasUser, HasContentTranslate;

    protected $items = null;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $contentLanguageFields= [
        'content'
    ];
    
    protected $fillable = [
        'user_id',
        'type',
        'content',
        'room_id',
        'is_delete',
        'parent_message_id'
    ];

    protected $casts = [
        'is_delete' => 'boolean',
    ];

    public static function getTypes()
    {
        return [
            'text',
            'photo',
            'link',
            'file',
            'send_fund'
        ];
    }

    protected $client_message_id = '';

    public function getUser()
    {
        $user = User::findByField('id', $this->user_id);
        return $user ?? getDeleteUser();
    }

    public function getItems()
    {
        if (! in_array($this->type, ['photo', 'link', 'story_reply', 'file', 'send_fund', 'story_share','audio' ,'post'])) {
            return [];
        }
        
        if (! $this->items) {
            $items = ChatMessageItem::findByField('message_id', $this->id, true);
            if ($items) {
                $items = $items->sortBy(function ($item) {
                    return $item->order;
                });
            }

            $this->items = $items;
        }

        return $this->items;
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function getRoom()
    {
        return ChatRoom::findByField('id', $this->room_id);
    }

    public function getContent()
    {
        return $this->is_delete ? __('Message has been unsent') : $this->content;
    }

    public function getShortContent()
    {
        if($this->is_delete){
            return __('Message has been unsent');
        }
        switch($this->type) {
            case 'text':
            case 'story_reply':
            case 'link':
                return $this->content;
                break;
            case 'photo':
                $items = $this->getItems();
                if (count($items) > 1) {
                    return __('sent photos');
                } else {
                    return __('sent a photo');
                }
                    
                break;
            case 'file':
                return __('sent an attachment');
                break;
            case 'send_fund':
                return __('sent funds');
                break;
            case 'story_share':
                return __('sent a story');
                break;
            case 'audio':
                return __('sent a voice');
                break;
            case 'post':
                return __('sent a post');
                break;
        }
    }
    public function canDelete($viewerId)
    {
        return $this->user_id == $viewerId;
    }

    public function getParentMessage()
    {
        if($this->parent_message_id){
            return self::findByField('id', $this->parent_message_id);
        }
        return null;
    }

    public function setClientMessageId($id)
    {
        $this->client_message_id = $id;
    }

    public function getClientMessageId()
    {
        return $this->client_message_id;
    }

    public function canView($viewerId)
    {
        if (! $viewerId) {
            return false;
        }
        
        return ChatMessageUser::getByUserMessage($this->id, $viewerId);
    }
}
