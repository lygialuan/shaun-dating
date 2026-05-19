<?php

return [
    'tasks' => [
        'max_attempts' => 3,
        'backoff_seconds' => [60, 300, 900],
        'cleanup_after_days' => 30,
        'limit' => 5,
    ],
    'moderation' => [
        'system_prompt' => "Analyse the provided content and Return ONLY valid JSON.
                            Do NOT wrap the response in markdown.
                            Do NOT include ```json, ``` or any explanation.
                            The response MUST start with { and end with }. Exp: {\"flagged\": <true|false>, \"reasons\": [\"string\"...], \"summary\": \"short human readable explanation\"}.",
    ],
    'reporting' => [
        'enabled' => true,
        'reporter_user_id' => 0,
        'notify_admins' => true,
    ],
    'provider' => [
        'failure_threshold' => 3,
    ],
];
