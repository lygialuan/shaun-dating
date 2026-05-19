<?php


namespace Packages\ShaunSocial\Group\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Group\Models\GroupCategory;

class GroupCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.group.manage_categories');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Group Categories'),
            ],
        ];
        $title = __('Group Categories');

        $categories = GroupCategory::getAll();

        return view('shaun_group::admin.category.index', compact('breadcrumbs', 'title', 'categories'));
    }

    public function create($id = null)
    {
        if (empty($id)) {
            $category = new GroupCategory();
            $category->is_active = true;
        } else {
            $category = GroupCategory::findOrFail($id);
        }

        $title = empty($id) ? __('Create') : __('Edit');

        $categoryParents = GroupCategory::where('id', '!=', $category->id)->where('parent_id', 0)->get();
        
        return view('shaun_group::admin.category.create', compact('title', 'categoryParents', 'category'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.')
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
            $category = GroupCategory::findOrFail($request->id);
            $category->update($data);
        } else {
            GroupCategory::create($data);
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Group category has been successfully updated.') : __('Group category has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $category = GroupCategory::findOrFail($id);

        $category->doDeleted();

        if (count($category->childs)) {
            foreach ($category->childs as $child) {
                $child->doDeleted();
            }
        }

        return redirect()->route('admin.group.category.index')->with([
            'admin_message_success' => __('Group category has been deleted.'),
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
            $category = GroupCategory::find($id);
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
