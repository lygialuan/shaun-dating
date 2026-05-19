<?php

namespace Packages\ShaunSocial\AiFeatures\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;
use Packages\ShaunSocial\AiFeatures\Services\AiFeatureTaskScheduler;
use Packages\ShaunSocial\Core\Models\Post;

class PostObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Post $post): void
    {
        $app = app();

        $app->terminating(function () use ($app, $post) {
            $app->make(AiFeatureTaskScheduler::class)->scheduleForPostId($post->id);
        });
    }
}
