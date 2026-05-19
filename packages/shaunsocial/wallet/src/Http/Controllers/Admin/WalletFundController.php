<?php

namespace Packages\ShaunSocial\Wallet\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Validation\AmountValidation;
use Packages\ShaunSocial\Wallet\Repositories\Api\WalletRepository;

class WalletFundController extends Controller
{
    use Utility;

    protected $walletRepository;

    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;

        $this->middleware('has.permission:admin.wallet.mass_funds');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Transfer Mass Funds'),
            ],
        ];
        $title = __('Transfer Mass Funds');
    
        return view('shaun_wallet::admin.fund.index', compact('breadcrumbs', 'title'));
    }

    public function send(Request $request)
    {
        $request->validate(
            [
                'id' => 'required|alpha_num',
                'amount' => ['required', 'integer',  'max:100000', 'min:-100000']
            ],
            [
                'id.required' => __('The user is required.'),
                'amount.required' => __('The amount is required.'),
                'amount.max' => __('The amount must be less than 100000'),
                'amount.min' => __('The amount must be greater than -100000')
            ]
        );

        $user = User::findOrFail($request->id);
        if ($request->amount < 0) {
            $balance = $user->getCurrentBalance();
            if ($balance < abs($request->amount)) {
                return redirect()->back()->withInput()->with([
                    'admin_message_error' => __('The amount must not be less than'). ' '.$balance
                ]);
            }
        }
        
        $this->walletRepository->send_mass_fund($request->only([
            'id', 'amount', 'notify'
        ]), $request->user());

        return redirect()->route('admin.wallet.fund.index')->with([
            'admin_message_success' => __('Successfully sent!')
        ]);
    }
}
