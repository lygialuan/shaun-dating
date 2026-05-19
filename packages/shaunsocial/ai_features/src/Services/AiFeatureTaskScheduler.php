<?php

namespace Packages\ShaunSocial\AiFeatures\Services;

use Illuminate\Support\Facades\Log;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\Core\Models\Comment;
use Packages\ShaunSocial\Core\Models\CommentItem;
use Packages\ShaunSocial\Core\Models\CommentReply;
use Packages\ShaunSocial\Core\Models\CommentReplyItem;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\PostItem;
use Packages\ShaunSocial\Core\Models\StorageFile;
use Throwable;
class AiFeatureTaskScheduler
{
    public function __construct(
        protected AiFeatureTaskManager $taskManager
    ) {
    }

    public function scheduleForPost(Post $post): void
    {
        try {
            $context = [
                'post_type' => $post->type,
                'user_id' => $post->user_id,
            ];

            $this->scheduleTextContent($post->getSubjectType(), $post->id, (string) $post->content, $context);
            $this->scheduleImageContent($post->getItems() ?? [], $post->getSubjectType(), $post->id, (string) $post->content, $context);
        } catch (Throwable $e) {
            Log::error('Failed to schedule AI moderation task for post.', [
                'post_id' => $post->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function scheduleForPostId(int $postId): void
    {
        $post = Post::find($postId);
        if (! $post) {
            return;
        }

        $this->scheduleForPost($post);
    }

    public function scheduleForComment(Comment $comment): void
    {
        try {
            $context = [
                'user_id' => $comment->user_id,
                'subject_type' => $comment->subject_type,
                'subject_id' => $comment->subject_id,
            ];

            $this->scheduleTextContent($comment->getSubjectType(), $comment->id, (string) $comment->content, $context);
            $this->scheduleImageContent($comment->getItems() ?? [], $comment->getSubjectType(), $comment->id, (string) $comment->content, $context);
        } catch (Throwable $e) {
            Log::error('Failed to schedule AI moderation task for comment.', [
                'comment_id' => $comment->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function scheduleForCommentId(int $commentId): void
    {
        $comment = Comment::find($commentId);
        if (! $comment) {
            return;
        }

        $this->scheduleForComment($comment);
    }

    public function scheduleForCommentReply(CommentReply $reply): void
    {
        try {
            $context = [
                'user_id' => $reply->user_id,
                'comment_id' => $reply->comment_id,
            ];

            $this->scheduleTextContent($reply->getSubjectType(), $reply->id, (string) $reply->content, $context);
            $this->scheduleImageContent($reply->getItems() ?? [], $reply->getSubjectType(), $reply->id, (string) $reply->content, $context);
        } catch (Throwable $e) {
            Log::error('Failed to schedule AI moderation task for comment reply.', [
                'comment_reply_id' => $reply->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function scheduleForCommentReplyId(int $replyId): void
    {
        $reply = CommentReply::find($replyId);
        if (! $reply) {
            return;
        }

        $this->scheduleForCommentReply($reply);
    }

    protected function scheduleTextContent(string $subjectType, int $subjectId, string $text, array $context = []): void
    {
        if (! (bool) setting('ai_features.detect_text')) {
            return;
        }

        $text = trim($text);
        if ($text === '') {
            return;
        }

        $this->taskManager->createTask([
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'content_type' => 'text',
            'payload' => [
                'text' => $text,
                'context' => $context,
            ],
            'auto_action' => $this->resolveAutoAction(),
        ]);
    }

    protected function scheduleImageContent($items, string $subjectType, int $subjectId, string $text, array $context = []): void
    {
        if (! (bool) setting('ai_features.detect_image')) {
            return;
        }

        if (! is_iterable($items)) {
            return;
        }

        $text = trim($text);

        foreach ($items as $item) {
            $file = $this->resolveFileFromItem($item);
            if (! $file || ! $this->isSupportedImage($file)) {
                continue;
            }

            $this->taskManager->createTask([
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'content_type' => 'image',
                'content_ref_type' => $file->getSubjectType(),
                'content_ref_id' => $file->id,
                'payload' => [
                    'text' => $text,
                    'context' => array_merge($context, [
                        'file_id' => $file->id,
                    ]),
                ],
                'auto_action' => $this->resolveAutoAction(),
            ]);
        }
    }

    protected function resolveFileFromItem(mixed $item): ?StorageFile
    {
        if ($item instanceof PostItem || $item instanceof CommentItem || $item instanceof CommentReplyItem) {
            $subject = $item->getSubject();
            if ($subject instanceof StorageFile) {
                return $subject;
            }
        }

        return null;
    }

    protected function resolveAutoAction(): string
    {
        return (bool) setting('ai_features.auto_delete_inappropriate_content')
            ? AiFeatureTask::AUTO_ACTION_DELETE
            : AiFeatureTask::AUTO_ACTION_FLAG;
    }

    protected function isSupportedImage(StorageFile $file): bool
    {
        $extension = strtolower((string) $file->extension);
        $type = strtolower((string) $file->type);

        if ($type === 'photo' || str_starts_with($type, 'image')) {
            return true;
        }

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true)) {
            return true;
        }

        return false;
    }
}
