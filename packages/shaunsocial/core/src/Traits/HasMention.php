<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\UserNotification;
use Packages\ShaunSocial\Core\Support\Facades\Notification;

trait HasMention
{
    public function initializeHasMention()
    {
        $this->fillable[] = 'mentions';
    }

    public function supportMention()
    {
        return true;
    }

    public function updateMention()
    {
        $value = $this->{$this->mentionField};
        if ($value) {
            $mentions = getMentionsFromContent($value);
            $mentionsUsers = [];
            foreach ($mentions as $mention) {
                $userName = str_replace('@', '', $mention);
                $user = User::findByField('user_name', $userName);
                if ($user) {
                    $mentionsUsers[$user->user_name] = $user->id;
                }
            }

            if (count($mentionsUsers)) {
                $mentions = Arr::join($mentionsUsers, ',');

                foreach ($mentionsUsers as $userName => $userId) {
                    $value = str_replace('@'.$userName,md5($userId.$this->id), $value);
                }

                $this->update([
                    'mentions' => $mentions,
                    $this->mentionField => $value
                ]);

                return;
            }
        }

        $this->update([
            'mentions' => '',
        ]);
    }

    public static function bootHasMention()
    {
        static::created(function ($model) {
            $model->updateMention();
        });
    }

    public function getMentionUserIds()
    {
        if ($this->mentions) {
            return Str::of($this->mentions)->explode(',');
        }
        
        return collect();
    }

    public function getMentionUsers($viewer)
    {
        $users = [];

        $mentions = $this->getMentionUserIds();
        foreach ($mentions as $userId) {
            if ($viewer && $viewer->checkBlock($userId)) {
                continue;
            }

            $user = User::findByField('id', $userId);
            if ($user) {
                $users[] = $user;
            }         
        }

        return $users;
    }

    public function getMentionUsersResource($viewer)
    {
        return UserResource::collection($this->getMentionUsers($viewer));
    }

    public function getMentionContent($viewer)
    {
        $value = $this->{$this->mentionField};
        $mentions = $this->getMentionUserIds();
        if ($value && $mentions) {
            foreach ($mentions as $userId) {
                $userName = '';
                
                $user = User::findByField('id', $userId);                
                if ($user) {
                    $userName = '@'.$user->user_name;
                }
                
                $value = str_replace(md5($userId.$this->id), $userName, $value);
            }
        }

        return $value;
    }

    public function sendMentionNotification($viewer, $usersKeep = [])
    {
        $users = $this->getMentionUsers($viewer);
        foreach ($users as $user) {
            if ($viewer->id != $user->id && $user->checkNotifySetting('mention') && !in_array($user->id, $usersKeep)) {
                Notification::send($user, $viewer, self::getMentionNotificationClass(), $this);
            }
        }
    }

    public function sendMentionNotificationWhenEdit($mentionsOld)
    {
        if ($this->mentions != $mentionsOld) {
            $mentions = Str::of($mentionsOld)->explode(',')->toArray();
            $mentionsCurrent = Str::of($this->mentions)->explode(',')->toArray();

            $mentionsKeep = array_intersect($mentions, $mentionsCurrent);
            $mentionsDelete = array_diff($mentionsCurrent, $mentions);

            /*foreach ($mentionsDelete as $userId) {
                $user = User::findByField('id', $userId);
                UserNotification::deleteFromAndSubject($this->getUser(), $user, self::getMentionNotificationClass(), $this);
            }*/

            $this->sendMentionNotification($this->getUser(), $mentionsKeep);
        }
    }
}
