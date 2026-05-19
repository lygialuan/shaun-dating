INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Manage AI Providers', 'admin.ai_provider.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, (SELECT COALESCE(MAX(p.`order`), 0) + 1 FROM `{prefix}permissions` AS p), '');

INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_ai_provider', '1.0.0', NOW(), NOW());

INSERT INTO `{prefix}ai_providers` (`name`, `description`, `class`, `created_at`, `updated_at`) VALUES
('OpenAI GPT', 'Versatile GPT models from OpenAI suitable for conversation, coding, and general reasoning tasks.', 'Packages\\ShaunSocial\\AiProvider\\Providers\\OpenAIProvider', NOW(), NOW()),
('Claude AI', 'Anthropic Claude models focused on safe, helpful dialogue with strong writing and reasoning abilities.', 'Packages\\ShaunSocial\\AiProvider\\Providers\\ClaudeProvider', NOW(), NOW()),
('Google Gemini', "Google's Gemini suite with strong multimodal understanding and long-context support.", 'Packages\\ShaunSocial\\AiProvider\\Providers\\GeminiProvider', NOW(), NOW()),
('xAI Grok', 'xAI Grok models offering edgy, up-to-date responses sourced from public data.', 'Packages\\ShaunSocial\\AiProvider\\Providers\\GrokProvider', NOW(), NOW()),
('Perplexity AI', 'Perplexity Sonar models specialized in retrieval-augmented answers with cited references.', 'Packages\\ShaunSocial\\AiProvider\\Providers\\PerplexityProvider', NOW(), NOW());

INSERT INTO `{prefix}ai_provider_keys` (`ai_provider_id`, `name`, `description`, `config`, `is_active`, `status`, `failure_count`, `last_error_message`, `last_error_at`, `created_at`, `updated_at`) VALUES
((SELECT id FROM `{prefix}ai_providers` WHERE class = 'Packages\\ShaunSocial\\AiProvider\\Providers\\OpenAIProvider'), 'Default Key', NULL, '{"api_key":"","model":"gpt-4o-mini","temperature":0.7,"max_tokens":1000,"system_prompt":"You are a helpful AI assistant."}', 1, 'healthy', 0, NULL, NULL, NOW(), NOW()),
((SELECT id FROM `{prefix}ai_providers` WHERE class = 'Packages\\ShaunSocial\\AiProvider\\Providers\\ClaudeProvider'), 'Default Key', NULL, '{"api_key":"","model":"claude-3-sonnet-20240229","temperature":0.7,"max_tokens":1000,"system_prompt":"You are a helpful AI assistant."}', 1, 'healthy', 0, NULL, NULL, NOW(), NOW()),
((SELECT id FROM `{prefix}ai_providers` WHERE class = 'Packages\\ShaunSocial\\AiProvider\\Providers\\GeminiProvider'), 'Default Key', NULL, '{"api_key":"","model":"gemini-1.5-pro","temperature":0.7,"max_output_tokens":1000,"system_prompt":"You are a helpful AI assistant."}', 1, 'healthy', 0, NULL, NULL, NOW(), NOW()),
((SELECT id FROM `{prefix}ai_providers` WHERE class = 'Packages\\ShaunSocial\\AiProvider\\Providers\\GrokProvider'), 'Default Key', NULL, '{"api_key":"","model":"grok-beta","temperature":0.7,"max_tokens":1000,"system_prompt":"You are a helpful AI assistant."}', 1, 'healthy', 0, NULL, NULL, NOW(), NOW()),
((SELECT id FROM `{prefix}ai_providers` WHERE class = 'Packages\\ShaunSocial\\AiProvider\\Providers\\PerplexityProvider'), 'Default Key', NULL, '{"api_key":"","model":"llama-3.1-sonar-small-128k-online","temperature":0.7,"max_tokens":1000,"system_prompt":"You are a helpful AI assistant."}', 1, 'healthy', 0, NULL, NULL, NOW(), NOW());

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('ai_providers', 'Packages\\ShaunSocial\\AiProvider\\Models\\AiProvider');
