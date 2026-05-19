<?php


namespace Packages\ShaunSocial\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasReport;
use Packages\ShaunSocial\Core\Traits\IsSubject;

class ChatRoom extends Model
{
    use HasCacheQueryFields, HasReport, IsSubject;

    protected $lastMessage = null;

    protected $viewer = null;

    protected $members = null;

    protected $fillable = [
        'code',
        'name',
        'is_group',
        'last_message_id'
    ];

    protected $casts = [
        'is_group' => 'boolean',
    ];

    protected $cacheQueryFields = [
        'id',
    ];

    public function setViewer($viewer)
    {
        $this->viewer = $viewer;
    }

    public function getViewer()
    {
        return $this->viewer;
    }

    public function getMembers()
    {
        if (!$this->members) {
            $this->members = ChatRoomMember::getUsers($this->id);
        }

        return $this->members;
    }

    public function setMembers($members)
    {
        $this->members = $members;
    }

    public function getMember($userId)
    {
        $members = $this->getMembers();

        return $members->first(function ($member, $key) use ($userId) {
            return ($userId == $member->user_id);
        });
    }

    public function getName($viewer = null)
    {
        if ($this->name) {
            return $this->name;
        }

        $members = $this->getMembersUser();    

        if ($viewer) {            
            $members = $members->filter(function ($member, $key) use ($viewer) {
                return $member->id != $viewer->id;
            });
        }

        $membersName = $members->map(function ($user, $key) use ($viewer) {
            return $user->getName();
        });

        return $membersName->join(', ');
    }

    public function getHref()
    {
        return route('web.chat.detail',[
            'id' => $this->id
        ]);
    }

    public function getTitle()
    {
        return $this->getName();
    }

    public function getAdminHref()
    {
        return route('admin.chat.detail',[
            'id' => $this->id
        ]);
    }

    public function getLastMessage($viewerId)
    {
        if ($this->lastMessage) {
            return $this->lastMessage;
        }

        if ($this->last_message_id) {
            $chatMessageUser = ChatMessageUser::getByUserMessage($this->last_message_id, $viewerId);            
            if ($chatMessageUser->is_delete) {
                return null;
            }
            $this->lastMessage = ChatMessage::findByField('id', $this->last_message_id);
            return $this->lastMessage;
        }
         
        return null;
    }

    public function setLastMessage($lastMessage)
    {
        $this->lastMessage = $lastMessage;
    }

    public function canReport($userId)
    {
        return $this->canView($userId);
    }

    public function canView($viewerId)
    {
        $viewer = User::findByField('id', $viewerId);
        $members = $this->getMembers();
        $member = $members->first(function ($member, $key) use ($viewer) {
            return ($viewer->id == $member->user_id);
        });

        if (! $member) {
            return false;
        }

        if (! $member->canView()) {
            return false;
        }

        if (!$this->is_group) {            
            $member = $members->first(function ($member, $key) use ($viewer) {
                return ($viewer->id != $member->user_id);
            });

            if ($viewer->checkBlock($member->user_id)) {
                return false;
            }
        }

        return true;
    }

    public function canSendMessage($viewerId)
    {
        $member = $this->getMember($viewerId);
        return $member->status == 'accepted';
    }

    public function getMembersUser()
    {
        $members = $this->getMembers();
        return $members->map(function ($member, $key) {
            return $member->getUser();
       });
    }

    public function isOnline($viewerId)
    {
        $members = $this->getMembers();
        $member = $members->first(function ($member, $key) use ($viewerId) {
            return ($viewerId != $member->user_id) && Cache::has('user_online_'.$member->user_id);
        });

        return $member ? true : false;
    }

    public static function getRoomTwoUser($viewerId, $userId)
    {
        $code = getCodeFromTwoUser($viewerId, $userId);
        return self::findByField('code', $code);
    }

    public function getReportToUserId($userId = null)
    {
        if (! $this->is_group) {
            $members = $this->getMembers();
            $member = $members->first(function ($member, $key) use ($userId) {
                return ($userId != $member->user_id);
            });

            return $member->user_id;
        }

        return;
    }

    public function checkEnable($viewer)
    {
        if (! $this->is_group) {
            $members = $this->getMembersUser();
            $user = $members->first(function ($member, $key) use ($viewer) {
                return $member->id != $viewer->id;
            });
            return $user->id ? true : false;
        }

        return true;
    }
}
