<?php
namespace Packages\ShaunSocial\Dating\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class DatingProfileCompletionSetting extends Model
{
    use HasCacheQueryFields;

    public $timestamps = true;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $fillable = [
        'is_active',
        'basic_info',
        'about',
        'profile_verification',
        'work_education',
        'more_about',
        'interests',
        'social_profiles',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    protected static function booted()
    {
        parent::booted();
    }
}
