<?php


return [
    'core' => [
        'queue' => env('SHAUN_QUEUE', false),
        'auto_delete_day' => 15,
        'number_item_random' => 50,
        'number_item_pin' => 50,
        'prefix_profile' => '@',
        'http_timeout' => 10,
        'user_name_min_length' => 5,
        'user_suggest_check_limit' => 5,
        'attempt_limit' => 120,
        'send_email_attempt_limit' => 10,
        'chart_day' => 7,
        'password_length' => 6,
        'limit_delete' => 2,
        'time_coookie_login' => 2628000,
        'user_root_id' => 1,
        'user_hastag_delete_day' => 60,
        'user_bio_limit' => 180,
        'user_about_limit' => 3500,
        'email_domain_default' => 'example.com',
        'check_fetch_domain' => [
            'https://twitter.com',
            'https://www.facebook.com',
            'https://www.instagram.com'
        ],
        'hashtag_suggest_relative_limit' => 10,
        'decimal' => 2,
        'permission_code' => 402,
        'statistic_lock' => 60,
        'limit_send_phone' => 60,
        'limit_save_auto' => 50,
        'sql_text_max_length' => 65000,
        'suggest_search_limit' => 10,
        'number_sitemap_get' => 100,
        'number_sitemap_per_file' => 5000,
    ],
    'role' => [
        'id' => [
            'root' => 1,
            'member_default' => 2,
            'guest' => 3,
        ],
    ],
    'language' => [
        'en_id' => 1,
        'system_default' => 'en',
    ],
    'cache' => [
        'file_name_config' => 'cache',
        'time' => [
            'model_query' => 86400,
            'mail_unsubscrite' => 86400,
            'access_token_update' => 3600,
            'pagination' => 86400,
            'search' => 86400,
            'trending' => 60,
            'short' => 5,
            'invite_info' => 86400,
            'search_pagination' => 30,
            'link' => 86400,
            'user_online' => 120,
            'suggest_check' => 120,
            'dashboard_count' => 900,
            'lock' => 1,
            'check_exist' => 30,
            'search_short_pagination' => 3
        ],
        'tags' => [
            'redis',
            'memcached',
        ],
        'short' => [
            'page_clear_cache' => 10
        ]
    ],
    'queue' => [
        'mail_normal' => 'mail_normal',
        'mail_many' => 'mail_many',
        'post_queue' => 'post_queue',
        'notification' => 'notification',
        'broadcast' => 'broadcast'
    ],
    'mail' => [
        'type_ignore_unsubscribe_list' => [
            'welcome',
            'email_verify',
            'forgot_password_code',
            'welcome_user_verify',
            'admin_change_password',
            'active_user',
            'inactive_user'
        ],
    ],
    'log' => [
        'lines' => [50, 100, 500, 1000, 50000, 100000],
    ],
    'file' => [
        'photo' => [
            'max_width' => 1024,
            'max_height' => 1024,
        ],
        'avatar' => [
            'width' => 200,
            'height' => 200,
        ],
        'transfer' => [
            'limit' => 10,
        ],
    ],
    'validation' => [
        'photo' => 'jpeg,jpg,png,gif,webp,heic',
        'video' => 'mp4,wmv,3gp,mov,avi',
        'csv' => 'csv,txt',
        'time_verify' => 360,
        'two_factor_verify' => 3600,
        'phone_verify' => 120,
    ],
    'materialicon' => [
        'version' => '6.5.95',
    ],
    'layout' => [
        'default_id' => 15,
        'header_footer_id' => 1,
    ],
    'follow' => [
        'user' => [
            'max_query_join' => 50
        ],
        'notify' => [
            'limit_run' => 4,
            'limit_send' => 200
        ]
    ],
    'pagination' => [
        'max_page_clear_cache' => 1000
    ],
    'block' => [
        'max_number_query_each_user' => 50
    ],
    'notify' => [
        'limit_number_owner_send' => 20,
        'limit_day_owner_send' => 10,
    ],
    'regex' => [
        'hashtag' => '/(#\w+)/u',
        'mention' => '/(@[\w.]+)/u',
        'user_name' => '/^[a-z\d._]{5,30}$/i',
        'user_name_router' => '[a-zA-Z\d._]{5,30}',
        'link' => '/https?:\\/\\/(?:www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b(?:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*)/',
        'phone_number' => '/^\\+?[0-9][0-9]{7,14}$/'
    ],
    'report' => [
        'other_id' => 1,
    ],
    'privacy' => [
        'user' => [
            'everyone' => 1,
            'my_follower' => 2,
            'only_me' => 3
        ]
    ],
    'search' => [
        'number_item_suggest' => 4
    ],
    'invite' => [
        'max_upload_csv' => 1,
        'number_email_send_queue' => 20
    ],
    'mail_daily' => [
        'limit_run' => 10,
        'max_notify_count' => 10,
        'day' => 1
    ],
    'spam' => [
        'sub_time' => 10
    ],
    'menu' => [
        'main_id' => 1,
        'mobile_menu_id' => 2,
        'footer_id' => 3
    ],
    'video' => [
        'limit_duration_convert_now' => env('VIDEO_TIME_CONVERT_NOW',120)
    ],
    'theme' => [
       'default_id' => 1
    ],
    'admod' => [
        'full_ads_default_click_show' => 5
    ],
    'trending_point' => [
        'follower' => 1,
        'reach' => 1,
        'diff_day' => 10
    ],
    'gender' => [
        'report_colors' => [
            1 => '#990000',
            2 => '#66FF66',
            3 => '#800080',
            4 => '#CC99CC',
            5 => '#3366CC',
            6 => '#6600CC',
            7 => '#306754',
            8 => '#FF9933'
        ],
        'report_unknown_color' => '#d8d8d8'
    ],
    'subscription' => [
        'remind_day' => 5
    ],
    'time_format' => [
        'payment' => 'DD MMMM YYYY, HH:mm',
        'date' => 'DD MMMM YYYY'
    ],
    'content_warning' => [
        'other_id' => 4,
    ],
    'source' => [
        'max_query_join' => 0
    ]
];
