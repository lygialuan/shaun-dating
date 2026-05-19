<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Enum\SubscriptionStatus;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Subscription;
use Packages\ShaunSocial\Core\Models\SubscriptionTransaction;
use Packages\ShaunSocial\Core\Models\SubscriptionType;
use Packages\ShaunSocial\Core\Repositories\Api\SubscriptionRepository;

class SubscriptionController extends Controller
{
    protected $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->middleware('has.permission:admin.subscription.manage');
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Subscriptions'),
            ],
        ];
        $title = __('Subscriptions');

        $prefix = env('DB_PREFIX');
        $builder = Subscription::selectRaw($prefix.'subscriptions.*')->orderBy('subscriptions.id','desc');
        $name = $request->input('name');

        if ($name) {
            $builder->join('users', function ($join) use ($name) {
                $join->on('users.id', '=', 'subscriptions.user_id')->where(function ($query) use ($name){
                    $query->where('users.name', 'LIKE', '%'.$name.'%')->orWhere('users.user_name', 'LIKE', '%'.$name.'%');
                });
            });
        }

        $typeArray = SubscriptionType::getAll()->mapWithKeys(function ($subscriptionType) {
            return [$subscriptionType->type => $subscriptionType->getClass()->getName()];
        })->toArray();

        $type = $request->input('type', '');
        if (! in_array($type, array_keys($typeArray))) {
            $type = '';
        }

        if ($type) {
            $builder->where('type', $type);
        }

        $statusArray = SubscriptionStatus::getAll();
        $status = $request->input('status', '');
        if (! in_array($status, array_keys($statusArray))) {
            $status = '';
        }

        if ($status) {
            $builder->where('status', $status);
        }

        $dateTypeArray = [
            '30_day' => __('Past 30 days'), 
            '60_day' => __('Past 60 days'),
            '90_day' => __('Past 90 days'), 
            'custom' => __('Custom')
        ];
        $dateType = $request->input('date_type');
        if (! in_array($dateType, array_keys($dateTypeArray))) {
            $dateType = '30_day';
        }
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        switch ($dateType) {
            case '30_day':
                $builder->where('subscriptions.created_at', '>=', now()->subDays(30));
                break;
            case '60_day':
                $builder->where('subscriptions.created_at', '>=', now()->subDays(60));
                break;
            case '90_day':
                $builder->where('subscriptions.created_at', '>=', now()->subDays(90));
                break;
            case 'custom':
                if ($fromDate) {
                    $builder->where('subscriptions.created_at', '>=', $fromDate. ' 00:00:00');
                }
                if ($toDate) {
                    $builder->where('subscriptions.created_at', '<=', $toDate. ' 23:59:59');
                }
                break;
        }

        $subscriptions = $builder->paginate(setting('feature.item_per_page'));
        
        return view('shaun_core::admin.subscription.index', compact('breadcrumbs', 'title', 'subscriptions', 'typeArray', 'type', 'dateType', 'dateTypeArray', 'name', 'fromDate', 'toDate', 'statusArray', 'status'));
    }

    public function detail(Request $request)
    {
        $id = $request->id;
        $subscription = Subscription::findOrFail($id);

        $builder = SubscriptionTransaction::where('subscription_id', $id)->orderBy('id','desc');
        $transactions = $builder->paginate(setting('feature.item_per_page'));

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Subscriptions'),
                'route' => 'admin.subscription.index',
            ],
            [
                'title' => __('Detail'),
            ],
        ];
        $title = __('Detail');

        return view('shaun_core::admin.subscription.detail', compact('breadcrumbs', 'title', 'transactions', 'subscription'));
    }

    public function cancel(Request $request)
    {
        $id = $request->id;
        $subscription = Subscription::findOrFail($id);

        if (! $subscription->canCancel()) {
            abort(400);
        }

        $this->subscriptionRepository->store_cancel($id);
        $subscription->update([
            'admin_cancel' => true
        ]);

        return redirect()->back()->with([
            'admin_message_success' => __('Subscription has been successfully canceled.'),
        ]);
    }

    public function resume(Request $request)
    {
        $id = $request->id;
        $subscription = Subscription::findOrFail($id);

        if (! $subscription->canResumeOnAdmin()) {
            abort(400);
        }

        $this->subscriptionRepository->store_resume($id);
        $subscription->update([
            'admin_cancel' => false
        ]);

        return redirect()->back()->with([
            'admin_message_success' => __('Subscription has been successfully resumed.'),
        ]);
    }
}
