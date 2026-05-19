<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Report;
use Packages\ShaunSocial\Core\Models\ReportCategory;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{

    public function __construct()
    {
        $routerList = [
            'admin.report.index',
            'admin.report.delete'
        ];

        if (in_array(Route::getCurrentRoute()->getName(), $routerList)) {
            $this->middleware('has.permission:admin.report.manage');
        } else {
            $this->middleware('has.permission:admin.report.manage_category');
        }
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Reports'),
            ],
        ];
        $title = __('Reports');
        $categories = ReportCategory::getAll();
        $builder = Report::orderBy('id', 'desc')->select('reports.*');

        $categoryId = $request->query('category_id', 0);
        if ($categoryId) {
            $builder->where('category_id', $categoryId);
        }

        $name = $request->query('name');
        if ($name) {
            $builder->where(function($builder) use ($name) {
                $builder->orWhere('reports.reason', 'LIKE', '%'.$name.'%');
                $builder->orWhere(function($builder) use ($name) {
                    $builder->join('users', function ($join) use ($name) {
                        $join->on('users.id', '=', 'reports.user_id')->where(function ($query) use ($name){
                            $query->where('users.name', 'LIKE', '%'.$name.'%');
                        });
                    });
                });
            });
        }

        $reports = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_core::admin.report.index', compact('breadcrumbs', 'title', 'reports', 'name', 'categoryId', 'categories'));
    }

    public function category()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Report Categories'),
            ],
        ];
        $title = __('Report Categories');
        $categories = ReportCategory::orderBy('order')->orderBy('id','DESC')->get();

        return view('shaun_core::admin.report.category', compact('breadcrumbs', 'title', 'categories'));
    }

    public function create_category($id = null)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Report Categories'),
                'route' => 'admin.report.category',
            ],
        ];
        if (empty($id)) {
            $category = new ReportCategory();
            $breadcrumbs[] = [
                'title' => __('Create'),
            ];
        } else {
            $category = ReportCategory::findOrFail($id);

            $breadcrumbs[] = [
                'title' => __('Edit'),
            ];
        }

        $title = empty($id) ? __('Create') : __('Edit');
        
        return view('shaun_core::admin.report.create_category', compact('title', 'category', 'breadcrumbs'));
    }

    public function store_category(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.'),
                'name.max' => __('The name must not be greater than 255 characters.'),
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

        if ($request->id) {
            $category = ReportCategory::findOrFail($request->id);
            if (!$category->canDelete()) {
                $data['is_active'] = true;
            }

            $category->update($data);
        } else {
            ReportCategory::create($data);
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Report category has been successfully updated.') : __('Report category has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function store_category_order(Request $request)
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
            $category = ReportCategory::find($id);
            if (! $category) {
                continue;
            }
            $category->update([
                'order' => $order + 1,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $report = Report::findOrFail($id);

        $report->delete();

        return redirect()->route('admin.report.index')->with([
            'admin_message_success' => __('Report has been deleted.'),
        ]);
    }

    public function multi_delete(Request $request)
    {
        $ids = $request->get('ids');        
        if (! is_array($ids)) {
            abort(404);
        }

        foreach ($ids as $id) {
            $report = Report::findByField('id', $id);
            $report->delete();
        }

        return redirect()->back()->with([
            'admin_message_success' => __('The selected report(s) have been deleted.')
        ]);
    }

    public function delete_category($id)
    {
        $category = ReportCategory::findOrFail($id);

        if (! $category->canDelete()) {
            abort(400);
        }

        $category->doDeleted();

        return redirect()->route('admin.report.category')->with([
            'admin_message_success' => __('Report category has been deleted.'),
        ]);
    }
}
