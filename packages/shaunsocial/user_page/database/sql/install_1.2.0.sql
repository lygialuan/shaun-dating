INSERT INTO `{prefix}wallet_transaction_sub_types` (`parent_type_id`, `type`, `class`) VALUES
(4, 'user_page_feature_buy', 'Packages\\ShaunSocial\\UserPage\\Wallet\\Payment\\UserPageTransactionTypeBuyFeature');

INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Maximum number of sub-profiles can create per day (0 is unlimited)', 'user_page.max_per_day', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'user_page'), NOW(), NOW(), 'number', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'user_page.max_per_day') ,'en', "To prevent spam, we only allow you to share a maximum of [x] sub-profiles per day.");

UPDATE `{prefix}subscription_types` SET `show`='page' WHERE `type` = 'page_feature'