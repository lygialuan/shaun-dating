INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Manage AI Feature Tasks', 'admin.ai_features.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, (SELECT COALESCE(MAX(p.`order`), 0) + 1 FROM `{prefix}permissions` AS p), '');

-- INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES
-- ('AI Features', 2, 'ai_features', (SELECT COALESCE(MAX(p.`order`), 0) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_ai_features');

-- INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
-- ('ai_features.chatbot_enable', 'ChatBot', "If this option is disable, member can't access chatbot menu.", '0', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'ai_features'), 0),
-- ('ai_features.chatbot_provider_key_id', 'Select AI Platform do you want to use for AI Chat Bot', 'List of AI Platforms can be configure here. API call fee will be applied, please check pricing of the selected AI platform carefully.', '0', '{"path":"shaun_ai_features::admin.partial.setting.name.provider_key","show_manage_link":false}', 'blade', 2, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'ai_features'), 0),
-- ('ai_features.detect_text', 'Auto detect inappropriate text in a post, comment and reply', 'Detect and flag inappropriate text (hate speech, violence, etc...)', '0', '', 'checkbox', 3, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'ai_features'), 0),
-- ('ai_features.text_provider_key_id', 'Select AI Platform do you want to use for detecting inappropriate text', 'List of AI Platforms can be configure here. API call fee will be applied, please check pricing of the selected AI platform carefully.', '0', '{"path":"shaun_ai_features::admin.partial.setting.name.provider_key","show_manage_link":false}', 'blade', 4, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'ai_features'), 0),
-- ('ai_features.detect_image', 'Auto detect inappropriate image in a post, comment and reply', 'Detect and flag inappropriate image (adult, sensitive....)', '0', '', 'checkbox', 5, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'ai_features'), 0),
-- ('ai_features.image_provider_key_id', 'Select AI Platform do you want to use for detecting inappropriate image', 'List of AI Platforms can be configure here. API call fee will be applied, please check pricing of the selected AI platform carefully.', '0', '{"path":"shaun_ai_features::admin.partial.setting.name.provider_key","show_manage_link":false}', 'blade', 6, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'ai_features'), 0),
-- ('ai_features.detect_video', 'Auto detect inappropriate video in a post or vibb (reel)', 'Detect and flag inappropriate video (adult, sensitive....)', '0', '', 'checkbox', 7, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'ai_features'), 0),
-- ('ai_features.video_provider_key_id', 'Select AI Platform do you want to use for detecting inappropriate video', 'List of AI Platforms can be configure here. API call fee will be applied, please check pricing of the selected AI platform carefully.', '0', '{"path":"shaun_ai_features::admin.partial.setting.name.provider_key","show_manage_link":false}', 'blade', 8, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'ai_features'), 0),
-- ('ai_features.auto_delete_inappropriate_content', 'Auto delete inappropriate content', 'If AI found inappropriate content in a POST, it will automatically delete the POST and send member an notification', '0', '', 'checkbox', 9, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'ai_features'), 0);

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('ai_feature_tasks', 'Packages\\ShaunSocial\\AiFeatures\\Models\\AiFeatureTask');

INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_ai_features', '1.0.0', NOW(), NOW());
