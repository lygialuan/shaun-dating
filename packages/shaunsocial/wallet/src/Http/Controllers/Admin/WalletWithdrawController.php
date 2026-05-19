<?php

namespace Packages\ShaunSocial\Wallet\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Wallet\Enum\WalletWithdrawStatus;
use Packages\ShaunSocial\Wallet\Models\WalletWithdraw;
use Packages\ShaunSocial\Wallet\Repositories\Api\WalletRepository;

class WalletWithdrawController extends Controller
{
    use Utility;

    protected $walletRepository;

    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;

        $this->middleware('has.permission:admin.wallet.withdraw_manage');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Transfer Fund Requests'),
            ],
        ];
        $title = __('Transfer Fund Requests');
        $prefix = env('DB_PREFIX');
        $builder = WalletWithdraw::selectRaw($prefix.'wallet_withdraws.*')->orderBy('wallet_withdraws.id','desc');
        $name = $request->query('name');
        if ($name) {
            $builder->join('users', function ($join) use ($name) {
                $join->on('users.id', '=', 'wallet_withdraws.user_id')->where(function ($query) use ($name){
                    $query->where('users.name', 'LIKE', '%'.$name.'%')->orWhere('users.user_name', 'LIKE', '%'.$name.'%');
                });
                    
            });
        }

        $statusArray = WalletWithdrawStatus::getAll();
        $status = $request->query('status', '');
        if (! in_array($status, array_keys($statusArray))) {
            $status = '';
        }

        if ($status !== '') {
            $builder->where('status', $status);
        }
        
        $typeArray = WalletWithdraw::getAllType();
        $type = $request->query('type', '');
        if (! in_array($type, array_keys($typeArray))) {
            $type = '';
        }

        if ($type !== '') {
            $builder->where('type', $type);
        }

        $withdraws = $builder->paginate(setting('feature.item_per_page'));
        return view('shaun_wallet::admin.withdraw.index', compact('breadcrumbs', 'title', 'name', 'withdraws', 'statusArray', 'status' ,'type', 'typeArray'));
    }

    public function store_accept(Request $request)
    {
        $withdraw = WalletWithdraw::findOrFail($request->id);
        if (! $withdraw->canAccept()) {
            abort(403);
        }

        $this->walletRepository->accept_withdraw($withdraw);

        return redirect()->back()->with([
            'admin_message_success' =>  __('This payment request has been successfully accepted.'),
        ]);
    }

    public function detail(Request $request)
    {
        $withdraw = WalletWithdraw::findOrFail($request->id);
        return view('shaun_wallet::admin.withdraw.detail', compact('withdraw'));
    }

    public function store_reject(Request $request)
    {
        $withdraw = WalletWithdraw::findOrFail($request->id);
        if (! $withdraw->canReject()) {
            abort(403);
        }

        $result = $this->walletRepository->reject_withdraw($withdraw);
        if ($result['status']) {
            return redirect()->back()->with([
                'admin_message_success' =>  __('This payment request has been rejected.'),
            ]);
        } else {
            return redirect()->back()->with([
                'admin_message_error' =>  $result['message'],
            ]);
        }
    }

    public function store_manage(Request $request)
    {
        $action = $request->get('action');
        $ids = $request->get('ids');        
        if (! is_array($ids) && $action != 'export') {
            abort(404);
        }
        $message = '';

        switch ($action) {
            case 'accept':
                foreach ($ids as $id) {
                    $withdraw = WalletWithdraw::findByField('id', $id);
                    if ($withdraw && $withdraw->canAccept()) {
                        $this->walletRepository->accept_withdraw($withdraw);
                    }
                }
                $message = __('The selected request(s) have been accepted successfully.');
                break;
            case 'reject':
                foreach ($ids as $id) {
                    $withdraw = WalletWithdraw::findByField('id', $id);
                    if ($withdraw && $withdraw->canReject()) {
                        $this->walletRepository->reject_withdraw($withdraw);
                    }
                }
    
                $message = __('The selected request(s) have been rejected.');
                break;
            case 'export':
                $prefix = env('DB_PREFIX');
                $builder = WalletWithdraw::selectRaw($prefix.'wallet_withdraws.*')->orderBy('wallet_withdraws.id','desc');
                $name = $request->input('name');
                if ($name) {
                    $builder->join('users', function ($join) use ($name) {
                        $join->on('users.id', '=', 'wallet_withdraws.user_id')->where(function ($query) use ($name){
                            $query->where('users.name', 'LIKE', '%'.$name.'%')->orWhere('users.user_name', 'LIKE', '%'.$name.'%');
                        });
                            
                    });
                }

                $statusArray = WalletWithdrawStatus::getAll();
                $status = $request->input('status', '');
                if (! in_array($status, array_keys($statusArray))) {
                    $status = '';
                }

                if ($status !== '') {
                    $builder->where('status', $status);
                }

                $typeArray = WalletWithdraw::getAllType();

                $type = $request->input('type', '');
                if (! in_array($type, array_keys($typeArray))) {
                    $type = '';
                }

                if ($type !== '') {
                    $builder->where('type', $type);
                }

                $withdraws = $builder->get();
                $data = [
                    [
                        __('Account detail'),
                        __('Payment method'),
                        __('Net'),
                        __('Currency'),
                        __('Status')
                    ]
                ];
                foreach ($withdraws as $withdraw) {
                    $data[] = [
                        $withdraw->bank_account,
                        $withdraw->getPaymentMethod(),
                        $withdraw->getAmountNet(),
                        $withdraw->currency,
                        $withdraw->getStatusText()
                    ];
                }
                $this->downloadCsvFile($data, 'withdraw.csv');

                break;
            default:
                abort(404);
                break;
        }
        
        return redirect()->back()->with([
            'admin_message_success' => $message,
        ]);
    }
}
