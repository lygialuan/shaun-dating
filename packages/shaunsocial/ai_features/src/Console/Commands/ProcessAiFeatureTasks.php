<?php

namespace Packages\ShaunSocial\AiFeatures\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\AiFeatures\Services\AiFeatureAutoDelete;
use Packages\ShaunSocial\AiFeatures\Services\AiFeatureAutoNotify;
use Packages\ShaunSocial\AiFeatures\Services\AiFeatureAutoNotifyForAdmin;
use Packages\ShaunSocial\AiFeatures\Services\AiFeatureAutoReport;
use Packages\ShaunSocial\AiFeatures\Services\AiFeatureTaskManager;

class ProcessAiFeatureTasks extends Command
{
    protected $signature = 'shaun_ai_feature:tasks {--limit=0 : Maximum tasks to process in this run} {--debug : Output verbose debug information}';

    protected $description = 'Process pending AI Feature moderation tasks.';

    public function __construct(
        protected AiFeatureTaskManager $taskManager,
        protected AiFeatureAutoReport $autoReporter,
        protected AiFeatureAutoDelete $autoDeleter,
        protected AiFeatureAutoNotify $autoNotifier,
        protected AiFeatureAutoNotifyForAdmin $adminNotifier
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limitOption = (int) $this->option('limit');
        if ($limitOption <= 0) {
            $limitOption = (int) config('shaun_ai_features.tasks.limit', 50);
        }

        $batchSize = max(1, $limitOption);
        $debug = (bool) $this->option('debug');

        if ($debug) {
            $this->line(sprintf('[DEBUG] Preparing to fetch up to %d task(s)', $batchSize));
        }

        $tasks = AiFeatureTask::getReadyItems($batchSize);

        if ($tasks->isEmpty()) {
            $this->info('No AI feature tasks ready to process.');

            return self::SUCCESS;
        }

        $processed = 0;
        $succeeded = 0;
        $failed = 0;
        $reportsCreated = 0;
        $autoDeleted = 0;
        $autoNotified = 0;
        $adminNotified = 0;

        foreach ($tasks as $task) {
            if ($debug) {
                $this->line(sprintf('[DEBUG] Inspecting task #%d (subject: %s #%d, status: %s, attempts: %d/%d)',
                    $task->id,
                    $task->subject_type,
                    $task->subject_id,
                    $task->status,
                    $task->attempts,
                    $task->max_attempts
                ));
            }

            if (! $this->claimTask($task)) {
                if ($debug) {
                    $this->line(sprintf('[DEBUG] -> Task #%d was already claimed by another worker.', $task->id));
                }
                $this->line(sprintf('Skipping task #%d (claimed by another worker).', $task->id));
                continue;
            }

            $task->refresh();
            if ($debug) {
                $this->line(sprintf('[DEBUG] -> Claimed task #%d (attempts now %d)', $task->id, $task->attempts));
            }
            $processed++;

            $result = $this->taskManager->processTask($task);
            if ($debug) {
                $this->line(sprintf('[DEBUG] -> Provider response for task #%d: status=%s message="%s"',
                    $task->id,
                    $result['status'] ? 'success' : 'failure',
                    $result['message'] ?? ''
                ));
            }
            $actionResult = $this->applyResult($task, $result, $debug);

            if ($result['status']) {
                $succeeded++;
                $this->info(sprintf('Task #%d completed successfully.', $task->id));
                if ($debug) {
                    $this->line(sprintf('[DEBUG] -> Task #%d stored result payload: %s',
                        $task->id,
                        json_encode($task->result ?? [], JSON_UNESCAPED_UNICODE)
                    ));
                }
                if ($actionResult['reported']) {
                    $reportsCreated++;
                }
                if ($actionResult['deleted']) {
                    $autoDeleted++;
                }
                if ($actionResult['notified']) {
                    $autoNotified++;
                }
                if ($actionResult['admin_alerted']) {
                    $adminNotified++;
                }
            } else {
                if ($task->status === AiFeatureTask::STATUS_FAILED) {
                    $failed++;
                    $this->error(sprintf('Task #%d failed permanently: %s', $task->id, $task->error_message));
                } else {
                    $this->warn(sprintf('Task #%d deferred: %s', $task->id, $task->error_message));
                }
            }
        }

        $deferred = $processed - $succeeded - $failed;
        $this->line(sprintf(
            'Processed %d task(s): %d succeeded, %d failed, %d deferred. Reports created: %d. Auto deleted: %d. User notified: %d. Admin notified: %d.',
            $processed,
            $succeeded,
            $failed,
            $deferred,
            $reportsCreated,
            $autoDeleted,
            $autoNotified,
            $adminNotified
        ));

        if ($debug) {
            $this->line(sprintf('[DEBUG] Summary -> success:%d failed:%d deferred:%d reported:%d deleted:%d user_notified:%d admin_notified:%d',
                $succeeded,
                $failed,
                $deferred,
                $reportsCreated,
                $autoDeleted,
                $autoNotified,
                $adminNotified
            ));
        }

        return self::SUCCESS;
    }

