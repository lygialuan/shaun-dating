<?php


namespace Packages\ShaunSocial\Chat\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Chat\Http\Requests\CheckRoomOnlineValidate;
use Packages\ShaunSocial\Chat\Http\Requests\DeleteMessageItemValidate;
use Packages\ShaunSocial\Chat\Http\Requests\GetRoomValidate;
use Packages\ShaunSocial\Chat\Http\Requests\StoreAudioValidate;
use Packages\ShaunSocial\Chat\Http\Requests\StoreMessageValidate;
use Packages\ShaunSocial\Chat\Http\Requests\StoreRoomNotificationValidate;
use Packages\ShaunSocial\Chat\Http\Requests\StoreRoomStatusValidate;
use Packages\ShaunSocial\Chat\Http\Requests\StoreRoomUnseenValidate;
use Packages\ShaunSocial\Chat\Http\Requests\StoreRoomValidate;
use Packages\ShaunSocial\Chat\Http\Requests\UploadPhotoValidate;
use Packages\ShaunSocial\Chat\Http\Requests\UnsentMessageValidate;
use Packages\ShaunSocial\Chat\Http\Requests\UploadFileValidate;
use Packages\ShaunSocial\Chat\Http\Requests\StoreMessageSendFundValidate;
use Packages\ShaunSocial\Chat\Repositories\Api\ChatRepository;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Wallet\Notification\WalletTipNotification;
use Packages\ShaunSocial\Wallet\Repositories\Api\WalletRepository;

class ChatController extends ApiController
{
    protected $chatRepository;
    protected $walletRepository;

    public function __construct(ChatRepository $chatRepository, WalletRepository $walletRepository)
    {
        $this->chatRepository = $chatRepository;
        $this->walletRepository = $walletRepository;
        $this->middleware('has.permission:chat.allow')->except([
            'get_room'
        ]);
        parent::__construct();
    }

    public function store_room_message(StoreMessageValidate $request)
    {
        $request->mergeIfMissing([
            'content' => '',
            'items' => [],
            'client_message_id' => ''
        ]);

        $result = $this->chatRepository->store_room_message($request->only([
            'type', 'content', 'items', 'room_id', 'parent_message_id', 'client_message_id'
        ]), $request->user());

        return $this->successResponse($result);
    }

    public function get_room(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->chatRepository->get($request->user(), false, $page);

        return $this->successResponse($result);
    }

    public function get_room_request(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->chatRepository->get($request->user(), true, $page);

        return $this->successResponse($result);
    }

    public function detail(GetRoomValidate $request)
    {
        $result = $this->chatRepository->detail($request->id);
        
        return $this->successResponse($result);
    }

    public function get_room_message(GetRoomValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->chatRepository->get_room_message($request->id, $request->user() ,$page);
    
        return $this->successResponse($result);
    }

    public function store_room(StoreRoomValidate $request)
    {
        $result = $this->chatRepository->store_room($request->user_id, $request->user());
    
        return $this->successResponse($result);
    }

    public function get_request_count(Request $request) 
    {
        $result = $this->chatRepository->get_request_count($request->user());

        return $this->successResponse($result);
    }

    public function store_room_seen(GetRoomValidate $request)
    {
        $this->chatRepository->store_room_seen($request->id, $request->user());
    
        return $this->successResponse();
    }

    public function store_room_status(StoreRoomStatusValidate $request)
    {
        $this->chatRepository->store_room_status($request->room_id, $request->action, $request->user());
    
        return $this->successResponse();
    }

    public function store_room_notify(StoreRoomNotificationValidate $request) 
    {
        $this->chatRepository->store_room_notify($request->room_id, $request->action, $request->user());

        return $this->successResponse();
    }

    public function clear_room_message(GetRoomValidate $request)
    {
        $this->chatRepository->clear_room_message($request->id, $request->user());
        
        return $this->successResponse();
    }

    public function upload_photo(UploadPhotoValidate $request)
    {
        $result = $this->chatRepository->upload_photo($request->file('file'), $request->user()->id);
    
        return $this->successResponse($result);
    }

    public function delete_message_item(DeleteMessageItemValidate $request)
    {
        $this->chatRepository->delete_message_item($request->id);
        
        return $this->successResponse();
    }

    public function store_room_unseen(StoreRoomUnseenValidate $request)
    {
        $this->chatRepository->store_room_unseen($request->id, $request->user());
        
        return $this->successResponse();
    }

    public function check_room_online(CheckRoomOnlineValidate $request)
    {
        $result = $this->chatRepository->check_room_online($request->ids, $request->user());
    
        return $this->successResponse($result);
    }

    public function search_room(Request $request)
    {
        $result = $this->chatRepository->search_room($request->user(), false, $request->text);
    
        return $this->successResponse($result);
    }

    public function search_room_request(Request $request)
    {
        $result = $this->chatRepository->search_room($request->user(), true, $request->text);
    
        return $this->successResponse($result);
    }

    public function store_active_room(GetRoomValidate $request)
    {
        $this->chatRepository->store_active_room($request->id, $request->user());
        
        return $this->successResponse();
    }

    public function store_inactive_room(GetRoomValidate $request)
    {
        $this->chatRepository->store_inactive_room($request->id, $request->user());
        
        return $this->successResponse();
    }

    public function unsent_room_message(UnsentMessageValidate $request)
    {
        $this->chatRepository->unsent_room_message($request->id, $request->user());
        
        return $this->successResponse();
    }

    public function upload_file(UploadFileValidate $request)
    {
        $result = $this->chatRepository->upload_file($request->file('file'), $request->user()->id);

        return $this->successResponse($result); 
    }

    public function send_fund(StoreMessageSendFundValidate $request)
    {
        $data = $request->only([
            'user_id', 'amount'
        ]);
        $data['id'] = $data['user_id'];
        
        $result = $this->walletRepository->store_send($data, $request->user());

        if ($result['status']) {
            $user = User::findByField('id', $data['user_id']);
            Notification::send($user,$user, WalletTipNotification::class, $request->user(), ['is_system' => true], 'shaun_wallet', false, false);
            $data = [
                'type' => 'send_fund',
                'content' => '',
                'items' => [],
                'transaction_id' => $result['transaction_id'],
                'room_id' => $request->room_id,
                'parent_message_id' => 0
            ];
            $chatResult = $this->chatRepository->store_room_message($data, $request->user());
            return $this->successResponse($chatResult);
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function delete_room(GetRoomValidate $request)
    {
        $this->chatRepository->delete_room($request->id, $request->user());
        
        return $this->successResponse();
    }

    public function store_audio(StoreAudioValidate $request)
    {
        $request->mergeIfMissing([
            'parent_message_id' => 0
        ]);

        $result = $this->chatRepository->store_audio($request->room_id, $request->file, $request->parent_message_id, $request->user());

        return $this->successResponse($result);
    }
}
