<?php

return [
    'search_pagination_time' => 3,
    'search_location_cache_time' => 600,
    'score_min' => 0,
    'score_max' => 10,
    'feature_count_lock_time' => 30,
    'nearby_listing' => 5,
    'latest_listings' => 5,
    'cache' => [
        'file_name_config' => 'cache',
        'time' => [
            'feature_count_lock_time' => 30,
            'suggest_address' => 600,
            '10_sec' => 10,
            '30_sec' => 30,
            '1_min' => 60,
            '2_min' => 120,
            '5_min' => 300,
            '10_min' => 600,
            '30_min' => 1800,
            '1_hour' => 3600,
            '2_hour' => 7200,
            '5_hour' => 18000,
            '1_day' => 86400,
        ],
    ],
    'map_option' => [
        'open_street_map' => 0,
        'google_map' => 1
    ],
    'date_left_popular' => 1,
    'date_high_rate_review' => 3,
];
