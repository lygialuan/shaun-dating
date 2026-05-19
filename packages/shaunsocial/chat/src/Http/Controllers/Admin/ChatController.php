<?php


namespace Packages\ShaunSocial\Chat\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Chat\Models\ChatMessage;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.chat.manage');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Messages'),
            ],
        ];

        $title = __('Messages');
        $types = [
            '1' => __('Content'),
            '2' => __('User'),
        ];

        $builder = ChatMessage::orderBy('chat_messages.id','DESC')->select(DB::raw('DISTINCT '.env('DB_PREFIX').'chat_messages.id'));

        $type = $request->query('type', 1);
        $text = $request->query('text', '');
        if ($text) {
            switch ($type) {
                case 1:
                    $builder->whereFullText('content', $text);
                    break;
                case 2:                    
                    $builder->join('chat_room_members', function ($join) use ($text) {
                        $join->on('chat_room_members.room_id', '=', 'chat_messages.room_id')->where('chat_room_members.user_name', 'LIKE', '%'.$text.'%');
                    });
                    break;
            }
        }

        $messages = $builder->paginate(setting('feature.item_per_page'));
        $messages->setCollection($messages->getCollection()->map(function ($value, $key){
            return ChatMessage::findByField('id', $value->id);
        }));

        return view('shaun_chat::admin.chat.index', compact('breadcrumbs', 'title', 'messages', 'types', 'type', 'text'));
    }

    public function detail(Request $request)
    {
        if (env('ADMIN_SHOW_MANAGE_MESSAGE')) {
            $breadcrumbs = [
                [
                    'title' => __('Dashboard'),
                    'route' => 'admin.dashboard.index',
                ],
                [
                    'title' => __('Messages'),
                    'route' => 'admin.chat.index',
                ],
                [
                    'title' => __('Room detail'),
                ],
            ];
        } else {
            $breadcrumbs = [
                [
                    'title' => __('Dashboard'),
                    'route' => 'admin.dashboard.index',
                ],
                [
                    'title' => __('Room detail'),
                ],
            ];
        }
        
        
        $room = ChatRoom::findOrFail($request->id);
        $title = $room->getName();
        $builder = ChatMessage::where('room_id', $request->id)->orderBy('id','DESC');
        $text = $request->query('text', '');
        if ($text) {
            $builder->whereFullText('content', $text);
        }

        $messages = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_chat::admin.chat.detail', compact('breadcrumbs', 'title', 'room', 'messages', 'text'));        
    }
}
