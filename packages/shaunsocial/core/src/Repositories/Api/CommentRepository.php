<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Http\Resources\Comment\CommentResource;
use Packages\ShaunSocial\Core\Http\Resources\Comment\CommentReplyResource;
use Packages\ShaunSocial\Core\Http\Resources\Comment\CommentItemResource;
use Packages\ShaunSocial\Core\Http\Resources\Comment\CommentReplyItemResource;
use Packages\ShaunSocial\Core\Models\Comment;
use Packages\ShaunSocial\Core\Models\CommentReply;
use Packages\ShaunSocial\Core\Models\CommentItem;
use Packages\ShaunSocial\Core\Models\CommentReplyItem;
use Packages\ShaunSocial\Core\Models\History;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Support\Facades\Utility;

class CommentRepository
{
    use HasUserList;

    public function store($data, $viewer)
    {
        $data['user_id'] = $viewer->id;
        if ($data['type'] == 'text') {
            $urls = [];
            preg_match_all(config('shaun_core.regex.link'), $data['content'], $urls);
            if (!empty($urls[0][0])) {
                $link = Utility::parseLink($urls[0][0], $viewer->id);
                if ($link) {
                    $commentItem = CommentItem::create([
                        'user_id' => $viewer->id,
                        'subject_type' => $link->getSubjectType(),
                        'subject_id' => $link->id,
                    ]);

                    $commentItem->setSubject($link);
                    CommentItem::setCacheQueryFieldsResult('id', $commentItem->id, $commentItem);
                    $data['type'] = 'link';
                    $data['items'] = [$commentItem->id];
                }
            }
        }

        $comment = Comment::create($data);
        
        // send notify
        $comment->sendMentionNotification($comment->getUSer());

        $subject = findByTypeId($data['subject_type'], $data['subject_id']);
        $subject->sendCommentNotification($viewer, $comment);

        if (!empty($data['items']) && count($data['items'])) {
            $commentItems = [];
            foreach ($data['items'] as $key => $item) {
                $commentItem = CommentItem::findByField('id', $item);
                $commentItem->update([
                    'comment_id' => $comment->id,
                    'order' => $key,
                ]);
                $commentItems[] = $commentItem;
            }
            $comment->setItems(collect($commentItems));
        }

        return new CommentResource($comment);
    }

    public function store_reply($data, $viewer)
    {
        $data['user_id'] = $viewer->id;
        if ($data['type'] == 'text') {
            $urls = [];
            preg_match_all(config('shaun_core.regex.link'), $data['content'], $urls);
            if (!empty($urls[0][0])) {
                $link = Utility::parseLink($urls[0][0], $viewer->id);
                if ($link) {
                    $replyItem = CommentReplyItem::create([
                        'user_id' => $viewer->id,
                        'subject_type' => $link->getSubjectType(),
                        'subject_id' => $link->id,
                    ]);

                    $replyItem->setSubject($link);
                    CommentReplyItem::setCacheQueryFieldsResult('id', $replyItem->id, $replyItem);
                    $data['type'] = 'link';
                    $data['items'] = [$replyItem->id];
                }
            }
        }
        
        $reply = CommentReply::create($data);
        
        // send notify
        $reply->sendMentionNotification($reply->getUSer());

        $comment = Comment::findByField('id', $data['comment_id']);
        $comment->sendReplyNotification($viewer, $reply);

        if (!empty($data['items']) && count($data['items'])) {
            $replyItems = [];
            foreach ($data['items'] as $key => $item) {
                $replyItem = CommentReplyItem::findByField('id', $item);
                $replyItem->update([
                    'reply_id' => $reply->id,
                    'order' => $key,
                ]);
                $replyItems[] = $replyItem;
            }
            $reply->setItems(collect($replyItems));
        }

        return new CommentReplyResource($reply);
    }

