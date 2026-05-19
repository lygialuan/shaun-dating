INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_advertising', '1.1.0', NOW(), NOW());

INSERT INTO `{prefix}wallet_payment_types` (`type`, `class`) VALUES
('buy_advertising', 'Packages\\ShaunSocial\\Advertising\\Wallet\\Payment\\AdvertisingTransactionTypeBuy'),
('refund_advertising', 'Packages\\ShaunSocial\\Advertising\\Wallet\\Payment\\AdvertisingTransactionTypeRefund');

-- INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
-- ('Ads campaign', 2, 'advertising', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_advertising');

-- INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
-- ('shaun_advertising.enable', 'Enable', 'If you disable ads feature, all active ads will stop working, the remaining balance will auto refund to owner of the ads at the ad campaign finish date.', '1', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'advertising'), 0),
-- ('shaun_advertising.vat', 'VAT (%)', '% VAT users must pay when paying for advertising services on your site.', '0', '', 'number', 2, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'advertising'), 0),
-- ('shaun_advertising.daily_budget_minimum', '', 'Minimum daily budget that users can enter when creating ads.', '10', '', 'number', 3, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'advertising'), 0),
-- ('shaun_advertising.amount_per_click', '', 'The amount the system will charge per ONE unique click.', '0.02', '', 'amount', 4, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'advertising'), 0),
-- ('shaun_advertising.amount_per_view', '', 'The amount the system will charge per ONE unique view.', '0.01', '', 'amount', 5, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'advertising'), 0),
-- ('shaun_advertising.feed_number_show', '', '', '3', '', 'amount', 6, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'advertising'), 0);

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('advertisings', 'Packages\\ShaunSocial\\Advertising\\Models\\Advertising');

-- INSERT INTO `{prefix}permission_groups` (`name`, `key`, `created_at`, `updated_at`, `package`, `order`) VALUES
-- ('Ads', 'advertising', NOW(), NOW(), 'shaun_advertising', (SELECT MAX(p.`order`) + 1 FROM `{prefix}permission_groups` as p));

-- INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`, `has_message_error`) VALUES
-- ('Manage Advertisings', 'admin.advertising.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 33, '', 1),
-- ('Show ads', 'advertising.show_ads', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'advertising'), NOW(), NOW(), 'checkbox', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '', 0);

INSERT INTO `{prefix}wallet_transaction_sub_types` (`parent_type_id`, `type`, `class`) VALUES
(4, 'buy_advertising', 'Packages\\ShaunSocial\\Advertising\\Wallet\\Payment\\AdvertisingTransactionTypeBuy'),
(4, 'refund_advertising', 'Packages\\ShaunSocial\\Advertising\\Wallet\\Payment\\AdvertisingTransactionTypeRefund');