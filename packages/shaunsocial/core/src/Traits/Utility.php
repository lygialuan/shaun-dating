<?php


namespace Packages\ShaunSocial\Core\Traits;

use Exception;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Models\LayoutContent;
use Packages\ShaunSocial\Core\Models\LayoutPage;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Packages\ShaunSocial\Core\Http\Resources\User\UserMeResource;
use Packages\ShaunSocial\Core\Http\Resources\Utility\LayoutContentResource;
use Packages\ShaunSocial\Core\Http\Resources\Utility\MenuResource;
use Packages\ShaunSocial\Core\Models\CodeVerify;
use Packages\ShaunSocial\Core\Models\MenuItem;
use Packages\ShaunSocial\Core\Models\UserNotification;
use Packages\ShaunSocial\Core\Models\InviteHistory;
use Packages\ShaunSocial\Gateway\Models\Gateway;
use Kreait\Firebase\Messaging\CloudMessage;
use Packages\ShaunSocial\Core\Exceptions\PermissionHttpException;
use Packages\ShaunSocial\Core\Models\Menu;
use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Core\Models\SmsProvider;
use Packages\ShaunSocial\Core\Models\UserActionLog;

trait Utility
{
    use ApiResponser;

    public function makeContentHtml($content)
    {
        if (! checkAppApi()) {
            return htmlspecialchars($content ?? '', ENT_NOQUOTES, 'UTF-8');
        }
        
        return $content;
    }

    public function filterNotification($results, $userId ,$fieldName = 'notify_id')
    {
        $notifications = collect();
        $cacheAll = Cache::get('user_notification_mark_all_as_read_'. $userId);
        foreach ($results as $result) {
            if ($cacheAll) {
                $cacheDetail = Cache::get('user_notification_mark_all_as_read_item_'.$result->{$fieldName});
                if (! $cacheDetail || $cacheDetail < $cacheAll) {
                    UserNotification::clearCacheQueryFieldsByAttribute('id', $result->{$fieldName});
                    Cache::set('user_notification_mark_all_as_read_item_'.$result->{$fieldName}, now()->timestamp);
                }
            }
            $notification = UserNotification::findByField('id', $result->{$fieldName});
            if ($notification->checkExists()) {
                $notifications->push($notification);
            } else {
                $notification->delete();
            }
        }

        return $notifications;
    }

    public function filterPostListForSource($posts, $viewer, $inSource = false)
    {
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = $viewer ? $viewer->isModerator() : false;
        return $posts->filter(function ($post, $key) use ($viewer, $inSource, $isAdmin, $viewerId) {
            //check delete user
            if (! $isAdmin && ! $post->checkShowWithSource($viewerId)) {
                return false;
            }
            if ($inSource) {
                $post->setIn('source', $inSource);
            }
            return $post->getUser() ? true : false;
        });
    }

    public function createLayoutPage($data, $layoutContents)
    {
        $page = LayoutPage::create($data);

        $this->addLayoutContents($page, $layoutContents);
    }

    public function addLayoutContents($page, $layoutContents)
    {
        foreach ($layoutContents as $position => $contents) {
            foreach ($contents as $content) {
                $content['page_id'] = $page->id;
                $content['position'] = $position;

                LayoutContent::create($content);
            }            
        }
    }

    public function getLayoutContent($request, $pageId, $viewType)
    {
        $user = $request->user();
        $roleId = $user ? $user->role_id : config('shaun_core.role.id.guest');
        $layoutContents = LayoutContent::getContents($pageId, $viewType, $roleId);
        foreach ($layoutContents as $key => $contents) {
            if ($contents) {
                $contents = $contents->filter(function ($item, $key) use ($request, $roleId) {
                    return $item->hasPermission($roleId);
                });

                $contents = LayoutContentResource::collection($contents)->toArray($request);
                $contents = collect($contents)->filter(function ($item, $key) {
                    return $item['data'] !== false;
                })->values();
                
                $layoutContents[$key] = $contents;
            }
        }
        return $layoutContents;
    }

