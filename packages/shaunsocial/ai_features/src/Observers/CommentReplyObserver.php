<?php

namespace Packages\ShaunSocial\AiFeatures\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Packages\ShaunSocial\AiFeatures\Services\AiFeatureTaskScheduler;
use Packages\ShaunSocial\Core\Models\CommentReply;

class CommentReplyObserver implements ShouldHandleEventsAfterCommit
{
    public function created(CommentReply $reply): void
    {
        $app = app();

        $app->terminating(fn () => $app->make(AiFeatureTaskScheduler::class)->scheduleForCommentReplyId($reply->id));
    }
}
