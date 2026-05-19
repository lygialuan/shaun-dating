<?php

use Packages\ShaunSocial\Core\Models\Key;

if (! function_exists('getSubscriptionHighlightBadgeList')) {
    function getSubscriptionHighlightBadgeList()
    {
        return [
            'most_popular' => __('Most Popular'),
            'best_deal' => __('Best Deal'),
            'recommended' => __('Recommended')
        ];
    }
}

if (! function_exists('getSubscriptionHighlightBackgroundColor')) {
    function getSubscriptionHighlightBackgroundColor()
    {
        return Key::getValue('user_subscription_highlight_background_color','#ff0000');
    }
}

if (! function_exists('getSubscriptionHighlightTextColor')) {
    function getSubscriptionHighlightTextColor()
    {
        return Key::getValue('user_subscription_highlight_text_color','#ffffff');
    }
}