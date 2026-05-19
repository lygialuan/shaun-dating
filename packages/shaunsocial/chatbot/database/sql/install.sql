INSERT INTO `{prefix}permission_groups` (`name`, `key`, `order`, `package`, `created_at`, `updated_at`) VALUES
('Chatbot', 'chatbot', (SELECT MAX(p.`order`) + 1 FROM `{prefix}permission_groups` as p), 'shaun_chatbot', NOW(), NOW());

INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Maximum number of message can ask the chatbot per day (0 is unlimited)','chatbot.limit_message_per_day',0,(SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'chatbot'), NOW(), NOW(), 'number', 0,(SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` AS p), ''),
('Maximum number of characters that can be sent per message (0 is unlimited)','chatbot.character_max',0,(SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'chatbot'), NOW(), NOW(), 'number', 0,(SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` AS p), ''),
('Allow sending messages', 'chatbot.allow', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'chatbot'), NOW(), NOW(), 'checkbox', 0, (SELECT MIN(p.`order`) - 1 FROM `{prefix}permissions` as p), '');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'chatbot.limit_message_per_day') ,'en', "You can send up to [x] messages per day, please contact admin for more details."),
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'chatbot.character_max') ,'en', "You are allowed to send up to [x] characters per message"),
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'chatbot.allow') ,'en', "You don't have send message permission.");

INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_chatbot', '1.2.0', NOW(), NOW());
