<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Packages\ShaunSocial\Chat\Http\Resources\ChatMessageResource;
use Packages\ShaunSocial\Chat\Http\Resources\ChatUserResource;
use Packages\ShaunSocial\Chat\Models\ChatMessage;
use Packages\ShaunSocial\Chat\Models\ChatMessageUser;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Chat\Models\ChatRoomMember;
use Packages\ShaunSocial\Core\Enum\UserDownloadStatus;
use Packages\ShaunSocial\Core\Http\Resources\Post\PostResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\UserDownload;
use Packages\ShaunSocial\Core\Models\UserDownloadItem;
use Packages\ShaunSocial\Core\Models\UserDownloadItemStatus;
use Packages\ShaunSocial\Core\Models\UserFollow;
use Packages\ShaunSocial\Core\Notification\Utility\UserDownloadNotification;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\UserPage\Http\Resources\UserPageAdminResource;
use Packages\ShaunSocial\UserPage\Http\Resources\UserPageResource;
use Packages\ShaunSocial\UserPage\Models\UserPageAdmin;
use RecursiveDirectoryIterator;
use RecursiveFilterIterator;
use RecursiveIteratorIterator;
use ZipArchive;
use Symfony\Component\HttpFoundation\File\File as FileCore;


class DownloadRecursiveFilterIterator extends RecursiveFilterIterator{
	public static $FILTERS = array(
        '.svn',
        '.git',
	);
	public function accept() :bool {
		return !in_array(
            $this->current()->getFilename(),
            self::$FILTERS,
            true
		);
	}
	
}

