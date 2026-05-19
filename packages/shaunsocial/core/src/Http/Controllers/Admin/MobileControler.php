<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;

class MobileControler extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.mobile.broadcast_message');
    }

    public function broadcast_message(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Broadcast message'),
            ],
        ];
        $title = __('Broadcast Message');

        return view('shaun_core::admin.mobile.broadcast_message', compact('breadcrumbs', 'title'));
    }

    public function store_broadcast_message(Request $request)
    {
        $request->validate(
            [
                'message' => 'required|max:255',
                'link' => 'string|nullable|url'
                
            ],
            [
                'message.required' => __('The message is required.'),
                'message.max' => __('The message must not be greater than 255 characters.'),
                'link.link' => __('The link should be valid.'),
            ]
        );

        $data = $request->all();
        $viewer = $request->user();
        $avatar = $viewer->getAvatar();

        if (checkFirebaseEnable()) {
            $message = CloudMessage::fromArray([
                'topic' => 'global',
                'notification' => [
                    'body' => $data['message'],
                    'image' => $avatar,
                ],
                'android' => [
                    "notification" => [
                        'image' => $avatar
                    ],
                ],
                'apns' => [
                    'payload' => [
                        "aps" => [
                            "mutable-content" =>1
                        ]
                    ],
                    'fcm_options' => [
                        "image" => $avatar
                    ]
                ],
                'data' => [
                    'url' => $data['link'] ?? '',
                ],
            ]);
            $messaging = app('firebase.messaging');
            try {
                $result = $messaging->send($message);
            } catch (Exception $e) {
                return redirect()->route('admin.mobile.broadcast_message')->with([
                    'admin_message_error' => $e->getMessage()
                ]);
            }
        } else {
            return redirect()->route('admin.mobile.broadcast_message')->with([
                'admin_message_error' => __('Please config google firebase server key.')
            ]);
        }

        return redirect()->route('admin.mobile.broadcast_message')->with([
            'admin_message_success' => __('Broadcast message has been successfully sent.'),
        ]);
    }
}
