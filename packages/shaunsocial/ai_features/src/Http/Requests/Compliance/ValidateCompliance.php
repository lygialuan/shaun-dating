<?php

namespace Packages\ShaunSocial\AiFeatures\Http\Requests\Compliance;

use Illuminate\Foundation\Http\FormRequest;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\AiFeatures\Notification\AiFeatureRemovalNotification;
use Packages\ShaunSocial\Core\Models\UserNotification;

class ValidateCompliance extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var AiFeatureTask|null $task */
        $task = AiFeatureTask::findByField('id', $this->route('task'));

        if (! $user || ! $task) {
            return false;
        }

        $subject = $task->getSubject();
        if ($subject && method_exists($subject, 'getUser')) {
            $owner = $subject->getUser();
            if ($owner && $owner->id === $user->id) {
                return true;
            }
        }

        return UserNotification::where('user_id', $user->id)
            ->where('subject_type', $task->getSubjectType())
            ->where('subject_id', $task->id)
            ->where('class', AiFeatureRemovalNotification::class)
            ->exists();
    }

    public function rules(): array
    {
        return [];
    }
}
