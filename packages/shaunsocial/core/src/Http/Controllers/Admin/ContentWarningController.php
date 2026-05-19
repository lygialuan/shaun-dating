<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\ContentWarningCategory;
use Illuminate\Support\Facades\Validator;

class ContentWarningController extends Controller
{

    public function __construct()
    {
        $this->middleware('has.permission:admin.content_warning.manage_category');
    }

    public function category()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Content Warning Categories'),
            ],
        ];
        $title = __('Content Warning Categories');
        $categories = ContentWarningCategory::orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();

        return view('shaun_core::admin.content_warning.category', compact('breadcrumbs', 'title', 'categories'));
    }

    public function create_category($id = null)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Content Warning Categories'),
                'route' => 'admin.content_warning.category',
            ],
        ];
        if (empty($id)) {
            $category = new ContentWarningCategory();
            $breadcrumbs[] = [
                'title' => __('Create'),
            ];
        } else {
            $category = ContentWarningCategory::findOrFail($id);

            $breadcrumbs[] = [
                'title' => __('Edit'),
            ];
        }

        $title = empty($id) ? __('Create') : __('Edit');
        
        return view('shaun_core::admin.content_warning.create_category', compact('title', 'category', 'breadcrumbs'));
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
            $category = ContentWarningCategory::findOrFail($request->id);
            if (!$category->canDelete()) {
                $data['is_active'] = true;
            }
            $category->update($data);
        } else {
            ContentWarningCategory::create($data);
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Content warning category has been successfully updated.') : __('Content warning category has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete_category($id)
    {
        $category = ContentWarningCategory::findOrFail($id);
        if ($category->canDelete()) {
            $category->doDeleted();
        }

        return redirect()->route('admin.content_warning.category')->with([
            'admin_message_success' => __('Content warning category has been deleted.'),
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
            $category = ContentWarningCategory::find($id);
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
}
