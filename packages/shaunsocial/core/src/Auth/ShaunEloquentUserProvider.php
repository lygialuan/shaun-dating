<?php


namespace Packages\ShaunSocial\Core\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Packages\ShaunSocial\Core\Models\User;

class ShaunEloquentUserProvider extends EloquentUserProvider
{
    public function __construct(HasherContract $hasher)
    {
        parent::__construct($hasher, User::class);
    }

    public function retrieveById($identifier)
    {
        return User::findByField('id', $identifier);
    }
}
