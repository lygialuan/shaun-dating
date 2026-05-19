<?php


namespace Packages\ShaunSocial\PaidContent\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;

class UserSubscriberPackage extends Model
{
    use HasCacheQueryFields, HasUser;

    protected $cacheQueryFields = [
        'user_id',
        'id'
    ];

    protected $fillable = [
        'user_id',
        'package_id',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    protected $package = null;

    public function getPackage()
    {
        if ($this->package === null) {
            $this->package = SubscriberPackage::findByField('id', $this->package_id);
        }

        return $this->package;
    }
}
