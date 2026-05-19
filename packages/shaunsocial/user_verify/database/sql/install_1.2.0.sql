-- INSERT INTO `{prefix}permission_groups` (`name`, `key`, `created_at`, `updated_at`, `package`, `order`) VALUES
-- ('Profile verification', 'user_verify', NOW(), NOW(), 'shaun_user_verify', (SELECT MAX(p.`order`) + 1 FROM `{prefix}permission_groups` as p));

-- INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
-- ('Send verification request', 'user_verify.send_request', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'user_verify'), NOW(), NOW(), 'checkbox', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

-- INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
-- ('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'user_verify.send_request') ,'en', "You don't have profile verification sending request permission.");