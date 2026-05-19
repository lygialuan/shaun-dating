<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Http\Resources\Report\ReportCategoryResource;
use Packages\ShaunSocial\Core\Models\Report;
use Packages\ShaunSocial\Core\Models\ReportCategory;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Support\Facades\Mail;

class ReportRepository
{
    protected $userRepository = null;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function category()
    {
        $categories = ReportCategory::getAll(false);
        return ReportCategoryResource::collection($categories);
    }

    public function store($data, $viewerId)
    {
        $subject = findByTypeId($data['subject_type'], $data['subject_id']);
        $toUserId = $subject->getReportToUserId($viewerId);
        if ($toUserId && setting('report.time_disable_user')) {
            $user = User::findByField('id', $toUserId);
            if ($user && $user->is_active && ! $user->isModerator()) {
                $count = Report::where('to_user_id', $toUserId)->where('created_at', '>', now()->subDays(1))->count();
                $count++;
                if ($count >= setting('report.time_disable_user')) {
                    $user->update([
                        'is_active' => false
                    ]);

                    Mail::send('inactive_user_report', $user, [
                        'link' => route('web.contact.create')
                    ]);
                }
            }            
        }
        
        Report::create([
            'user_id' => $viewerId,
            'subject_type' => $data['subject_type'],
            'subject_id' => $data['subject_id'],
            'category_id' => $data['category_id'],
            'reason' => $data['reason'],
            'to_user_id' => $toUserId
        ]);
    }
}
