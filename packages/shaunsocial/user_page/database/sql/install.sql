INSERT INTO `{prefix}permission_groups` (`name`, `key`, `created_at`, `updated_at`, `package`, `order`) VALUES
('Sub Profiles', 'user_page', NOW(), NOW(), 'shaun_user_page', (SELECT MAX(p.`order`) + 1 FROM `{prefix}permission_groups` as p));

INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Manage Profiles', 'admin.user_page.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 29, ''),
('Page Categories', 'admin.user_page.manage_categories', 0, 1, NOW(), NOW(), 'checkbox', 1, 30, ''),
('Manage create sub profile', 'admin.user_page.manage_create_sub_profile', 0, 1, NOW(), NOW(), 'checkbox', 1, 30, ''),
('Page Verification Requests', 'admin.user_page.manage_verifies', 0, 1, NOW(), NOW(), 'checkbox', 1, 31, ''),
('Profile Feature Packages', 'admin.user_page.manage_feature_packages', 0, 1, NOW(), NOW(), 'checkbox', 1, 32, ''),
('Allow sub-profiles creation', 'user_page.allow_create', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'user_page'), NOW(), NOW(), 'checkbox', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), ''),
('Maximum number of sub-profiles can create per day (0 is unlimited)', 'user_page.max_per_day', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'user_page'), NOW(), NOW(), 'number', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('user_page_categories', 'Packages\\ShaunSocial\\UserPage\\Models\\UserPageCategory'),
('user_page_reviews', 'Packages\\ShaunSocial\\UserPage\\Models\\UserPageReview'),
('user_page_packages', 'Packages\\ShaunSocial\\UserPage\\Models\\UserPagePackage');

INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_user_page', '1.3.0', NOW(), NOW());

INSERT INTO `{prefix}layout_maps` (`router`, `layout_class`) VALUES
('user_pages', 'Packages\\ShaunSocial\\UserPage\\Repositories\\Helpers\\Layout\\PageLayout');

INSERT INTO `{prefix}layout_blocks` (`component`, `title`, `enable`, `class`, `support_header_footer`, `created_at`, `updated_at`, `package`) VALUES
('PageFeature', 'Featured Pages', 1, 'Packages\\ShaunSocial\\UserPage\\Repositories\\Helpers\\Widget\\PageFeatureWidget', 0, NOW(), NOW(), 'shaun_user_page');

INSERT INTO `{prefix}subscription_types` (`type`, `order`, `class`, `show`) VALUES
('page_feature', 1, 'Packages\\ShaunSocial\\UserPage\\Subscription\\SubscriptionTypePageFeature', 'page');

INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
('Sub Profiles', 2, 'user_page', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_user_page');

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('shaun_user_page.enable', 'Enable sub-profiles', "The system will only disable some main features of the sub-profile such as: sub-profile menu, option to switch on main menu and sub-profiles tab in search results sub-profile. Sub Profiles that have been created can still continue to operate normally.", '1', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_page'), 0);
-- ('shaun_user_page.feature_enable', 'Enable featured page', '', '0', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_page'), 0),
-- ('shaun_user_page.feature_remind_day', 'Auto-renew page featured subscription will be notified to users in advance (days)', '', '5', '', 'number', 2, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_page'), 0),
-- ('shaun_user_page.feature_badge', 'Page featured badge', '', '', '{"default":"images/default/user_page/feature_badge.svg","style_width":"50","extensions":"svg"}', 'image', 3, 2,  (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_page'), 0),
-- ('shaun_user_page.feature_image', 'Image on the featured page introduction - Light mode', '', '', '{"default":"images/default/user_page/feature_image.png","style_width":"200"}', 'image', 4, 2,  (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_page'), 0),
-- ('shaun_user_page.feature_image_dark', 'Image on the featured page introduction - Dark mode', '', '', '{"default":"images/default/user_page/feature_image_dark.png","style_width":"200"}', 'image', 5, 2,  (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_page'), 0);

INSERT INTO `{prefix}user_page_categories` (`id`, `name`, `parent_id`, `is_active`, `is_delete`, `order`, `created_at`, `updated_at`) VALUES
(100, 'Actor', 0, 1, 0, 1, '2024-03-22 06:56:03', '2024-03-22 06:56:03'),
(101, 'Artist', 0, 1, 0, 100, '2024-03-22 06:56:10', '2024-03-22 06:56:10'),
(102, 'Sportsperson', 0, 1, 0, 101, '2024-03-22 06:56:17', '2024-03-22 06:56:17'),
(103, 'Blogger', 0, 1, 0, 102, '2024-03-22 06:56:23', '2024-03-22 06:56:23'),
(104, 'Chef', 0, 1, 0, 103, '2024-03-22 06:56:30', '2024-03-22 06:56:30'),
(105, 'Comedian', 0, 1, 0, 104, '2024-03-22 06:56:36', '2024-03-22 06:56:36'),
(106, 'Designer', 0, 1, 0, 105, '2024-03-22 06:56:42', '2024-03-22 06:56:42'),
(107, 'Digital creator', 0, 1, 0, 106, '2024-03-22 06:56:49', '2024-03-22 06:56:49'),
(108, 'Entrepreneur', 0, 1, 0, 107, '2024-03-22 06:56:55', '2024-03-22 06:56:55'),
(109, 'Fashion model', 0, 1, 0, 108, '2024-03-22 06:57:01', '2024-03-22 06:57:01'),
(110, 'Gamer', 0, 1, 0, 109, '2024-03-22 06:57:07', '2024-03-22 06:57:07'),
(111, 'Journalist', 0, 1, 0, 110, '2024-03-22 06:57:13', '2024-03-22 06:57:13'),
(112, 'Musician', 0, 1, 0, 111, '2024-03-22 06:57:18', '2024-03-22 06:57:18'),
(113, 'Photographer', 0, 1, 0, 112, '2024-03-22 06:57:24', '2024-03-22 06:57:24'),
(114, 'Public figure', 0, 1, 0, 113, '2024-03-22 06:57:30', '2024-03-22 06:57:30'),
(115, 'Writer', 0, 1, 0, 114, '2024-03-22 06:57:35', '2024-03-22 06:57:35'),
(116, 'Software', 0, 1, 0, 115, '2024-03-22 06:59:38', '2024-03-22 06:59:38');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('user_page_categories', 'name', 100, 'en', 'Actor'),
('user_page_categories', 'name', 101, 'en', 'Artist'),
('user_page_categories', 'name', 102, 'en', 'Sportsperson'),
('user_page_categories', 'name', 103, 'en', 'Blogger'),
('user_page_categories', 'name', 104, 'en', 'Chef'),
('user_page_categories', 'name', 105, 'en', 'Comedian'),
('user_page_categories', 'name', 106, 'en', 'Designer'),
('user_page_categories', 'name', 107, 'en', 'Digital creator'),
('user_page_categories', 'name', 108, 'en', 'Entrepreneur'),
('user_page_categories', 'name', 109, 'en', 'Fashion model'),
('user_page_categories', 'name', 110, 'en', 'Gamer'),
('user_page_categories', 'name', 111, 'en', 'Journalist'),
('user_page_categories', 'name', 112, 'en', 'Musician'),
('user_page_categories', 'name', 113, 'en', 'Photographer'),
('user_page_categories', 'name', 114, 'en', 'Public figure'),
('user_page_categories', 'name', 115, 'en', 'Writer'),
('user_page_categories', 'name', 116, 'en', 'Software');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'user_page.allow_create') ,'en', "You don't have sub-profiles creation permission."),
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'user_page.max_per_day') ,'en', "To prevent spam, we only allow you to share a maximum of [x] sub-profiles per day.");

INSERT INTO `{prefix}wallet_transaction_sub_types` (`parent_type_id`, `type`, `class`) VALUES
(4, 'user_page_feature_buy', 'Packages\\ShaunSocial\\UserPage\\Wallet\\Payment\\UserPageTransactionTypeBuyFeature');