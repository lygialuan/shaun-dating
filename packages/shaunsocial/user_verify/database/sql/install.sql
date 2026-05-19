INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('User verify requests', 'admin.user_verify.request_manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 23, '');

INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
('User Verification', 2, 'user_verify', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_user_verify');

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('user_verify.enable', 'Enable', 'This setting will affect pages and users.', '0', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_verify'), 0),
('user_verify.support_files', 'Supported file formats', '', 'pdf,jpeg,jpg,png,gif,webp', '', 'text', 2, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_verify'), 0),
('user_verify.badge', 'Verified badge', '', '', '{"default":"images/default/user_verify/badge.svg","style_width":"50","extensions":"svg"}', 'image', 3, 2,  (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_verify'), 0),
('user_verify.user_page_badge', 'Page verified badge', '', '', '{"default":"images/default/user_verify/user_page_badge.svg","style_width":"50","extensions":"svg"}', 'image', 4, 2,  (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_verify'), 0),
('user_verify.unverify_when', 'Users lost their verification status when', '', '1', '{"path":"shaun_user_verify::admin.partial.setting.name.unverify_when"}', 'blade', 5, 2,  (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_verify'), 0);

INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_user_verify', '1.4.0', NOW(), NOW());

INSERT INTO `{prefix}permission_groups` (`name`, `key`, `created_at`, `updated_at`, `package`, `order`) VALUES
('Profile verification', 'user_verify', NOW(), NOW(), 'shaun_user_verify', (SELECT MAX(p.`order`) + 1 FROM `{prefix}permission_groups` as p));

INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Send verification request', 'user_verify.send_request', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'user_verify'), NOW(), NOW(), 'checkbox', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'user_verify.send_request') ,'en', "You don't have profile verification sending request permission.");

INSERT INTO `{prefix}role_permissions` (`role_id`, `permission_id`, `value`) VALUES
(2, (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'user_verify.send_request') , '1');

INSERT INTO `{prefix}mail_templates` (`type`, `subject`, `content`, `vars`, `created_at`, `updated_at`, `name`, `package`) VALUES
('user_verify_reject', '', '', '[reason],[link]', NOW(), NOW(), 'When admin reject verify', 'shaun_user_verify');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('mail_templates', 'subject', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'user_verify_reject'), 'en', '[[site_name]] Verification request update'),
('mail_templates', 'content', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'user_verify_reject'), 'en', '<p>[header]</p><p>Your <a href="[link]">profile</a> verification request has been rejected. Here is comment from admin: [reason]</p><p>[footer]</p>');