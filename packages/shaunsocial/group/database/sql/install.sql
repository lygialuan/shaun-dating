INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_group', '1.2.0', NOW(), NOW());

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('group_categories', 'Packages\\ShaunSocial\\Group\\Models\\GroupCategory'),
('groups', 'Packages\\ShaunSocial\\Group\\Models\\Group');

-- INSERT INTO `{prefix}permission_groups` (`name`, `key`, `created_at`, `updated_at`, `package`, `order`) VALUES
-- ('Group', 'group', NOW(), NOW(), 'shaun_group', (SELECT MAX(p.`order`) + 1 FROM `{prefix}permission_groups` as p));

-- INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
-- ('Manage Groups', 'admin.group.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 36, ''),
-- ('Group Categories', 'admin.group.manage_categories', 0, 1, NOW(), NOW(), 'checkbox', 1, 37, ''),
-- ('Allow group creation', 'group.allow_create', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'group'), NOW(), NOW(), 'checkbox', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), ''),
-- ('Maximum number of groups can create per day (0 is unlimited)', 'group.max_per_day', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'group'), NOW(), NOW(), 'number', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');


INSERT INTO `{prefix}group_categories` (`id`, `name`, `parent_id`, `is_active`, `is_delete`, `order`, `created_at`, `updated_at`) VALUES
(100, 'Sports', 0, 1, 0, 99, '2024-03-22 06:56:03', '2024-03-22 06:56:03'),
(101, 'Technology', 0, 1, 0, 100, '2024-03-22 06:56:10', '2024-03-22 06:56:10'),
(102, 'Art', 0, 1, 0, 101, '2024-03-22 06:56:17', '2024-03-22 06:56:17'),
(103, 'Entertainment', 0, 1, 0, 102, '2024-03-22 06:56:23', '2024-03-22 06:56:23'),
(104, 'Gaming', 0, 1, 0, 103, '2024-03-22 06:56:30', '2024-03-22 06:56:30'),
(105, 'Politics', 0, 1, 0, 104, '2024-03-22 06:56:36', '2024-03-22 06:56:36'),
(106, 'Business', 0, 1, 0, 105, '2024-03-22 06:56:42', '2024-03-22 06:56:42'),
(107, 'Culture', 0, 1, 0, 106, '2024-03-22 06:56:49', '2024-03-22 06:56:49'),
(108, 'Science', 0, 1, 0, 107, '2024-03-22 06:56:55', '2024-03-22 06:56:55'),
(109, 'Food', 0, 1, 0, 108, '2024-03-22 06:57:01', '2024-03-22 06:57:01'),
(110, 'Animals', 0, 1, 0, 109, '2024-03-22 06:57:07', '2024-03-22 06:57:07'),
(111, 'Education', 0, 1, 0, 110, '2024-03-22 06:57:13', '2024-03-22 06:57:13'),
(112, 'Fashion & Beauty', 0, 1, 0, 111, '2024-03-22 06:57:18', '2024-03-22 06:57:18'),
(113, 'Health & Fitness', 0, 1, 0, 112, '2024-03-22 06:57:24', '2024-03-22 06:57:24'),
(114, 'News', 0, 1, 0, 113, '2024-03-22 06:57:30', '2024-03-22 06:57:30'),
(115, 'Cryptocurrency', 0, 1, 0, 114, '2024-03-22 06:57:35', '2024-03-22 06:57:35'),
(116, 'Travel', 0, 1, 0, 115, '2024-03-22 06:59:38', '2024-03-22 06:59:38');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('group_categories', 'name', 100, 'en', 'Sports'),
('group_categories', 'name', 101, 'en', 'Technology'),
('group_categories', 'name', 102, 'en', 'Art'),
('group_categories', 'name', 103, 'en', 'Entertainment'),
('group_categories', 'name', 104, 'en', 'Gaming'),
('group_categories', 'name', 105, 'en', 'Politics'),
('group_categories', 'name', 106, 'en', 'Business'),
('group_categories', 'name', 107, 'en', 'Culture'),
('group_categories', 'name', 108, 'en', 'Science'),
('group_categories', 'name', 109, 'en', 'Food'),
('group_categories', 'name', 110, 'en', 'Animals'),
('group_categories', 'name', 111, 'en', 'Education'),
('group_categories', 'name', 112, 'en', 'Fashion & Beauty'),
('group_categories', 'name', 113, 'en', 'Health & Fitness'),
('group_categories', 'name', 114, 'en', 'News'),
('group_categories', 'name', 115, 'en', 'Cryptocurrency'),
('group_categories', 'name', 116, 'en', 'Travel');

-- INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
-- ('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'group.allow_create') ,'en', "You don't have group creation permission."),
-- ('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'group.max_per_day') ,'en', "To prevent spam, we only allow you to share a maximum of [x] groups per day.");

-- INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
-- ('Group', 2, 'group', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_group');

-- INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
-- ('shaun_group.enable', 'Enable', "If this option is disable, member can't access group menu and all existing groups.", '1', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'group'), 0),
-- ('shaun_group.auto_approve', 'Auto approve', '', '1', '', 'checkbox', 2, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'group'), 0),
-- ('shaun_group.cover_default', 'Cover group default image', 'We recommend using an image with a resolution of 1024x395 for the default group cover image.', '', '{"default":"images/default/cover_group.png","style_width":"200"}', 'image', 3, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'group'), 0);

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('groups', 'Packages\\ShaunSocial\\Group\\Models\\Group'),
('group_post_pendings', 'Packages\\ShaunSocial\\Group\\Models\\GroupPostPending'),
('group_member_requests', 'Packages\\ShaunSocial\\Group\\Models\\GroupMemberRequest');

-- INSERT INTO `{prefix}layout_maps` (`router`, `layout_class`) VALUES
-- ('groups', 'Packages\\ShaunSocial\\Group\\Repositories\\Helpers\\Layout\\GroupLayout'),
-- ('group_profile', 'Packages\\ShaunSocial\\Group\\Repositories\\Helpers\\Layout\\GroupProfileLayout');

-- INSERT INTO `{prefix}layout_blocks` (`component`, `title`, `enable`, `class`, `support_header_footer`, `created_at`, `updated_at`, `package`) VALUES
-- ('GroupNew', 'New Groups', 1, 'Packages\\ShaunSocial\\Group\\Repositories\\Helpers\\Widget\\GroupNewWidget', 0, NOW(), NOW(), 'shaun_group'),
-- ('GroupPopular', 'Popular Groups', 1, 'Packages\\ShaunSocial\\Group\\Repositories\\Helpers\\Widget\\GroupPopularWidget', 0, NOW(), NOW(), 'shaun_group');