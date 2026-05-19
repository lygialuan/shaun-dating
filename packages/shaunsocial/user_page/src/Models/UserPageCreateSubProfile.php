<?php

namespace Packages\ShaunSocial\UserPage\Models;

use Illuminate\Database\Eloquent\Model;

class UserPageCreateSubProfile extends Model
{
    protected $fillable = [
        'user_id',
        'number_of_users',
        'expire_role_id',
        'gender_id',
        'from_age',
        'to_age',
        'about_me',
        'interests',
        'country_id',
        'state_id',
        'city_id',
        'is_created',
    ];

    protected $casts = [
        'about_me' => 'array',
        'interests' => 'array',
        'is_created' => 'boolean',
    ];
}
