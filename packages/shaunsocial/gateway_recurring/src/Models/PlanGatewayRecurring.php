<?php

namespace Packages\ShaunSocial\GatewayRecurring\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class PlanGatewayRecurring extends Model
{
    use HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $fillable = [
        'plan_id',
        'gateway_recurring_id',
        'flex_form_id'
    ];
}