    public function getMenu($request, $alias)
    {
        $user = $request->user();
        $roleId = $user ? $user->role_id : config('shaun_core.role.id.guest');
        $isAdmin = $user ? $user->isModerator() : false;

        $mainMenus = Menu::getAll();
        $menu = $mainMenus->first(function ($value, $key) use ($alias) {
            return $value->alias == $alias;
        });

        $menus = [];
        if ($menu) {
            $menus = MenuItem::getItem($menu->id);

            if ($menus) {
                $menus = $menus->filter(function ($item, $key) use ($roleId) {
                    return $item->hasPermission($roleId) && $item->is_active;
                });

                $menus = $menus->map(function ($item, $key) use ($roleId, $isAdmin) {
                    if ($item->childs) {
                        $item->childs = $item->childs->filter(function ($item, $key) use ($roleId, $isAdmin) {
                            return ($item->hasPermission($roleId) || $isAdmin) && $item->is_active;
                        });
                    }
                    return $item;
                });
            }
        }

        return MenuResource::collection($menus);
    }

    public function validateSpam($data)
    {
        $result = ['status' => false, 'message' => __('Method detect spam invalidate.')];
        if (!empty($data['hash'])) {
            //check app use api key with hash 
            if (empty($data['time'])) {
                return $result;
            }

            $subTime = now()->timestamp - (double)$data['time'];
            if (abs($subTime) > config('shaun_core.spam.sub_time')) {
                return $result;
            }
            $hash = $data['hash'];
            unset($data['hash']);
            $data['key'] = setting('app.api_secret_key');
            if (md5(implode('',$data)) == $hash) {
                $result['status']  = true;
                $result['message']  = '';
            }
            
            return $result;
        }
        
        //check web use reptcha
        if (empty($data['token'])) {
            return $result;
        }

        if (setting('spam.capcha_type') == 'recaptcha') {
        $client = new Client();
        $response = $client->post(
            'https://www.google.com/recaptcha/api/siteverify',
            ['form_params'=>
                [
                    'secret'=>setting('spam.recapcha_private_key'),
                    'response'=>$data['token']
                 ]
            ]
        );
        $body = json_decode((string)$response->getBody());
        if ($body->success) {
            $result['status']  = true;
            $result['message']  = '';
        }
        } else {
            $client = new Client();
            $response = $client->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                ['form_params'=>
                    [
                        'secret'=>setting('spam.turnstile_secret_key'),
                        'response'=> $data['token'],
                        'remoteip' => $_SERVER['REMOTE_ADDR']
                    ]
                ]
            );
            $body = json_decode((string)$response->getBody());
            if ($body->success) {
                $result['status']  = true;
                $result['message']  = '';
            }
        }

