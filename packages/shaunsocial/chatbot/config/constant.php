<?php

return [    
    'default_settings' => [
        'max_message_length' => 4000,
        'max_context_messages' => 10,
        'response_timeout' => 30,
        'rate_limit' => [
            'requests_per_minute' => 60,
            'requests_per_hour' => 1000
        ]
    ]
];
