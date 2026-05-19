<?php

namespace Packages\ShaunSocial\UserSubscription\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscriptionPackage;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscriptionPlan;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscriptionPackageCompare;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscriptionPackageCompareColumn;
use Packages\ShaunSocial\Core\Models\Key;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Models\Role;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\UserSubscription\Enum\UserSubscriptionPlanBillingCycleType;
use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Validation\AmountValidation;
use Packages\ShaunSocial\GatewayRecurring\Models\GatewayRecurring;

class UserSubscriptionController extends Controller
{

    public function __construct()
    {
        $routerName = Route::getCurrentRoute()->getName();
        switch ($routerName) {
            case 'admin.user_subscription.pricing_table.index':
            case 'admin.user_subscription.store_pricing_table':
            case 'admin.user_subscription.store_order':
                $this->middleware('has.permission:admin.user_subscription.pricing_table');
                break;
            case 'admin.user_subscription.pricing_table.preview':
                $this->middleware('has.permission:admin.user_subscription.pricing_table|admin.user_subscription.manage_package');
                break;
            default:
                $this->middleware('has.permission:admin.user_subscription.manage_package');
                break;
        }
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Packages'),
            ],
        ];
        $title = __('Packages');

        $packages = UserSubscriptionPackage::all();

        return view('shaun_user_subscription::admin.package.index', compact('breadcrumbs', 'title', 'packages'));
    }

    public function create($id = null)
    {
        $title = empty($id) ? __('Create') : __('Edit');
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Manage Packages'),
                'route' => 'admin.user_subscription.index',
            ]
        ];

        if (empty($id)) {
            $package = new UserSubscriptionPackage();
            $package->is_active = true;
            $breadcrumbs[] = [
                'title' => __('Create'),
            ];
        } else {
            $package = UserSubscriptionPackage::findOrFail($id);
            $breadcrumbs[] = [
                'title' => __('Edit'),
            ];
        }

        $roles = Role::getMemberRoles();

        return view('shaun_user_subscription::admin.package.create', compact('package', 'title', 'breadcrumbs', 'roles'));
    }

    public function store(Request $request)
    {
        $roles = Role::getMemberRoles()->pluck('id')->all();

        $rules = [
            'name' => 'required|max:255',
            'description' => 'required|max:255',
            'role_id' => [
                'required',
                Rule::in($roles)
            ],
            'expire_role_id' => [
                'required',
                Rule::in($roles)
            ],
        ];
        
        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.'),
                'name.max' => __('The name must not be greater than 255 characters.'),
                'description.required' => __('The description is required.'),
                'description.max' => __('The description must not be greater than 255 characters.'),
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $request->mergeIfMissing([
            'is_active' => false,
            'is_show_badge' => false
        ]);
        $data = $request->except('id', '_token');

        if ($request->id) {
            $package = UserSubscriptionPackage::findOrFail($request->id);
            $package->update($data);
        } else {
            $package = UserSubscriptionPackage::create($data);
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Package has been successfully updated.') : __('Package has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $package = UserSubscriptionPackage::findOrFail($id);

        $package->doDeleted();

        $plans = $package->getPlans();
        $plans->each(function($plan) {
            $plan->doDeleted();
        });
        
        return redirect()->route('admin.user_subscription.index')->with([
            'admin_message_success' => __('Package has been deleted.'),
        ]);
    }

    public function plan($packageId)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Packages'),
                'route' => 'admin.user_subscription.index',
            ],
            [
                'title' => __('Plans'),
            ],
        ];
        $title = __('Plans');

        $package = UserSubscriptionPackage::findOrFail($packageId);

        $plans = UserSubscriptionPlan::where('package_id', $packageId)->orderBy('order', 'ASC')->orderBy('id','DESC')->get();

        return view('shaun_user_subscription::admin.package.plan', compact('breadcrumbs', 'title', 'plans', 'packageId', 'package'));
    }

    public function create_plan($packageId, $id = null)
    {
        $title = empty($id) ? __('Create') : __('Edit');
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Manage Packages'),
                'route' => 'admin.user_subscription.index',
            ]
        ];

        if (empty($id)) {
            $plan = new UserSubscriptionPlan();
            $plan->is_active = true;
            $breadcrumbs[] = [
                'title' => __('Create'),
            ];
        } else {
            $plan = UserSubscriptionPlan::findOrFail($id);
            $breadcrumbs[] = [
                'title' => __('Edit'),
            ];
        }
        $billingCycleType = UserSubscriptionPlanBillingCycleType::getAll();

        $gateways = GatewayRecurring::orderBy('order', 'ASC')->where('is_active', true)->where('show', true)->orderBy('id', 'DESC')->get();

        return view('shaun_user_subscription::admin.package.create_plan', compact('plan', 'title', 'breadcrumbs', 'billingCycleType', 'packageId', 'gateways'));
    }

    public function store_plan(Request $request)
    {
        $rules = [
            'name' => 'required',
            'amount' => ['required', new AmountValidation()],
            'billing_cycle' => ['required', 'numeric', 'min:1', 'decimal:0'],
            'billing_cycle_type' => [
                'required', Rule::in(UserSubscriptionPlanBillingCycleType::values())
            ],
            'flex_form_id' => ['required_with:gateway_recurring_id']
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.'),
                'name.max' => __('The name must not be greater than 255 characters.'),
                'amount.required' => __('The amount is required.'),
                'billing_cycle_type.required' => __('The billing cycle type is required.'),
                'billing_cycle.required' => __('The billing cycle is required.'),
                'billing_cycle.min' => __('The amount must be minimum 1.'),
                'billing_cycle.decimal' => __('The amount field must have 0 decimal places.')
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $request->mergeIfMissing([
            'is_active' => false
        ]);
        $data = $request->except('id', '_token');

        if (!$data['trial_day']) {
            $data['trial_day'] = 0;
        }
        if (!$data['billing_cycle']) {
            $data['billing_cycle'] = 0;
        }
        if (empty($data['gateway_recurring_id'])) {
            $data['gateway_recurring_id'] = 0;
            $data['flex_form_id'] = 0;
        }

        if ($request->id) {
            $plan = UserSubscriptionPlan::findOrFail($request->id);
            $plan->update($data);
        } else {
            $plan = UserSubscriptionPlan::create($data);
        }

        $syncData = [];
        foreach ($request->gateways ?? [] as $gatewayId => $gatewayData) {
            if (empty($gatewayData['flex_form_id'])) continue;
            $syncData[$gatewayId] = [
                'flex_form_id' => $gatewayData['flex_form_id'] ?? null
            ];
        }

        $plan->gateways()->sync($syncData);

        $request->session()->flash('admin_message_success', $request->id ? __('Plan has been successfully updated.') : __('Plan has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete_plan($id)
    {
        $plan = UserSubscriptionPlan::findOrFail($id);

        $plan->doDeleted();
        
        return redirect()->back()->with([
            'admin_message_success' => __('Plan has been deleted.'),
        ]);
    }

    public function pricing_table($language = null)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Customize Pricing Plans'),
            ],
        ];
        $title = __('Customize Pricing Plans');

        $packages = UserSubscriptionPackage::orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();

        $highlightBadgesList = getSubscriptionHighlightBadgeList();

        $languages = Language::getAll();

        if (!$language) {
            $language = config('shaun_core.language.system_default');
        }

        if (! in_array($language, $languages->pluck('key')->all())) {
            abort(404);
        }

        $packageCompares = UserSubscriptionPackageCompare::all();

        $packageCompareColumns = UserSubscriptionPackageCompareColumn::all();

        $highlightBadge = [
            'highlight_background_color' => getSubscriptionHighlightBackgroundColor(),
            'highlight_text_color' => getSubscriptionHighlightTextColor()
        ];

        $highlightAs = Key::getValue('user_subscription_highlight_as');

        return view('shaun_user_subscription::admin.package.pricing_table', compact('breadcrumbs', 'title', 'packages', 'highlightBadgesList', 'languages', 'language', 'packageCompares', 'packageCompareColumns', 'highlightBadge', 'highlightAs'));
    }

    public function store_plan_order(Request $request)
    {
        $rules = [
            'orders' => 'required',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $orders = $request->orders;
        foreach ($orders as $order => $id) {
            $plan = UserSubscriptionPlan::find($id);
            if (! $plan) {
                continue;
            }
            $plan->update([
                'order' => $order + 1,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function store_order(Request $request)
    {
        $rules = [
            'orders' => 'required',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $orders = $request->orders;
        foreach ($orders as $order => $id) {
            $package = UserSubscriptionPackage::find($id);
            if (! $package) {
                continue;
            }
            $package->update([
                'order' => $order + 1,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function store_pricing_table(Request $request)
    {
        $data = $request->except('id', '_token');

        Key::setValue('user_subscription_highlight_background_color', $request->highlight_background_color);
        Key::setValue('user_subscription_highlight_text_color', $request->highlight_text_color);
        Key::setValue('user_subscription_highlight_as', $request->highlight_as);

        if($request->selected_packages){
            UserSubscriptionPackage::whereIn('id', $request->selected_packages)->update(['is_show' => 1]);
            UserSubscriptionPackage::whereNotIn('id', $request->selected_packages)->update(['is_show' => 0]);
        }else{
            UserSubscriptionPackage::query()->update(['is_show' => 0]);
        }
        UserSubscriptionPackage::where('id', '<>', $request->highlighted_package)->update(['is_highlight' => 0]);
        UserSubscriptionPackage::where('id', $request->highlighted_package)->update(['is_highlight' => 1]);

        $ids = array();
        foreach ($request['sc'] as $subscriptionCompare) {
            if (!empty($subscriptionCompare['name'])) {
                $item = ['name' => $subscriptionCompare['name']];
                if (!empty($subscriptionCompare['id'])) {
                    $subscriptionPackageCompare = UserSubscriptionPackageCompare::findOrFail($subscriptionCompare['id']);
                    $subscriptionPackageCompare->update($item);
                    $subscriptionPackageCompare->updateTranslations($request['language']);
                    $ids[] = (int)($subscriptionCompare['id']); 
                } else {
                    $subscriptionPackageCompare = UserSubscriptionPackageCompare::create($item);
                    $ids[] = (int)($subscriptionPackageCompare->id);
                }

                if(!empty($subscriptionCompare['scc'])){
                    foreach ($subscriptionCompare['scc'] as $key => $subscriptionCompareColumn) {
                        $data = ['compare_id' => $subscriptionPackageCompare->id, 'package_id' => $key, 'type' => $subscriptionCompareColumn['type']];
                        if($subscriptionCompareColumn['type'] == 'text'){
                            $data['value'] = $subscriptionCompareColumn['text_value'];
                        }else{
                            $data['value'] = $subscriptionCompareColumn['boolean_value'];
                        }
                        if (!empty($subscriptionCompareColumn['id'])) {
                            $subscriptionPackageCompareColumn = UserSubscriptionPackageCompareColumn::findOrFail($subscriptionCompareColumn['id']);
                            $subscriptionPackageCompareColumn->update($data);    
                            $subscriptionPackageCompareColumn->updateTranslations($request['language']);    
                        } else {
                            $subscriptionPackageCompareColumn = UserSubscriptionPackageCompareColumn::create($data);
                        }
                    }
                }          
            }
        }

        UserSubscriptionPackageCompare::whereNotIn('id', $ids)->delete();
        UserSubscriptionPackageCompareColumn::whereNotIn('compare_id', $ids)->delete();
        Cache::forget('user_subscription_package_compares');
        Cache::forget('user_subscription_packages');

        return redirect()->back()->with([
            'admin_message_success' => __('Pricing plans have been updated.'),
        ]);
    }

    public function preview_pricing_table()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Manage Packages'),
                'route' => 'admin.user_subscription.index',
            ],
            [
                'title' => __('Preview Pricing Plans'),
            ]
        ];
        $title = __('Preview Pricing Plans');

        $packages = UserSubscriptionPackage::getAll()->filter(function ($value, $key) {
            return count($value->getPlans()) > 0;
        });

        $packageCompares = UserSubscriptionPackageCompare::all();

        $packageCompareColumns = UserSubscriptionPackageCompareColumn::all();

        $highlightBadge = [
            'highlight_background_color' => getSubscriptionHighlightBackgroundColor(),
            'highlight_text_color' => getSubscriptionHighlightTextColor()
        ];

        $highlightBadgesList = getSubscriptionHighlightBadgeList();
        $highlightAs = Key::getValue('user_subscription_highlight_as');
        $highlightBadgeValue = $highlightAs ? $highlightBadgesList[$highlightAs] : '';

        return view('shaun_user_subscription::admin.package.preview_pricing_table', compact('breadcrumbs', 'title', 'packages', 'packageCompares', 'packageCompareColumns', 'highlightBadge', 'highlightBadgeValue'));
    }
}