        return $result;
    }

    public function createCodeVerify($userId, $type, $email = '',$length = 6)
    {
        $code = fake()->randomNumber($length);
        do {       
            $data = CodeVerify::where('code', $code)->get();
            if (!$data->count()) {
                break;
            }
            $code = fake()->randomNumber($length);
        }
        while (true);

        CodeVerify::where('user_id', $userId)->where('type', $type)->delete();

        CodeVerify::create([
            'user_id' => $userId,
            'type' => $type,
            'code' => $code,
            'email' => $email
        ]);

        return $code;
    }

    public function checkCodeVerify($userId, $type, $code, $email = '',$time = null)
    {
        if (!$time) {
            $time = config('shaun_core.validation.time_verify');
        }

        $result = ['status' => false, 'message' => __('Your code is incorrect. Please try again.')];

        $builder = CodeVerify::where('type', $type)->where('code', $code);
        if ($userId) {
            $builder->where('user_id', $userId);
        }
        
        $codeVerify = $builder->first();
        
        if (! $codeVerify ) {
            return $result;
        }
        if ($email && $codeVerify->email != $email) {
            return $result;
        }
        if ($codeVerify->created_at->timestamp < now()->timestamp - $time) {
            $result['message'] = __('The code has expired.');
            return $result;
        }

        return ['status' => true, 'codeVerify' => $codeVerify];
    }

    public function pushNotify($user, $body, $avatar, $data, $tag = '', $types = [])
    {
        $fcmTokens = $user->getFcmTokens();

        if (count($types)) {
            $fcmTokens = $fcmTokens->filter(function ($value, $key) use ($types) {
                return in_array($value->type, $types);
            });
        }

        if (! count($fcmTokens)) {
            return;
        }
        if (! checkFirebaseEnable()) {
            return;
        }
        $messaging = app('firebase.messaging');
        $message = CloudMessage::fromArray([
            'notification' => [
                'body' => $body,
                'image' => $avatar
            ],
            'android' => [
                'notification' => [
                    'image' => $avatar,
                    'tag' => $tag
                ],
            ],
            'apns' => [
                'payload' => [
                    "aps" => [
                        'sound' => 'default',
                        'mutable-content' => 1,
                        'apns-collapse-id' => $tag
                    ]
                ],
                'fcm_options' => [
                    'image' => $avatar
                ]
            ],
            'webpush' => [
                'notification' => [
                    'title' => setting('site.title'),
                    'body' => $body,
                    'image' => $avatar,
                    'icon' => setting('pwa.icon')
                ],
                'fcm_options' => [
                    'link' => $data['url']
                ]
            ],
            'data' => $data
        ]);
        
        $tokens = $fcmTokens->pluck('token');
        try {
            $sendReports = $messaging->sendMulticast($message, $tokens->toArray());
            $invalidTokens = $sendReports->invalidTokens();
            if ($invalidTokens) {
                foreach ($invalidTokens as $token) {
                    $tokenDelete = $fcmTokens->first(function ($value, $key) use ($token) {
                        return $value->token = $token;
                    });

                    $tokenDelete->delete();
                }
            }
            $unknownTokens = $sendReports->unknownTokens();
            if ($unknownTokens) {
                foreach ($unknownTokens as $token) {
                    $tokenDelete = $fcmTokens->first(function ($value, $key) use ($token) {
                        return $value->token = $token;
                    });

                    $tokenDelete->delete();
                }
            }
        } catch (Exception $e) {
        }
    }

    public function checkInviteLimit($viewer, $count)
    {
        $countCurrent = InviteHistory::where('user_id', $viewer->id)->where('created_at', '>', now()->subDay())->count();
        $countSpam = getInviteLimit();

        return ($countCurrent + $count <= $countSpam);
    }

    public function getGateways($currencyCode)
    {
        $gateways = Gateway::getAll();
        $gateways = $gateways->filter(function ($item, $key) use ($currencyCode) {
            return $item->checkSupportCurrency($currencyCode);
        });

        return $gateways;
    }


    public function loginUserBase($user, $request, $parent = null)
    {
        $token = $user->createToken('authToken')->plainTextToken;

        if (! $user->has_active) {
            $user->update(['has_active' => true]);
        }

        if ($parent) {
            $user->setParent($parent);
        }

        return [
            'user' => new UserMeResource($user),
            'token' => $token,
        ];
    }

    public function downloadCsvFile($data, $fileName)
    {
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download  
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$fileName}");
        header("Content-Transfer-Encoding: binary");

        ob_start();
        $df = fopen("php://output", 'w');
        foreach ($data as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        $result = ob_get_clean();
        echo $result;
        die();
    }
    
    public function checkPermissionHaveValue($key, $value, $user)
    {
        if (! $user->isModerator()) {
            $permissionValue = $user->getPermissionValue($key);
            if ($permissionValue && $value && $value > $permissionValue) {
                $message = Permission::getMessageErrorByKey($key, $permissionValue);
                throw new PermissionHttpException($message); 
            }                        
        }
    }

    public function checkPermissionActionLog($key, $type, $user)
    {
        if (! $user->isModerator()) {
            $permissionValue = $user->getPermissionValue($key);
            if ($permissionValue) {
                $count = UserActionLog::getCount($user->id, $type);
                if ($count >= $permissionValue) {
                    $message = Permission::getMessageErrorByKey($key, $permissionValue);
                    throw new PermissionHttpException($message); 
                }
            }  
        } 
    }

    public function checkPermission($key, $user) {
        if (! $user->isModerator() && ! $user->hasPermission($key)) {
            $message = Permission::getMessageErrorByKey($key);
            throw new PermissionHttpException($message); 
        }
    }

    public function sendPhoneNumber($userId, $phone, $text)
    {
        $key = 'send_message_phone_'.$userId.$phone;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            return [
                'status' => false,
                'message' => __('Please wait for a minute to try again.')
            ];
        }
        
        $provider = SmsProvider::getDefault();
        $result = $provider->sendSms($text, $phone);
        if ($result['status']) {
            RateLimiter::increment($key, config('shaun_core.core.limit_send_phone'));
        }
        
        return $result;
    }
}
