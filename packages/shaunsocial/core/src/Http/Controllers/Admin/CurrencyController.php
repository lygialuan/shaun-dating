<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Models\Currency;

class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.currency.manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Currencies'),
            ],
        ];
        $title = __('Currencies');
        $currencies = Currency::all();

        return view('shaun_core::admin.currency.index', compact('breadcrumbs', 'title', 'currencies'));
    }

    public function create($id = null)
    {
        if (empty($id)) {
            $currency = new Currency();
        } else {
            $currency = Currency::findOrFail($id);
        }

        $title = empty($id) ? __('Create') : __('Edit');

        return view('shaun_core::admin.currency.create', compact('title', 'currency'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'code' => 'required|max:3',
            'symbol' => 'required|max:16',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.'),
                'name.max' => __('The name must not be greater than 255 characters.'),
                'code.required' => __('The code is required.'),
                'code.max' => __('The code must not be greater than 3 characters.'),
                'symbol.required' => __('The name is required.'),
                'symbol.max' => __('The name must not be greater than 16 characters.'),
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $data = $request->except('id', '_token');

        if ($request->id) {
            $currency = Currency::findOrFail($request->id);

            $currency->update($data);
        } else {
            Currency::create($data);
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Currency has been successfully updated.') : __('Currency has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $currency = Currency::findOrFail($id);

        if ($currency->canDelete()) {
            $currency->delete();
        }

        return redirect()->route('admin.currency.index')->with([
            'admin_message_success' => __('Currency has been deleted.'),
        ]);
    }

    public function store_default(Request $request)
    {
        $rules = [
            'id' => 'required',
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

        $currency = Currency::findOrFail($request->id);

        Currency::query()->update(['is_default' => 0]);
        $currency->update(['is_default' => 1]);

        return response()->json([
            'status' => true,
        ]);
    }
}
