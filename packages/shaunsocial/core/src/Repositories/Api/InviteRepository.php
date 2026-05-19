<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Http\Resources\Invite\InviteResource;
use Packages\ShaunSocial\Core\Models\Invite;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Support\Facades\Mail;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Enum\InviteType;
use Packages\ShaunSocial\Core\Models\InviteHistory;
use Packages\ShaunSocial\Core\Notification\Invite\InviteJoinNotification;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Traits\CacheSearchPagination;

class InviteRepository
{
    use CacheSearchPagination;

    public function info($viewer)
    {
        $result = Cache::remember('invite_info_'.$viewer->id, config('shaun_core.cache.time.invite_info'), function () use ($viewer) {
            $totalSent = Invite::where('user_id', $viewer->id)->where('type', InviteType::INVITE)->count();
            $totalReferral = Invite::where('user_id', $viewer->id)->where('type', InviteType::REFERRAL)->count();
            return [
                'total_sent' => $totalSent,
                'total_referral' => $totalReferral,
            ];
        });

        return $result;
    }

    public function get($query, $type, $page, $viewer)
    {
        switch ($type) {
            case InviteType::INVITE->value:
                $builder = Invite::where('user_id', $viewer->id)->where('type', $type)->orderBy('updated_at', 'DESC');
                if ($query) {
                    $builder->where('email', 'LIKE', '%'.$query.'%');
                }
                break;
            case InviteType::REFERRAL->value:
                $builder = Invite::where('user_id', $viewer->id)->where('type', $type)->orderBy('invites.updated_at', 'DESC');
                if ($query) {
                    $builder->join('users', function ($join) use ($query) {
                        $join->on('users.id', '=', 'invites.new_user_id')->where(function ($select) use ($query){
                            $select->where('users.name', 'LIKE', '%'.$query.'%')->orWhere('users.user_name', 'LIKE', '%'.$query.'%');
                        });
                    });
                }
                break;
        }

        $invites = $this->getCacheSearchPagination('invite_'.$viewer->id.'_'.$query.'_'.$type.'_'.$page, $builder, $page, 0, config('shaun_core.cache.time.short'));
        $invitesNextPage = $this->getCacheSearchPagination('invite_'.$viewer->id.'_'.$query.'_'.$type.'_'.$page, $builder, $page + 1, 0, config('shaun_core.cache.time.short'));

        return [
            'items' => InviteResource::collection($invites),
            'has_next_page' => count($invitesNextPage) ? true : false
        ];
    }

    public function store($data, $viewer)
    {
        $emails = $data['emails'];
        $emails = Str::of($emails)->explode(',');
        
        $this->send_invite($emails, $data['message'], $viewer);
    }

    public function check($code, $viewer)
    {
        $user = User::findByField('ref_code', $code);
        if ($user) {
            Invite::create([
                'user_id' => $user->id,
                'new_user_id' => $viewer->id,
                'type' => InviteType::REFERRAL
            ]);
    
            //add follow
            $userIds = [];
            if (setting('feature.auto_follow')) {
                $userIds = explode(',', setting('feature.auto_follow'));
            }

            if (! in_array($user->id, $userIds)) {
                $viewer->addFollow($user->id, $user->isPage());
            }
    
            //send notify
            if ($user->checkNotifySetting('invite')) {
                Notification::send($user, $viewer, InviteJoinNotification::class, $viewer);
            }
        }
    }

    public function store_csv($emails, $viewer)
    {
        $this->send_invite($emails, '', $viewer);
    }

    protected function send_invite($emails, $message, $viewer)
    {
        $queue = false;
        if (count($emails) > config('shaun_core.invite.number_email_send_queue')) {
            $queue = true;
        }
        foreach ($emails as $email) {
            $email = trim($email);
            if (! $email) {
                continue;
            }
            //Send email
            Mail::send('invite_email', $email, [
                'ref_link' => $viewer->getRefUrl(),
                'message' => $message ? $message : '',
                'sender_title' => $viewer->getName(),
                'mail_many' => $queue
            ]);
            
            $invite = Invite::getInviteByUserAndEmail($viewer->id, $email);
            if (! $invite) {
                Invite::create([
                    'user_id' => $viewer->id,
                    'email' => $email
                ]);
            } else {
                $invite->touch();
            }

            InviteHistory::create([
                'user_id' => $viewer->id
            ]);
        }
    }
}