    protected function claimTask(AiFeatureTask $task): bool
    {
        $affected = AiFeatureTask::where('id', $task->id)
            ->where('status', AiFeatureTask::STATUS_PENDING)
            ->update([
                'status' => AiFeatureTask::STATUS_PROCESSING,
                'attempts' => DB::raw('attempts + 1'),
                'updated_at' => Carbon::now(),
            ]);

        return $affected > 0;
    }

    /**
     * @param array{status: bool, message: string, data?: array, provider_key_id?: int|null, error_code?: string|null, retryable: bool} $result
     */
    protected function applyResult(AiFeatureTask $task, array $result, bool $debug = false): array
    {
        $task->provider_key_id = $result['provider_key_id'] ?? $task->provider_key_id;
        $task->result = $result['data'] ?? null;
        $task->error_code = $result['error_code'] ?? null;
        $task->error_message = $result['status'] ? null : ($result['message'] ?? '');

        if ($result['status']) {
            $task->status = AiFeatureTask::STATUS_DONE;
            $task->processed_at = Carbon::now();
            $task->next_run_at = null;
        } else {
            $shouldRetry = $result['retryable'] && $task->attempts < $task->max_attempts;

            if ($shouldRetry) {
                $task->status = AiFeatureTask::STATUS_PENDING;
                $task->next_run_at = Carbon::now()->addSeconds($this->calculateBackoff($task->attempts));
            } else {
                $task->status = AiFeatureTask::STATUS_FAILED;
                $task->processed_at = Carbon::now();
                $task->next_run_at = null;
            }
        }

        $task->save();

        $reported = false;
        $deleted = false;
        $notified = false;
        $adminAlerted = false;

        if ($task->status === AiFeatureTask::STATUS_DONE) {
            $reported = $this->autoReporter->handle($task);

            if ($reported && ! $task->reported_at) {
                $task->reported_at = Carbon::now();
                $task->save();
            }

            $deleted = $this->autoDeleter->handle($task);
            if ($deleted) {
                $task->auto_action = AiFeatureTask::AUTO_ACTION_DELETE;
                $task->save();
            }

            if ($deleted) {
                $notified = $this->autoNotifier->handle($task, true);
            }

            if ((bool) data_get($task->result, 'flagged', false)) {
                $adminAlerted = $this->adminNotifier->handle($task);
            }

            if ($debug) {
                $this->line(sprintf('[DEBUG] ----> Post actions: reported=%s deleted=%s notified=%s',
                    $reported ? 'yes' : 'no',
                    $deleted ? 'yes' : 'no',
                    $notified ? 'yes' : 'no'
                ));
            }
        }

        return [
            'reported' => $reported,
            'deleted' => $deleted,
            'notified' => $notified,
            'admin_alerted' => $adminAlerted,
        ];
    }

    protected function calculateBackoff(int $attempts): int
    {
        $backoffs = config('shaun_ai_features.tasks.backoff_seconds', [60, 300, 900]);
        if (! is_array($backoffs) || empty($backoffs)) {
            return 60;
        }

        $index = max(0, min($attempts - 1, count($backoffs) - 1));

        return (int) $backoffs[$index];
    }
}
