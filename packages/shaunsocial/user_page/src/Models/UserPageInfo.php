<?php

namespace Packages\ShaunSocial\UserPage\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasLink;

class UserPageInfo extends Model
{
    use HasCacheQueryFields, HasLink;
    
    protected $cacheQueryFields = [
        'user_page_id'
    ];

    protected $fillable = [
        'user_page_id',
        'address',
        'phone_number',
        'email',
        'websites',
        'open_hours',
        'price',
        'description',
        'review_score',
        'review_count',
        'review_enable'
    ];

    protected $casts = [
        'review_enable' => 'boolean',
    ];

    public function getPrice()
    {
        $prices = collect(getPageInfoPriceList());
        $price = $this->price;
        return $prices->first(function ($item, $key) use ($price){
            return $item['value'] == $price;
        });
    }

    public function getOpenHours()
    {
        $hours = collect(getPageInfoHourList());
        $openHours = ['type' => 'none'];
        if ($this->open_hours) {
            $openHours = json_decode($this->open_hours, true);
        };
        $type = $openHours['type'];
        $values = isset($openHours['values']) ? $openHours['values'] : null;
        $hour = $hours->first(function ($item, $key) use ($type){
            return $item['value'] == $type;
        });
        return [
            'type' => $hour,
            'values' => $values
        ];
    }

    public function getWebsites()
    {
        return $this->getLinkWithField('websites');
    }
}
