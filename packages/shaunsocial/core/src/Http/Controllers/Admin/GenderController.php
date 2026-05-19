<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Gender;

class GenderController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.gender.manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Genders'),
            ],
        ];
        $title = __('Genders');
        $genders = Gender::orderBy('order')->orderBy('id','DESC')->get();

        return view('shaun_core::admin.gender.index', compact('breadcrumbs', 'title', 'genders'));
    }

    public function create($id = null)
    {
        if (empty($id)) {
            $gender = new Gender();
        } else {
            $gender = Gender::findOrFail($id);
        }

        $title = empty($id) ? __('Create') : __('Edit');

        return view('shaun_core::admin.gender.create', compact('title', 'gender'));
    }

    public function store(Request $request)
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
            $gender = Gender::findOrFail($request->id);

            $gender->update($data);
        } else {
            Gender::create($data);
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Gender has been successfully updated.') : __('Gender has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function store_order(Request $request) {
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
            $gender = Gender::find($id);
            if (! $gender) {
                continue;
            }
            $gender->update([
                'order' => $order + 1,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $gender = Gender::findOrFail($id);

        $gender->doDeleted();

        return redirect()->route('admin.gender.index')->with([
            'admin_message_success' => __('Gender has been deleted.'),
        ]);
    }
}
