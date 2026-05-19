-- INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
-- ('Maximum number of vibbs can create per day (0 is unlimited)', 'vibb.max_per_day', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'vibb'), NOW(), NOW(), 'number', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

-- INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
-- ('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'vibb.max_per_day') ,'en', "To prevent spam, we only allow you to share a maximum of [x] vibbs per day.");