class UserDownloadRun extends Command
{
    use HasUserList;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:user_download_run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User download run task.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    protected $content_search = '<span style="display:none">{content}</span>';
    public function handle()
    {
        if (config('app.debug')) {
            return;
        }
        
        $userDownloads = UserDownload::where('status', '!=', UserDownloadStatus::DONE)->limit(setting('feature.item_per_page'))->get();
        $userDownloads->each(function($userDownload){
            $user = $userDownload->getUser();
            App::setLocale(getUserLanguage($user));
            
            if ($userDownload->status == UserDownloadStatus::ZIPPING) {
                // Zip file
                $zip = new ZipArchive();
                $downloadPath = storage_path('tmp').DIRECTORY_SEPARATOR.'download_'.$userDownload->user_id.'.zip';
                $zip->open($downloadPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                $rootPath = storage_path('download_data/'. $userDownload->user_id);
                $dirItr    = new RecursiveDirectoryIterator($rootPath);
                $filterItr = new DownloadRecursiveFilterIterator($dirItr);
                $files       = new RecursiveIteratorIterator($filterItr, RecursiveIteratorIterator::LEAVES_ONLY);
                
                foreach ($files as $name => $file)
                {
                    // Skip directories (they would be added automatically)
                    if (!$file->isDir())
                    {
                        // Get real and relative path for current file
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($rootPath) + 1);
                            // Add current file to archive
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                $zip->close();
                $downloadFile = File::store(new FileCore($downloadPath), [
                    'parent_id' => $userDownload->id,
                    'parent_type' => 'user_download'
                ]);
                deleteFolder($rootPath);

                $userDownload->update([
                    'status' => UserDownloadStatus::DONE,
                    'file_id' => $downloadFile->id
                ]);

                //Send notify
                Notification::send($user, $user, UserDownloadNotification::class, null, ['is_system' => true], 'shaun_core', false);
            }

            if ($userDownload->status == UserDownloadStatus::EXPORTING) {
                //Export info
                $global = [
                    'user_avatar' => getPathForDownload($user->getAvatar()),
                    'site_title'=> setting('site.title'),
                    'logo' => getPathForDownload(setting('site.logo')),
                    'user_id' => $userDownload->user_id,
                    'static_path' => '',
                ];

                $infoPath = storage_path('download_data/'. $userDownload->user_id.'/index.html');
                if (! file_exists($infoPath)) {
                    //add css file
                    appCopy(public_path('download_data/css/main.css'), storage_path('download_data/'. $userDownload->user_id.'/css/main.css'));

                    exportDownloadDataFile($userDownload->user_id, $user->getAvatar());
                    exportDownloadDataFile($userDownload->user_id, setting('site.logo'));

                    $gender = $user->getGender();
                    $userData = [
                        'name' => $user->getName(),
                        'href' => $user->getHref(),
                        'bio' => $user->bio,
                        'about' => $user->about,
                        'location' => $user->location,
                        'birthday' => $user->birthday,
                        'gender' => $gender ? $gender->getTranslatedAttributeValue('name') : '',
                        'links' => $user->links ?? '',
                        'follower_count' => $user->follower_count,
                        'following_count' => $user->following_count,
                        'page_title' => __('Home'),
                        'is_page' => $user->isPage()
                    ];

                    if ($user->isPage()) {
                        $pageInfo = $user->getPageInfo();
                        $categories = $user->getCategories();
                        $hashtags = $user->getPageHashtags();
                        $userData = array_merge($userData, [
                            'description' => $pageInfo->description,
                            'categories' => $categories->map(function ($item, $key) {
                                return $item->getTranslatedAttributeValue('name');
                            })->join(', '),
                            'hashtags' => $hashtags->pluck('name')->join(', '),
                            'websites' => $pageInfo->websites ?? '',
                            'open_hours' => $pageInfo->getOpenHours(),
                            'price' => $pageInfo->getPrice(),
                            'address' => $user->location,
                            'phone_number' => $pageInfo->phone_number,
                            'email' => $pageInfo->email,
                            'review_score' => $pageInfo->review_score,
                            'review_count' => $pageInfo->review_count
                        ]);
                    }

                    $userHtml = view('shaun_core::download_data.index', array_merge($userData, $global, $userDownload->getParams()))->render();
                    file_put_contents($infoPath, $userHtml);
                }
                $count = UserDownloadItem::where('user_id', $userDownload->user_id)->count();
                if (! $count) {
                    $userDownload->update([
                        'status' => UserDownloadStatus::ZIPPING,
                    ]);

                    return;
                }

                //Export child item
                $items = UserDownloadItem::where('user_id', $userDownload->user_id)->where('parent_id', '!=', 0)->orderBy('id')->limit(setting('feature.item_per_page'))->get();
                $checkChild = count($items) > 0;
                $items->each(function($item) use ($global) {
                    $this->exportFileItem($item, array_merge($global, ['static_path' => '../']));
                });

                //Export parent
                if (! $checkChild) {
                    $items = UserDownloadItem::where('user_id', $userDownload->user_id)->where('parent_id', 0)->orderBy('id')->limit(setting('feature.item_per_page'))->get();
                    $items->each(function($item) use ($global) {                        
                        $this->exportFileItem($item, $global);
                    });
                }
            }

            if ($userDownload->status == UserDownloadStatus::ITEM_RUNNING) {
                $userDownloadItems = UserDownloadItem::where('status', UserDownloadItemStatus::RUNNING)->limit(setting('feature.item_per_page'))->get();
                $userDownloadItems->each(function($userDownloadItem) use ($userDownload) {
                if ($userDownloadItem->type == 'room')  {
                        $params = $userDownloadItem->getParams();
                        $builder = ChatMessageUser::where('room_id', $params['id'])->where('user_id', $userDownloadItem->user_id)->where('is_delete', false)->orderBy('id', 'DESC')->limit(setting('feature.item_per_page'));
                        if ($userDownloadItem->id_min) {
                            $builder->where('id', '<', $userDownloadItem->id_min);
                        }
                        $messageId = $userDownloadItem->id_min;
                        $messages = $builder->get();
                        if (count($messages)) {
                            $messages->each(function($message) use ($userDownloadItem){                              
                                UserDownloadItem::create([
                                    'type' => 'message',
                                    'user_id' => $userDownloadItem->user_id,
                                    'status' => UserDownloadItemStatus::DONE,
                                    'params' => (new ChatMessageResource(ChatMessage::findByField('id', $message->message_id)))->toJson(),
                                    'parent_id' => $userDownloadItem->id,
                                    'package' => 'shaun_chat'
                                ]);  
                            });
                            $messageId = $messages->last()->id;
                            $userDownloadItem->update([
                                'id_min' => $messageId
                            ]);
                        } else {
                            if ($userDownloadItem->id_min > 0) {
                                $userDownloadItem->update([
                                    'status' => UserDownloadItemStatus::DONE
                                ]);
                            } else {                        
                                $params = $userDownload->getParams();
                                $count = UserDownloadItem::where('user_id', $userDownloadItem->user_id)->where('type','room')->where('id', '!=', $userDownloadItem->id)->count();
                                if (! $count) {
                                    $params['room_id'] = 0;
                                    $userDownload->update(['params' => json_encode($params)]);
                                }                                                
                                
                                $userDownloadItem->delete();
                            }
                        }
                        
                }
                });

                $count = UserDownloadItem::where('user_id', $userDownload->user_id)->where('status',UserDownloadItemStatus::RUNNING)->count();
                if (! $count) {
                    $userDownload->update([
                        'status' => UserDownloadStatus::EXPORTING,
                    ]);
                }
            }
            
            if ($userDownload->status == UserDownloadStatus::RUNNING) {
                $check = false;
                //post
                $params = $userDownload->getParams();
                $builder = Post::where('user_id', $userDownload->user_id)->orderBy('id', 'DESC')->limit(setting('feature.item_per_page'));
                $postId = 0;
                if (! empty($params['post_id'])) {
                    $builder->where('id', '<', $params['post_id']);                    
                    $postId = $params['post_id']; 
                }

                $posts = $builder->get();
                if (count($posts)) {
                    $check = true;            
                    $posts->each(function($post) use ($userDownload){      
                        UserDownloadItem::create([
                            'type' => 'post',
                            'user_id' => $userDownload->user_id,
                            'status' => UserDownloadItemStatus::DONE,
                            'params' => (new PostResource($post))->toJson()
                        ]);  
                    });
                    $postId = $posts->last()->id;
                }

                //admin 
                $adminPage = false;
                $hasPage = false;
                if (isset($params['admin_page'])) {
                    $adminPage = $params['admin_page'];
                }

                if (isset($params['page'])) {
                    $hasPage = $params['page'];
                }
                if (empty($params['page_check'])) {
                    if ($user->isPage()){
                        $admins = UserPageAdmin::where('user_page_id', $user->id)->get();
                        if (count($admins)) {
                            $adminPage = true;
                        }
                        $admins->each(function($admin) use ($userDownload) {
                            UserDownloadItem::create([
                                'type' => 'admin_page',
                                'user_id' => $userDownload->user_id,
                                'status' => UserDownloadItemStatus::DONE,
                                'params' => (new UserPageAdminResource($admin))->toJson(),
                                'package' => 'shaun_user_page'
                            ]);
                        });
                    } else {
                        $pages = UserPageAdmin::where('user_id', $user->id)->get();
                        if (count($pages)) {
                            $hasPage = true;
                        }
                        $pages->each(function($page) use ($userDownload) {
                            UserDownloadItem::create([
                                'type' => 'page',
                                'user_id' => $userDownload->user_id,
                                'status' => UserDownloadItemStatus::DONE,
                                'params' => (new UserPageResource($page->getPage()))->toJson(),
                                'package' => 'shaun_user_page'
                            ]);
                        });
                    }
                }

                //follower
                $builder = UserFollow::where('follower_id', $userDownload->user_id)->orderBy('id', 'DESC')->limit(setting('feature.item_per_page'));
                $followerId = 0;
                if (! empty($params['follower_id'])) {
                    $builder->where('id', '<', $params['follower_id']);                    
                    $followerId = $params['follower_id']; 
                }
                $users = $builder->get();
                $users = $this->filterUserList($users, $user);
                if (count($users)) {
                    $check = true;

                    $users->each(function($follow) use ($userDownload){    
                        $userTmp = User::findByField('id', $follow->user_id);
                        if ($userTmp) {
                            UserDownloadItem::create([
                                'type' => 'user_follower',
                                'user_id' => $userDownload->user_id,
                                'status' => UserDownloadItemStatus::DONE,
                                'params' => (new UserResource($userTmp))->toJson(),
                            ]);
                        }
                    });
                    $followerId = $users->last()->id;
                }

                //following
                $builder = UserFollow::where('user_id', $userDownload->user_id)->orderBy('id', 'DESC')->limit(setting('feature.item_per_page'));
                $followingId = 0;
                if (! empty($params['following_id'])) {
                    $builder->where('id', '<', $params['following_id']);                    
                    $followingId = $params['following_id']; 
                }

                $users = $builder->get();
                $users = $this->filterUserList($users, $user, 'follower_id');
                if (count($users)) {
                    $check = true;

                    $users->each(function($follow) use ($userDownload){
                        $userTmp = User::findByField('id', $follow->follower_id);

                        if ($userTmp) {
                            UserDownloadItem::create([
                                'type' => 'user_following',
                                'user_id' => $userDownload->user_id,
                                'status' => UserDownloadItemStatus::DONE,
                                'params' => (new UserResource($userTmp))->toJson(),
                            ]);
                        }
                    });
                    
                    $followingId = $users->last()->id;
                }

                //message
                $builder = ChatRoomMember::where('user_id', $userDownload->user_id)->orderBy('id', 'DESC')->limit(setting('feature.item_per_page'));
                $roomMemberId = 0;
                if (! empty($params['room_id'])) {
                    $builder->where('id', '<', $params['room_id']);
                    $roomMemberId = $params['room_id'];
                }

                $chatRooms = $builder->get();
                if (count($chatRooms)) {
                    $check = true;
                    $chatRooms->each(function($chatRoom) use ($userDownload, $user){                                                  
                        $room = ChatRoom::findByField('id', $chatRoom->room_id);
                        $members = $room->getMembersUser();

                        UserDownloadItem::create([
                            'type' => 'room',
                            'user_id' => $userDownload->user_id,
                            'params' => json_encode([
                                'id' => $chatRoom->room_id,
                                'name' => $room->getName($user),
                                'members' => json_decode(ChatUserResource::collection($members)->toJson(), true)
                            ]),
                            'package' => 'shaun_chat'
                        ]);
                    });
                    $roomMemberId = $chatRooms->last()->id;            
                }

                $status = UserDownloadStatus::RUNNING;
                if (! $check) {
                    $status = UserDownloadStatus::ITEM_RUNNING;  
                }

                $userDownload->update([
                    'status' => $status,
                    'params' => json_encode([
                        'post_id' => $postId,
                        'room_id' => $roomMemberId,
                        'follower_id' => $followerId,
                        'following_id' => $followingId,
                        'page_check' => true,
                        'page' => $hasPage,
                        'admin_page' => $adminPage
                    ])
                ]);
            }
        });
    }

    public function getDownloadItemPath($userDownloadItem)
    {
        if ($userDownloadItem->parent_id) {
            $parent = UserDownloadItem::findByField('id', $userDownloadItem->parent_id);
            $params = $parent->getParams();
            return storage_path('download_data/'. $userDownloadItem->user_id.'/'. $parent->type.'/'. $params['id'].'.html');
        }
        return storage_path('download_data/'. $userDownloadItem->user_id.'/'. $userDownloadItem->type.'.html');
    }

    public function exportFileItem($item, $global)
    {
        $path = $this->getDownloadItemPath($item);
        $itemHtml = view($item->package.'::download_data.'. $item->type.'_item', array_merge($global, $item->getParams(), ['download_data_user_id' => $item->user_id]))->render(). "\n\t\t".$this->content_search;
        if (! file_exists($path)) {
            $html = view($item->package.'::download_data.'. $item->type, array_merge($global,[
                'page_title' => $this->getPageTitle($item),
                'download_data_user_id' => $item->user_id
            ]))->render();
        } else {
            $html = file_get_contents($path);
        }
        $html = str_replace($this->content_search, $itemHtml, $html);

        $info = pathinfo($path);
        if (!file_exists($info['dirname'])) {
            mkdir($info['dirname'], 0777, true);
        }

        file_put_contents($path, $html);

        $item->delete();
    }

    public function getPageTitle($item)
    {
        switch ($item->type) {
            case 'room':
                return __('Rooms');
                break;
            case 'message':
                return __('Room detail');
                break;
            case 'post':
                return __('Posts');
                break; 
            case 'user_follower':
                return __('User follower');
                break; 
            case 'user_following':
                return __('User following');
                break; 
            case 'admin_page':
                return __('Admin page');
                break; 
            case 'page':
                return __('Page');
                break; 
        }
    }
}
