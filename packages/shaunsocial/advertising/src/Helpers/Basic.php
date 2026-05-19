<?php

if (! function_exists('getAmountPerClickAdvertising')) {
    function getAmountPerClickAdvertising($count)
    {
        return $count * 0.01;
    }
}

if (! function_exists('getAmountPerViewAdvertising')) {
    function getAmountPerViewAdvertising($count)
    {
        return $count * 0.001;
    }
}

if (! function_exists('getPostNumberShowAdvertising')) {
    function getPostNumberShowAdvertising()
    {
        $number = setting('shaun_advertising.feed_number_show');
        if ($number < config('shaun_advertising.post_number_show') || $number > setting('feature.item_per_page')) {
            return config('shaun_advertising.post_number_show');
        }

        return $number;
    }
}