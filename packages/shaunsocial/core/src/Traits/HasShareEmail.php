<?php


namespace Packages\ShaunSocial\Core\Traits;

trait HasShareEmail
{
    public function supportShareEmail()
    {
        return true;
    }

    public function canShareEmail($userId)
    {
        return true;
    }
}
