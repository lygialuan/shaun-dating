<?php

namespace Packages\ShaunSocial\Gift\Repositories\Api;

use Packages\ShaunSocial\Gift\Models\Gift;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Gift\Models\GiftTransaction;
use Packages\ShaunSocial\Gift\Models\GiftAggregate;
use Packages\ShaunSocial\Gift\Support\Facades\Gift as GiftService;
use Packages\ShaunSocial\Gift\Http\Resources\GiftTransactionResource;

class GiftRepository
{
    public function send($viewer, $data)
    {
        return GiftService::send($viewer, $data);
    }
  
    public function findGift($id)
    {
        return Gift::findByField('id', $id);
    }

    public function findUser($id)
    {
        return User::findByField('id', $id);
    }

    public function createTransaction($data)
    {
        return GiftTransaction::create($data);
    }

    public function updateAggregate($data)
    {
        $aggregate = GiftAggregate::firstOrCreate(
            [
                'target_type' => $data['target_type'],
                'target_id'   => $data['target_id'],
                'sender_id'   => $data['sender_id'],
                'receiver_id' => $data['receiver_id'],
            ],
            [
                'total_gifts'  => 0,
                'total_amount' => 0,
            ]
        );

        $aggregate->increment('total_gifts', $data['quantity']);
        $aggregate->increment('total_amount', $data['total_price']);
        $aggregate->clearCache();
    }

    public function giftReceived($userId, $page)
    {
        $query = GiftTransaction::query()
            ->where('receiver_id', $userId)
            ->with([
                'gift:id,name,icon,icon_default',
                'sender:id,name'
            ])->latest();

        $gifts = GiftTransaction::getCachePagination('user_gift_received_'.$userId, $query, $page);
        $giftsNextPage = GiftTransaction::getCachePagination('user_gift_received_'.$userId, $query, $page + 1);

        return [
            'items' => GiftTransactionResource::collection($gifts),
            'has_next_page' => count($giftsNextPage) ? true : false
        ];
    }
}