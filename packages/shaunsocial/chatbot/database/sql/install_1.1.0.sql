
INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Allow sending messages', 'chatbot.allow', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'chatbot'), NOW(), NOW(), 'checkbox', 0, (SELECT MIN(p.`order`) - 1 FROM `{prefix}permissions` as p), '');
UPDATE `{prefix}permissions` SET `name` = 'Manage AI Chatbot' WHERE `key` = 'admin.chatbot.manage';
INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'chatbot.allow') ,'en', "You don't have send message permission.");