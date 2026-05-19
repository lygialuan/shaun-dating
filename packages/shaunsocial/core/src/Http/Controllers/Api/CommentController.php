<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Comment\DeleteCommentReplyValidate;
use Packages\ShaunSocial\Core\Http\Requests\Comment\DeleteCommentValidate;
use Packages\ShaunSocial\Core\Http\Requests\Comment\EditCommentReplyValidate;
use Packages\ShaunSocial\Core\Http\Requests\Comment\EditCommentValidate;
use Packages\ShaunSocial\Core\Http\Requests\Comment\GetCommentReplyValidate;
use Packages\ShaunSocial\Core\Http\Requests\Comment\GetCommentSingleValidate;
use Packages\ShaunSocial\Core\Http\Requests\Comment\GetCommentValidate;
use Packages\ShaunSocial\Core\Http\Requests\Comment\StoreCommentReplyValidate;
use Packages\ShaunSocial\Core\Http\Requests\Comment\StoreCommentValidate;
use Packages\ShaunSocial\Core\Http\Requests\Comment\DeleteCommentItemValidate;
use Packages\ShaunSocial\Core\Http\Requests\Comment\DeleteReplytItemValidate;
use Packages\ShaunSocial\Chat\Http\Requests\UploadPhotoValidate;
use Packages\ShaunSocial\Core\Models\UserActionLog;
use Packages\ShaunSocial\Core\Repositories\Api\CommentRepository;

class CommentController extends ApiController
{
    protected $commentRepository;

    public function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
        parent::__construct();
    }

    public function store(StoreCommentValidate $request)
    {
        $request->mergeIfMissing([
            'content' => '',
            'items' => [],
        ]);

        $result = $this->commentRepository->store($request->only([
            'subject_type', 'subject_id', 'content', 'items', 'type'
        ]), $request->user());

        UserActionLog::create([
            'user_id' => $request->user()->id,
            'type' => 'create_comment'
        ]);

        return $this->successResponse($result);
    }

    public function store_reply(StoreCommentReplyValidate $request)
    {
        $request->mergeIfMissing([
            'content' => '',
            'items' => [],
        ]);
        
        $result = $this->commentRepository->store_reply($request->only([
            'comment_id', 'content', 'items', 'type'
        ]), $request->user());

        UserActionLog::create([
            'user_id' => $request->user()->id,
            'type' => 'create_comment'
        ]);

        return $this->successResponse($result);
    }

    public function get(GetCommentValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->commentRepository->get($request->subject_type, $request->subject_id, $page, $request->user());
        
        return $this->successResponse($result);
    }

    public function get_reply(GetCommentReplyValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->commentRepository->get_reply($request->id, $page, $request->user());
        
        return $this->successResponse($result);
    }

    public function delete(DeleteCommentValidate $request)
    {
        $this->commentRepository->delete($request->id);

        return $this->successResponse();
    }

    public function delete_reply(DeleteCommentReplyValidate $request)
    {
        $this->commentRepository->delete_reply($request->id);

        return $this->successResponse();
    }

    public function get_single(GetCommentSingleValidate $request)
    {
        $result = $this->commentRepository->get_single($request->comment_id, $request->reply_id, $request->user());
        
        return $this->successResponse($result);
    }

    public function store_edit(EditCommentValidate $request)
    {
        $result = $this->commentRepository->store_edit($request->id, $request->content, $request->user());
        
        return $this->successResponse($result);
    }

    public function store_reply_edit(EditCommentReplyValidate $request)
    {
        $result = $this->commentRepository->store_reply_edit($request->id, $request->content, $request->user());
    
        return $this->successResponse($result);
    }

    public function upload_photo(UploadPhotoValidate $request)
    {
        $result = $this->commentRepository->upload_photo($request->file('file'), $request->user()->id);
    
        return $this->successResponse($result);
    }

    public function delete_item(DeleteCommentItemValidate $request)
    {
        $this->commentRepository->delete_item($request->id);
        
        return $this->successResponse();
}

    public function upload_reply_photo(UploadPhotoValidate $request)
    {
        $result = $this->commentRepository->upload_reply_photo($request->file('file'), $request->user()->id);
    
        return $this->successResponse($result);
    }

    public function delete_reply_item(DeleteReplytItemValidate $request)
    {
        $this->commentRepository->delete_reply_item($request->id);
        
        return $this->successResponse();
    }
}
