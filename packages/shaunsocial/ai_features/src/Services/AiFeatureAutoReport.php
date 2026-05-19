<?php

namespace Packages\ShaunSocial\AiFeatures\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\Core\Models\Comment;
use Packages\ShaunSocial\Core\Models\CommentReply;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\ReportCategory;
use Packages\ShaunSocial\Core\Repositories\Api\ReportRepository;
use Throwable;
class AiFeatureAutoReport
{
    public function __construct(
        protected ReportRepository $reportRepository
    ) {
    }

    public function handle(AiFeatureTask $task): bool
    {
        $config = config('shaun_ai_features.reporting', []);

        if (! Arr::get($config, 'enabled', false)) {
            return false;
        }

        $supportedSubjects = ['posts', 'comments', 'comment_replies'];
        if (! in_array($task->subject_type, $supportedSubjects, true)) {
            return false;
        }

        if (! (bool) data_get($task->result, 'flagged', false)) {
            return false;
        }

        $reporterId = config('shaun_ai_features.reporting.reporter_user_id',0);
        

        $subject = $this->resolveSubject($task->subject_type, $task->subject_id);
        if (! $subject || ! method_exists($subject, 'getReport')) {
            return false;
        }

        if ($subject->getReport($reporterId)) {
            // Already reported by the automated user; avoid duplicates.
            return false;
        }

        $category = ReportCategory::getCategoryAi();

        $reason = $this->buildReasonFromTask($task);

        try {
            $this->reportRepository->store([
                'subject_type' => $task->subject_type,
                'subject_id' => $task->subject_id,
                'category_id' => $category->id,
                'reason' => $reason,
            ], $reporterId);
            return true;
        } catch (Throwable $e) {
            Log::error('AI auto report failed.', [
                'task_id' => $task->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function buildReasonFromTask(AiFeatureTask $task): string
    {
        $summary = trim((string) data_get($task->result, 'summary', ''));
        $reasons = array_filter(array_map('trim', (array) data_get($task->result, 'reasons', [])));

        $details = $summary;
        if ($details !== '' && ! empty($reasons)) {
            $details .= ' | '.implode(', ', $reasons);
        } elseif ($details === '') {
            $details = implode(', ', $reasons);
        }

        $message = $details === ''
            ? __('Flagged by automated moderation.')
            : __('Flagged by automated moderation: :details', ['details' => $details]);

        return Str::limit($message, 255, '');
    }

    protected function resolveSubject(string $subjectType, int $subjectId): mixed
    {
        $subject = findByTypeId($subjectType, $subjectId);
        if ($subject) {
            return $subject;
        }

        $fallback = [
            'posts' => Post::class,
            'comments' => Comment::class,
            'comment_replies' => CommentReply::class,
        ][$subjectType] ?? null;

        if ($fallback && class_exists($fallback)) {
            return $fallback::find($subjectId);
        }

        return null;
    }
}
