<?php

namespace Packages\ShaunSocial\Gateway\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Gateway\Models\Gateway;

class GatewayController extends Controller
{
    public function __construct()
    {
       $this->middleware('has.permission:admin.gateway.manage');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Gateways'),
            ],
        ];
        $title = __('Gateways');
        $gateways = Gateway::orderBy('order', 'ASC')->where('show', true)->orderBy('id', 'DESC')->get();

        return view('shaun_gateway::admin.gateway.index', compact('breadcrumbs', 'title', 'gateways'));
    }


    public function edit($id)
    {
        $gateway = Gateway::findOrFail($id);

        $title = __('Edit');

        return view('shaun_gateway::admin.gateway.edit', compact('title', 'gateway'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
        ];

        $id = $request->id;
        $gateway = Gateway::findOrFail($id);

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.'),
                'name.max' => __('The name must not be greater than 255 characters.')
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $class = $gateway->getClass();
        $result = $class->checkConfig($request->post('config', []));
        if (! $result['status']) {
            return response()->json([
                'status' => false,
                'messages' => [
                    $result['message']
                ],
            ]);
        }

        $request->mergeIfMissing([
            'is_active' => false,
        ]);

        $data = $request->except('id', '_token', 'config');
        $data['config'] = json_encode($request->post('config', []));
        $gateway->update($data);
        $request->session()->flash('admin_message_success', __('Gateway has been successfully updated.'));

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
            $gateway = Gateway::find($id);
            if (! $gateway) {
                continue;
            }
            $gateway->update([
                'order' => $order + 1,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }
}
