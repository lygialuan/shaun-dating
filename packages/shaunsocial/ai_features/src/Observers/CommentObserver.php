<?php

namespace Packages\ShaunSocial\AiFeatures\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Packages\ShaunSocial\AiFeatures\Services\AiFeatureTaskScheduler;
use Packages\ShaunSocial\Core\Models\Comment;

class CommentObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Comment $comment): void
    {
        $app = app();

        $app->terminating(fn () => $app->make(AiFeatureTaskScheduler::class)->scheduleForCommentId($comment->id));
    }
}
