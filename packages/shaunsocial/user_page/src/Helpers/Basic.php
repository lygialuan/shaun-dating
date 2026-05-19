<?php 

if (! function_exists('getPageInfoPriceList')) {
    function getPageInfoPriceList()
    {
        return [
            [
                'value' => 1,
                'title' => '$',
                'description' => __('Cheap'),
            ],
            [
                'value' => 2,
                'title' => '$$',
                'description' => __('Moderate'),
            ],
            [
                'value' => 3,
                'title' => '$$$',
                'description' => __('Expensive'),
            ],
            [
                'value' => 4,
                'title' => '$$$$',
                'description' => __('Splurge'),
            ],
            [
                'value' => 0,
                'title' => __('Not applicable'),
                'description' => __('Price range is not applicable'),
            ],
        ];
    }
}

if (! function_exists('getPageInfoHourList')) {
    function getPageInfoHourList()
    {
        return [
            [
                'value' => 'none',
                'title' => __('No Hours Available'),
                'description' => __("Visitors won't see business hours on this Page"),
            ],
            [
                'value' => 'open',
                'title' => __('Always open'),
                'description' => __('e.g. parks, beaches, roads'),
            ],
            [
                'value' => 'close',
                'title' => __('Permanently closed'),
                'description' => __('This business has permanently closed'),
            ],
            [
                'value' => 'hours',
                'title' => __('Open during selected hours'),
                'description' => __('Input your own hours'),
            ],
        ];
    }
}

if (! function_exists('getPageInfoDayOfWeekList')) {
    function getPageInfoDayOfWeekList()
    {
        return ['mon' => __('Monday'), 'tue' => __('Tuesday'), 'wed' => __('Wednesday'), 'thu' => __('Thursday'), 'fri' => __('Friday'), 'sat' => __('Saturday'), 'sun' => __('Sunday')];
    }
}