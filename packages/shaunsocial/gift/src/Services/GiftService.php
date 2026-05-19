<?php

namespace Packages\ShaunSocial\Gift\Services;

use Packages\ShaunSocial\Gift\Repositories\Api\GiftRepository;
use Packages\ShaunSocial\Wallet\Repositories\Api\WalletRepository;
use Packages\ShaunSocial\Wallet\Support\Facades\Wallet;

class GiftService
{
    protected $giftRepository;
    protected $walletRepository;

    public function __construct(WalletRepository $walletRepository, GiftRepository $giftRepository)
    {
        $this->giftRepository = $giftRepository;
        $this->walletRepository = $walletRepository;
    }

    public function send($sender, $data)
    {
        $gift           = $this->giftRepository->findGift($data['gift_id']);
        $receiver       = $this->giftRepository->findUser($data['receiver_id']);

        $quantity       = $data['quantity'];
        $totalPrice     = $gift->price * $quantity;
        $feePercent     = (float) setting('shaun_gift.platform_fee', 0);
        $platformFee    = round($totalPrice * $feePercent / 100, 2);

        $result = $this->walletRepository->store_send(['id'=> $data['receiver_id'], 'amount'=> $totalPrice], $sender, ['type'=> 'gift', 'fee' => $platformFee]);
        if (!$result['status']) {
            return $result;
        }

        $transactionGift = $this->giftRepository->createTransaction([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
            'gift_id'     => $gift->id,
            'quantity'    => $quantity,
            'price'       => $gift->price,
            'total_price' => $totalPrice,
            'target_type' => $data['target_type'],
            'target_id'   => $data['target_id'],
        ]);

        $this->giftRepository->updateAggregate([
            'target_type' => $data['target_type'],
            'target_id'   => $data['target_id'],
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
            'quantity'    => $quantity,
            'total_price' => $totalPrice
        ]);

        if ($feePercent) {
            Wallet::add('payment', config('shaun_wallet.system_wallet_user_id'), $platformFee, $transactionGift, 'gift_fee', $receiver->id);
        }

        return $result;
    }
}