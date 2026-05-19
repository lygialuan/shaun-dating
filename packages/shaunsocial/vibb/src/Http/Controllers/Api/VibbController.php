<?php


namespace Packages\ShaunSocial\Vibb\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Vibb\Repositories\Api\VibbRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\Post\GetPostProfileValidate;
use Packages\ShaunSocial\Core\Models\UserActionLog;
use Packages\ShaunSocial\Vibb\Http\Resources\VibbSongResource;
use Packages\ShaunSocial\Vibb\Models\VibbSong;
use Packages\ShaunSocial\Vibb\Http\Requests\UploadVideoValidate;
use Packages\ShaunSocial\Vibb\Http\Requests\StoreVibbValidate;

class VibbController extends ApiController
{
    protected $vibbRepository;

    public function __construct(VibbRepository $vibbRepository)
    {
        if (! setting('shaun_vibb.enable')) {
            throw new MessageHttpException(__('Do not support this method.'));
        }

        $this->vibbRepository = $vibbRepository;
        $current = Route::getCurrentRoute();
        switch ($current->getActionMethod()) {
            case 'store':
                $this->middleware('has.permission:vibb.allow_create');
                break;
        }

        parent::__construct();
    }
    
    public function config()
    {
        $songs = VibbSong::getDefaultAll();
        return $this->successResponse([
            'songs' => VibbSongResource::collection($songs)
        ]);
    }

    public function search_song(Request $request)
    {
        $result = $this->vibbRepository->search_song($request->text);

        return $this->successResponse($result);
    }

    public function upload_video(UploadVideoValidate $request)
    {
        $result = $this->vibbRepository->upload_video($request->file('file'),$request->get('is_converted', false), $request->user()->id);
        
        if ($result['status']) {
            return $this->successResponse($result['item']);
        } else {
            return $this->errorNotFound($result['message']);
        } 
    }

    public function store(StoreVibbValidate $request)
    {
        $request->mergeIfMissing([
            'content' => '',
            'comment_privacy' => 'everyone',
            'song_id' => 0,
            'is_converted' => false
        ]);

        $result = $this->vibbRepository->store($request->only(['content', 'item_id', 'is_converted', 'content_warning_categories', 'song_id', 'comment_privacy'
        ]), $request->user());

        UserActionLog::create([
            'user_id' => $request->user()->id,
            'type' => 'create_vibb'
        ]);

        return $this->successResponse($result);
    }

    public function for_you(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->vibbRepository->for_you($request->user(), $page);

        return $this->successResponse($result);
    }

    public function following(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->vibbRepository->following($request->user(), $page);

        return $this->successResponse($result);
    }

    public function profile(GetPostProfileValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->vibbRepository->profile($request->id, $page, $request->user());

        return $this->successResponse($result);
    }

    public function my(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->vibbRepository->my($page, $request->user());

        return $this->successResponse($result);
    }
}