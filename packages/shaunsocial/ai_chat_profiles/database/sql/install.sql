INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES
('AI Chat Profiles', 2, 'ai_chat_profiles', (SELECT COALESCE(MAX(p.`order`), 0) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_ai_chat_profiles');

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('ai_chat_profiles.enable', 'Enable', 'If this option is disabled, AI chat profiles cannot be used.', '1', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'ai_chat_profiles'), 0),
('ai_chat_profiles.chat_provider_key_id', 'Providers', 'Select the AI platform to use for AI Chat Profiles. API usage may incur costs.', '0', '{"path":"shaun_ai_chat_profiles::admin.partial.setting.name.provider_key","show_manage_link":false}', 'blade', 2, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'ai_chat_profiles'), 0);
