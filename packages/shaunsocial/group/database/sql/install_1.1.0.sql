INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Maximum number of groups can create per day (0 is unlimited)', 'group.max_per_day', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'group'), NOW(), NOW(), 'number', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'group.max_per_day') ,'en', "To prevent spam, we only allow you to share a maximum of [x] groups per day.");