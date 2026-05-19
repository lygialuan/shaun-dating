INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('polls', 'Packages\\ShaunSocial\\Core\\Models\\Poll'),
('content_warning_categories', 'Packages\\ShaunSocial\\Core\\Models\\ContentWarningCategory');

INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
-- ('Allow poll creation', 'post.allow_create_poll', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'post'), NOW(), NOW(), 'checkbox', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), ''),
-- ('Maximum number of options that can be created for a poll', 'post.max_poll_item', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'post'), NOW(), NOW(), 'number', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), ''),
-- ('Maximum number of days allowed to be selected when creating a poll', 'post.max_close_day', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'post'), NOW(), NOW(), 'number', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), ''),
('Content Warning Categories', 'admin.content_warning.manage_category', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'admin'), NOW(), NOW(), 'checkbox', 1, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
-- ('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'post.allow_create_poll') ,'en', "You don't have poll creation permission."),
-- ('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'post.max_poll_item') ,'en', 'You only can share a poll that has max [x] option(s).'),
-- ('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'post.max_close_day') ,'en', 'You only can share a poll that has max [x] day(s) length.'),
('content_warning_categories', 'name', 1, 'en', 'Sensitive'),
('content_warning_categories', 'name', 2, 'en', 'Violence'),
('content_warning_categories', 'name', 3, 'en', 'Nudity'),
('content_warning_categories', 'name', 4, 'en', 'Other');

INSERT INTO `{prefix}content_warning_categories` (`id`, `name`, `is_active`, `is_delete`, `order`, `created_at`, `updated_at`) VALUES
(1, 'Sensitive', 1, 0, 1, NOW(), NOW()),
(2, 'Violence', 1, 0, 2, NOW(), NOW()),
(3, 'Nudity', 1, 0, 3, NOW(), NOW()),
(4, 'Other', 1, 0, 4, NOW(), NOW());

INSERT INTO `{prefix}layout_maps` (`router`, `layout_class`) VALUES
('documents', 'Packages\\ShaunSocial\\Core\\Repositories\\Helpers\\Layout\\DocumentsLayout');

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('feature.comment_photo_max', 'Write a comment/reply -  maximum photos can be attached.', 'Maximum photos that member can attach to a comment (0 or empty setting is unlimited).', '10', '', 'number', 18, 2, 4, 0),
-- ('feature.hashtag_auto_approve', 'Auto approve new hashtag.', '', '1', '', 'checkbox', 8, 2, 4, 0),
('site.appearance_mode_default', 'Appearance mode default', 'Set appearance mode default for all users after signup.', 'off', '{"off":"Light","on":"Dark", "auto": "System"}', 'radio', 14, 1, 1, 0),
('feature.age_restriction', 'Age restriction', "Minimum age. If the 'Date of birth' field is not required or value of this setting is empty, age restriction will consider as disabled.", '0', '', 'number', 8, 2, 5, 0),
('feature.require_birth', 'Required date of birth', 'The option will apply for both web and mobile apps. If enabled, this field will show in sign up but will show on in edit profile page as option field. If age restriction is enabled, please enable this option.', '0', '', 'checkbox', 9, 2, 5, 0),
('feature.require_location', 'Required location', 'This option will apply for both web and mobile apps. If enabled, this field will show in sign up process and edit profile page as required field. If disabled, the field will not show in sign up but will show on edit profile page as optional field.', '0', '', 'checkbox', 10, 2, 5, 0),
('feature.require_gender', 'Required gender', 'This option will apply for both web and mobile apps. If enabled, this field will show in sign up process and edit profile page as required field. If disabled, the field will not show in sign up but will show in edit profile page as optional field.', '0', '', 'checkbox', 11, 2, 5, 0);

INSERT INTO `{prefix}storage_services` (`name`, `key`, `config`, `is_default`, `extra`) VALUES
('Digitalocean', 'do', '{"key":"","secret":"","bucket":"","endpoint":"","url":""}', 0, '');