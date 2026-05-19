<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Enum\PostPaidType;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Hashtag\GetHashtagValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\DeletePostItemValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\DeletePostValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\EditPostValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\GetPostProfileValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\GetPostValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\StorePostValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\UploadPhotoValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\UploadVideoValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\UploadFileValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\EditContentWarningValidate;
use Packages\ShaunSocial\Core\Http\Requests\Utility\FetchLinkValidate;
use Packages\ShaunSocial\Core\Repositories\Api\PostRepository;
use Packages\ShaunSocial\Core\Http\Requests\Post\GetPollVoteValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\StorePollVoteValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\EditCommentPrivacyValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\StorePinHomeValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\StorePinProfileValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\StoreStopCommentValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\GetByIdsValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\GetNewHomeValidate;
use Packages\ShaunSocial\Core\Models\UserActionLog;

class PostController extends ApiController
{
    protected $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
        parent::__construct();
    }

    public function upload_photo(UploadPhotoValidate $request)
    {
        $result = $this->postRepository->upload_photo($request->file('file'), $request->user()->id);
    
        return $this->successResponse($result);
    }

    public function upload_thumb(UploadPhotoValidate $request)
    {
        $result = $this->postRepository->upload_thumb($request->file('file'), $request->user()->id);
    
        return $this->successResponse($result);
    }

    public function get_by_ids(GetByIdsValidate $request)
    {
        $result = $this->postRepository->get_by_ids($request->ids, $request->user());

        return $this->successResponse($result);
    }

    public function store(StorePostValidate $request)
    {
        $request->mergeIfMissing([
            'parent_id' => 0,
            'content' => '',
            'items' => [],
            'close_minute' => 0,
            'comment_privacy' => 'everyone',
            'source_id' => 0,
            'source_type' => '',
            'subject_type' => '',
            'subject_id' => 0,
            'is_paid' => 0,
            'content_amount' => 0,
            'paid_type' => PostPaidType::PAYPERVIEW->value,
            'thumb_file_id' => 0,
        ]);

        $result = $this->postRepository->store($request->only([
            'type', 'content', 'items', 'parent_id', 'content_warning_categories', 'close_minute', 'comment_privacy', 'source_id', 'source_type','subject_type', 'subject_id', 'is_paid', 'content_amount', 'paid_type', 'thumb_file_id'
        ]), $request->user());

        UserActionLog::create([
            'user_id' => $request->user()->id,
            'type' => 'create_post'
        ]);

        return $this->successResponse($result);
    }

    public function profile(GetPostProfileValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->profile($request->id, $page, $request->user());

        return $this->successResponse($result);
    }

    public function profile_media(GetPostProfileValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->profile_media($request->id, $page, $request->user());

        return $this->successResponse($result);
    }

    public function home(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->home($request->user(), $page, getUniqueFromRequest($request));

        return $this->successResponse($result);
    }

    public function delete(DeletePostValidate $request)
    {
        $this->postRepository->delete($request->id);

        return $this->successResponse();
    }

    public function get(GetPostValidate $request)
    {
        $result = $this->postRepository->get($request->id, $request->user(), $request->ip());

        return $this->successResponse($result);
    }

    public function fetch_link(FetchLinkValidate $request)
    {
        $result = $this->postRepository->fetch_link($request->url, $request->user()->id);
        if ($result !== null) {
            return $this->successResponse($result);
        } else {
            return $this->errorNotFound(__('This link error.'));
        }
    }

    public function delete_item(DeletePostItemValidate $request)
    {
        $this->postRepository->delete_item($request->id);

        return $this->successResponse();
    }

    public function hashtag(GetHashtagValidate $request)
    {
        $page = $request->page ? $request->page : 1;
        $result = $this->postRepository->hashtag($request->hashtag, $page, $request->user());

        return $this->successResponse($result);
    }

    public function discovery(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->discovery($request->user(), $page, getUniqueFromRequest($request));

        return $this->successResponse($result);
    }

    public function watch(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->watch($request->user(), $page, getUniqueFromRequest($request));

        return $this->successResponse($result);
    }

    public function media(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->media($request->user(), $page);

        return $this->successResponse($result);
    }

    public function store_edit(EditPostValidate $request)
    {
        $result = $this->postRepository->store_edit($request->id, $request->content, $request->user());
    
        return $this->successResponse($result);
    }

    public function upload_video(UploadVideoValidate $request)
    {
        $result = $this->postRepository->upload_video($request->file('file'), $request->get('is_converted', false), $request->get('convert_now', false),$request->user()->id);
        
        if ($result['status']) {
            return $this->successResponse($result['item']);
        } else {
            return $this->errorNotFound($result['message']);
        }
    }

    public function upload_file(UploadFileValidate $request)
    {
        $result = $this->postRepository->upload_file($request->file('file'), $request->user()->id);

        return $this->successResponse($result); 
    }

    public function store_vote_poll(StorePollVoteValidate $request)
    {
        $result = $this->postRepository->store_vote_poll($request, $request->user()->id);

        return $this->successResponse($result); 
    }

    public function get_poll_item_vote(GetPollVoteValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->get_poll_item_vote($request->poll_item_id, $request->user(), $page);

        return $this->successResponse($result);
    }

    public function store_comment_privacy(EditCommentPrivacyValidate $request)
    {
        $result = $this->postRepository->store_comment_privacy($request->id, $request->comment_privacy);
    
        return $this->successResponse($result);
    }
    
    public function store_content_warning(EditContentWarningValidate $request)
    {
        $result = $this->postRepository->store_content_warning($request->id, $request->content_warning_categories);
    
        return $this->successResponse($result);
    }

    public function document(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->document($request->user(), $page);

        return $this->successResponse($result);
    }

    public function store_stop_comment(StoreStopCommentValidate $request)
    {
        $this->postRepository->store_stop_comment($request->id, $request->stop);
        return $this->successResponse();
    }

    public function store_pin_home(StorePinHomeValidate $request)
    {
        $this->postRepository->store_pin_home($request->id, $request->action);
        return $this->successResponse();
    }

    public function store_pin_profile(StorePinProfileValidate $request)
    {
        $this->postRepository->store_pin_profile($request->id, $request->action);
        return $this->successResponse();
    }

    public function get_new_home(GetNewHomeValidate $request)
    {
        $result = $this->postRepository->get_new_home($request->get('id', 0), $request->user());

        return $this->successResponse($result);
    }
}
