<?php


namespace Packages\ShaunSocial\UserPage\Validation;
use Packages\ShaunSocial\Core\Validation\UserNameValidate;

class PageAliasValidate extends UserNameValidate
{
    protected $message = null;
    
    public function __construct()
    {
        $this->message = __('Between 5 and 30 characters for the alias is acceptable, can be any combination of letters, numbers, or some special characters. Usernames can contain letters (a-z), (A-Z), numbers (0-9), periods (.), or the underscore character (_).');
    }
}
