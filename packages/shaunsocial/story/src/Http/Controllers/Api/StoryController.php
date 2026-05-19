<?php


namespace Packages\ShaunSocial\Story\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Story\Http\Resources\StoryBackgroundResource;
use Packages\ShaunSocial\Story\Models\StoryBackground;
use Packages\ShaunSocial\Story\Repositories\Api\StoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Models\UserActionLog;
use Packages\ShaunSocial\Story\Http\Requests\DeleteStoryItemValidate;
use Packages\ShaunSocial\Story\Http\Requests\DetailStoryValidate;
use Packages\ShaunSocial\Story\Http\Requests\GetCurrentValidate;
use Packages\ShaunSocial\Story\Http\Requests\GetDetailItemValidate;
use Packages\ShaunSocial\Story\Http\Requests\GetViewItemValidate;
use Packages\ShaunSocial\Story\Http\Requests\StoreMessageValidate;
use Packages\ShaunSocial\Story\Http\Requests\StoreStoryValidate;
use Packages\ShaunSocial\Story\Http\Requests\StoreStoryViewItemValidate;
use Packages\ShaunSocial\Story\Http\Requests\ShareStoryValidate;
use Packages\ShaunSocial\Story\Http\Requests\UploadVideoValidate;
use Packages\ShaunSocial\Story\Http\Resources\StorySongResource;
use Packages\ShaunSocial\Story\Models\StorySong;

class StoryController extends ApiController
{
    protected $storyRepository;

    public function getWhitelistForceLogin()
    {
        return [
            'detail'
        ];
    }

    public function __construct(StoryRepository $storyRepository)
    {
        $this->storyRepository = $storyRepository;
        $current = Route::getCurrentRoute();
        switch ($current->getActionMethod()) {
            case 'store':
                $this->middleware('has.permission:story.allow_create');
                break;
            case 'store_message':
            case 'share_message':
                $this->middleware('has.permission:chat.allow');
                break;
        }

        parent::__construct();
    }
    
    public function config()
    {
        $stories = StoryBackground::getAll();
        $songs = StorySong::getDefaultAll();
        return $this->successResponse([
            'backgrounds' => StoryBackgroundResource::collection($stories),
            'songs' => StorySongResource::collection($songs)
        ]);
    }

    public function search_song(Request $request)
    {
        $result = $this->storyRepository->search_song($request->text);

        return $this->successResponse($result);
    }

    public function get(Request $request)
    {
        $page = $request->page ? $request->page : 1;
        
        $filters = $request->filters;

        $result = $this->storyRepository->get($request->user(), $page, $filters);

        return $this->successResponse($result);
    }

    public function store(StoreStoryValidate $request)
    {
        $request->mergeIfMissing([
            'song_id' => 0,
            'content' => '',
            'background_id' => 0,
            'content_color' => ''
        ]);
        $result = $this->storyRepository->store($request->all(), $request->file('photo'), $request->user());

        UserActionLog::create([
            'user_id' => $request->user()->id,
            'type' => 'create_story'
        ]);

        return $this->successResponse($result);
    }

    public function detail(DetailStoryValidate $request)
    {
        $result = $this->storyRepository->detail($request->id);

        return $this->successResponse($result);
    }

    public function delete_item(DeleteStoryItemValidate $request)
    {
        $this->storyRepository->delete($request->id);

        return $this->successResponse();
    }

    public function store_view_item(StoreStoryViewItemValidate $request)
    {
        $this->storyRepository->store_view_item($request->id, $request->user());

        return $this->successResponse();
    }

    public function get_view(GetViewItemValidate $request)
    {
        $page = $request->page ? $request->page : 1;
        $result = $this->storyRepository->get_view($request->id, $page, $request->user());

        return $this->successResponse($result);
    }
    
    public function my(Request $request)
    {
        $page = $request->page ? $request->page : 1;
        $result = $this->storyRepository->my($request->user(), $page);

        return $this->successResponse($result);
    }

    public function store_message(StoreMessageValidate $request)
    {
        $this->storyRepository->store_message($request->all(),$request->user());

        return $this->successResponse();
    }

    public function detail_item(GetDetailItemValidate $request)
    {
        $result = $this->storyRepository->detail_item($request->id, $request->user());

        return $this->successResponse($result);
    }

    public function detail_in_list(DetailStoryValidate $request)
    {
        $result = $this->storyRepository->detail_in_list($request->id);

        return $this->successResponse($result);
    }

    public function share_message(ShareStoryValidate $request)
    {
        $this->storyRepository->share_message($request->all(),$request->user());

        return $this->successResponse();
    }

    public function upload_video(UploadVideoValidate $request)
    {
        $result = $this->storyRepository->upload_video($request->file('file'),$request->get('is_converted', false), $request->user()->id);
        
        if ($result['status']) {
            return $this->successResponse($result['item']);
        } else {
            return $this->errorNotFound($result['message']);
        } 
    }
}