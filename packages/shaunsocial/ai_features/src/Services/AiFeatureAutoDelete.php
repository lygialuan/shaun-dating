<?php

namespace Packages\ShaunSocial\AiFeatures\Services;

use Illuminate\Support\Facades\Log;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\AiFeatures\Services\AiFeatureTaskItemBackup;
use Packages\ShaunSocial\Core\Repositories\Api\CommentRepository;
use Packages\ShaunSocial\Core\Repositories\Api\PostRepository;

class AiFeatureAutoDelete
{
    protected $postRepository;
    protected $commentRepository;
    protected AiFeatureTaskItemBackup $taskItemBackup;

    public function __construct(
        PostRepository $postRepository,
        CommentRepository $commentRepository,
        AiFeatureTaskItemBackup $taskItemBackup
    ) {
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->taskItemBackup = $taskItemBackup;
    }
    public function handle(AiFeatureTask $task): bool
    {
        if (! (bool) setting('ai_features.auto_delete_inappropriate_content')) {
            return false;
        }

        if (! (bool) data_get($task->result, 'flagged', false)) {
            return false;
        }

        $this->taskItemBackup->cloneSubjectItems($task);
    
        switch ($task->subject_type) {
            case 'posts':
                $this->postRepository->delete($task->subject_id);
                return true;
            case 'comments':
                $this->commentRepository->delete($task->subject_id);
                return true;
            case 'comment_replies':
                $this->commentRepository->delete_reply($task->subject_id);
                return true;
            default:
                return false;
        }

    }
}