    public function get($subjectType, $subjectId, $page, $viewer)
    {
        $subject = findByTypeId($subjectType, $subjectId);

        $comments = Comment::getCachePagination('comment_'.$subjectType.'_'.$subjectId, Comment::where('subject_type', $subjectType)->where('subject_id', $subjectId)->orderBy('id', 'DESC'), $page);

        return [
            'items' => CommentResource::collection($this->filterUserList($comments, $viewer)),
            'has_next_page' => checkNextPage($subject->comment_count, count($comments), $page)
        ];
    }

    public function get_reply($commentId, $page, $viewer)
    {
        $comment = Comment::findByField('id', $commentId);

        $replies = CommentReply::getCachePagination('reply_'.$commentId, CommentReply::where('comment_id', $commentId)->orderBy('id', 'DESC'), $page);

        return [
            'items' => CommentReplyResource::collection($this->filterUserList($replies, $viewer)),
            'has_next_page' => checkNextPage($comment->reply_count, count($replies), $page)
        ];
    }

    public function delete($commentId)
    {
        $comment = Comment::findByField('id', $commentId);
        $comment->delete();
    }

    public function delete_reply($replyId)
    {
        $reply = CommentReply::findByField('id', $replyId); 
        $reply->delete();
    }

    public function get_single($commentId, $replyId, $viewer)
    {
        $comment = Comment::findByField('id', $commentId);

        $result = ['comment' => new CommentResource($comment)];
        if ($replyId) {
            $reply = CommentReply::findByField('id', $replyId);
            $result['reply'] = new CommentReplyResource($reply);
        }

        return $result;
    }

    public function store_edit($id, $content, $viewer)
    {
        $comment = Comment::findByField('id', $id);
        $mentions = $comment->mentions;

        History::create([
            'user_id' => $viewer->id,
            'content' => $comment->getMentionContent(null),
            'mentions' => $comment->mentions,
            'subject_type' => $comment->getSubjectType(),
            'subject_id' => $comment->id,
        ]);

        $comment->update([
            'content' => $content,
            'is_edited' => true
        ]);
        
        $comment->updateMention();
        $comment->sendMentionNotificationWhenEdit($mentions);

        return new CommentResource($comment);
    }

    public function store_reply_edit($id, $content, $viewer)
    {
        $reply = CommentReply::findByField('id', $id);
        $mentions = $reply->mentions;

        History::create([
            'user_id' => $viewer->id,
            'content' => $reply->getMentionContent(null),
            'mentions' => $reply->mentions,
            'subject_type' => $reply->getSubjectType(),
            'subject_id' => $reply->id,
        ]);

        $reply->update([
            'content' => $content,
            'is_edited' => true
        ]);
        
        $reply->updateMention();
        $reply->sendMentionNotificationWhenEdit($mentions);

        return new CommentReplyResource($reply);
    }

    public function upload_photo($file, $viewerId)
    {
        $storageFile = File::storePhoto($file, [
            'parent_type' => 'comment_item',
            'user_id' => $viewerId,
        ], true);

        $commentItem = CommentItem::create([
            'user_id' => $viewerId,
            'subject_type' => $storageFile->getSubjectType(),
            'subject_id' => $storageFile->id,
        ]);

        $storageFile->update([
            'parent_id' => $commentItem->id,
        ]);

        $commentItem->setSubject($storageFile);

        return new CommentItemResource($commentItem);
    }

    public function delete_item($itemId)
    {
        $item = CommentItem::findByField('id', $itemId);
        $item->delete();
    }

    public function upload_reply_photo($file, $viewerId)
    {
        $storageFile = File::storePhoto($file, [
            'parent_type' => 'comment_reply_item',
            'user_id' => $viewerId,
        ], true);

        $commentReplyItem = CommentReplyItem::create([
            'user_id' => $viewerId,
            'subject_type' => $storageFile->getSubjectType(),
            'subject_id' => $storageFile->id,
        ]);

        $storageFile->update([
            'parent_id' => $commentReplyItem->id,
        ]);

        $commentReplyItem->setSubject($storageFile);

        return new CommentReplyItemResource($commentReplyItem);
    }

    public function delete_reply_item($itemId)
    {
        $item = CommentReplyItem::findByField('id', $itemId);
        $item->delete();
    }

}
