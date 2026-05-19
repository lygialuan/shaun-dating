<?php

namespace Packages\ShaunSocial\Gateway\Models;

interface OrderBase
{
    public function getItems();

    public function getCurrency();

    public function getTotalAmount();

    public function getReturnUrl();
    
    public function getCancelUrl();
    
    public function onSuccessful($data, $transactionId);
}
