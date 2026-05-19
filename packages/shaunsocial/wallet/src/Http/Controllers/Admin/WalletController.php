<?php

namespace Packages\ShaunSocial\Wallet\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Wallet\Models\WalletTransaction;

class WalletController extends Controller
{
    public function __construct()
    {
        $routerName = Route::getCurrentRoute()->getName();
        switch ($routerName) {
            case 'admin.wallet.index':
            case 'admin.wallet.transactions':
                $this->middleware('has.permission:admin.wallet.manage');
                break;
            case 'admin.wallet.billing_activity':
                $this->middleware('has.permission:admin.wallet.billing_activity');
                break;
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
                'title' => __('Wallets'),
            ],
        ];
        $title = __('Wallets');
        $builder = User::orderBy('id','desc');

        $name = $request->query('name');
        if ($name) {
            $builder->where(function ($query) use ($name){
                $query->where('name', 'LIKE', '%'.$name.'%')->orWhere('user_name', 'LIKE', '%'.$name.'%');
            });
        }

        $users = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_wallet::admin.wallet.index', compact('breadcrumbs', 'title', 'users', 'name'));
    }

    public function transactions(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Wallets'),
                'route' => 'admin.wallet.index',
            ],
            [
                'title' => __('Transactions'),
            ],
        ];
        $title = __('Transactions');
        $user = User::findOrFail($request->user_id);

        $builder = WalletTransaction::where('user_id', $request->user_id)->where('is_active', true)->orderBy('id','desc');
        $transactions = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_wallet::admin.wallet.transactions', compact('breadcrumbs', 'title', 'transactions', 'user'));
    }

    public function billing_activity(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __("System's billing activities"),
            ],
        ];
        $title = __("System's billing activities");
        $prefix = env('DB_PREFIX');
        $builder = WalletTransaction::selectRaw($prefix.'wallet_transactions.*')->where('wallet_transactions.user_id', config('shaun_wallet.system_wallet_user_id'))->orderBy('wallet_transactions.id','desc');

        $name = $request->input('name');
        if ($name) {
            $builder->join('users', function ($join) use ($name) {
                $join->on('users.id', '=', 'wallet_transactions.from_user_id')->where(function ($query) use ($name){
                    $query->where('users.name', 'LIKE', '%'.$name.'%')->orWhere('users.user_name', 'LIKE', '%'.$name.'%');
                });
                    
            });
        }

        $typeArray = [
            'deposit' => __('Deposit'),
            'payment' => __('Payment'),
            'withdraw' => __('Withdraw')
        ];

        $type = $request->input('type', '');
        if (! in_array($type, array_keys($typeArray))) {
            $type = '';
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
                $builder->where('wallet_transactions.created_at', '>=', now()->subDays(30));
                break;
            case '60_day':
                $builder->where('wallet_transactions.created_at', '>=', now()->subDays(60));
                break;
            case '90_day':
                $builder->where('wallet_transactions.created_at', '>=', now()->subDays(90));
                break;
            case 'custom':
                if ($fromDate) {
                    $builder->where('wallet_transactions.created_at', '>=', $fromDate. ' 00:00:00');
                }
                if ($toDate) {
                    $builder->where('wallet_transactions.created_at', '<=', $toDate. ' 23:59:59');
                }
                break;
        }
        $totalDeposit = 0;
        $totalPayment = 0;
        $totalWithdraw = 0;
        if ($type !== '') {
            switch ($type) {
                case 'deposit':
                    $builder->where('type', 'root_buy');
                    $totalDeposit = $builder->sum('amount');
                    break;
                case 'payment':
                    $builder->where('type', 'payment');
                    $totalPayment = $builder->sum('amount');
                    break;
                case 'withdraw':
                    $builder->where('type', 'root_withdraw');
                    $totalWithdraw = $builder->sum('amount');
                    break;
            }
        } else {
            $builderTmp = clone $builder;
            $builderTmp->where('type', 'root_buy');
            $totalDeposit = $builderTmp->sum('amount');

            $builderTmp = clone $builder;
            $builderTmp->where('type', 'payment');
            $totalPayment = $builderTmp->sum('amount');

            $builderTmp = clone $builder;
            $builderTmp->where('type', 'root_withdraw');
            $totalWithdraw = $builderTmp->sum('amount');
        }

        $transactions = $builder->paginate(setting('feature.item_per_page'));
        
        return view('shaun_wallet::admin.wallet.billing_activity', compact('breadcrumbs', 'title', 'transactions', 'typeArray', 'type', 'dateType', 'dateTypeArray', 'totalDeposit', 'totalPayment', 'totalWithdraw', 'name', 'fromDate', 'toDate'));
    }
}
