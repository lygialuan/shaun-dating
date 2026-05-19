<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Packages\ShaunSocial\Chat\Models\ChatMessageItem;
use Packages\ShaunSocial\Chat\Repositories\Api\ChatRepository;
use Packages\ShaunSocial\Core\Enum\UserListMessageStatus;
use Packages\ShaunSocial\Core\Enum\UserListMessageType;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\UserFollow;
use Packages\ShaunSocial\Core\Models\UserListMember;
use Packages\ShaunSocial\Core\Models\UserListMessage;
use Packages\ShaunSocial\Core\Models\UserListMessageCron;
use Packages\ShaunSocial\PaidContent\Models\UserSubscriber;

class UserListMessageRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:user_list_message_run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send message to user list.';

    protected $chatRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ChatRepository $chatRepository)
    {
        $this->chatRepository = $chatRepository;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $messages = UserListMessage::where('status', UserListMessageStatus::INIT)->orderBy('id')->limit(setting('feature.item_per_page'))->get();
        $messages->each(function($message) {
            $user = $message->getUser();
            if (! $user) {
                $message->delete();
                return;
            }
            $userField = 'user_id';
            switch ($message->type) {
                case UserListMessageType::FOLLOWER:
                    $builder = UserFollow::where('follower_id', $message->user_id)->where('id', '>', $message->current)->orderBy('id')->limit(setting('feature.item_per_page'));
                    break;
                case UserListMessageType::FOLLOWING:
                    $builder = UserFollow::where('user_id', $message->user_id)->where('id', '>', $message->current)->orderBy('id')->limit(setting('feature.item_per_page'));
                    $userField = 'follower_id';
                    break;
                case UserListMessageType::LIST:
                    $builder = UserListMember::where('user_list_id', $message->list_id)->where('id', '>', $message->current)->orderBy('id')->limit(setting('feature.item_per_page'));
                    break;
                case UserListMessageType::SUBSCRIBER:
                    $builder = UserSubscriber::where('subscriber_id', $message->user_id)->where('id', '>', $message->current)->orderBy('id')->limit(setting('feature.item_per_page'));
                    break;
            }

            $items = $builder->get();
            $current = $message->current;
            foreach ($items as $item) {
                $current = $item->id;
                $userId = $item->{$userField};
                $user = User::findByField('id', $userId);
                if ($user) {
                    UserListMessageCron::create([
                        'user_id' => $userId,
                        'message_id' => $message->id
                    ]);
                }
            }

            if (count($items) < setting('feature.item_per_page')) {
                $message->update([
                    'status' => UserListMessageStatus::DONE
                ]);
            } else {
                $message->update([
                    'current' => $current
                ]);
            }
        });

        $crons = UserListMessageCron::orderBy('id')->limit(setting('feature.item_per_page'))->get();
        $crons->each(function ($cron) {
            $message = $cron->getUserListMessage();
            if (! $message) {
                $cron->delete();
                return;
            }

            $userFrom = $message->getUser();
            if (! $userFrom) {
                $cron->delete();
                return;
            }

            $subject = $message->getSubject();
            if (! $subject) {
                $cron->delete();
                return;
            }

            $user = $cron->getUser();
            if (! $user) {
                $cron->delete();
                return;
            }

            $isAdmin = $userFrom->isModerator();
            if ($user->canSendMessage($userFrom->id) || $isAdmin) {
                $result = $this->chatRepository->store_room($user->id, $userFrom);
        
                $item = ChatMessageItem::create([
                    'user_id' => $userFrom->id,
                    'subject_type' => $subject->getSubjectType(),
                    'subject_id' => $subject->id,            
                ]);

                $this->chatRepository->store_room_message([
                    'type' => 'post',
                    'content' => $message->content,
                    'items' => [$item->id],
                    'room_id' => $result['id']
                ], $userFrom);
            }
            $cron->delete();
        });
    }
}
