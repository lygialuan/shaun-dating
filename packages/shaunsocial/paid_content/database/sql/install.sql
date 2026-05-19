INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_paid_content', '1.1.0', NOW(), NOW());

-- INSERT INTO `{prefix}permission_groups` (`name`, `key`, `created_at`, `updated_at`, `package`, `order`) VALUES
-- ('Paid content', 'paid_content', NOW(), NOW(), 'shaun_paid_content', (SELECT MAX(p.`order`) + 1 FROM `{prefix}permission_groups` as p));

-- INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
-- ('Paid content subscription packages', 'admin.paid_content.packages_manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 26, ''),
-- ('Paid content tips packages', 'admin.paid_content.tips_manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 27, ''),
-- ('Allow paid content creation', 'paid_content.allow_create', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'paid_content'), NOW(), NOW(), 'checkbox', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

-- INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
-- ('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'paid_content.allow_create') ,'en', "Your current membership does not have paid content creation permission. Please contact admin to upgrade.");

-- INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
-- ('Paid content', 2, 'paid_content', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_paid_content');

-- INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
-- ('shaun_paid_content.enable', 'Enable', 'If disabled, content creators cannot paid content, members cannot subscribe to content creator’s profile, and all paid posts will be hidden.', '1', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'paid_content'), 0),
-- ('shaun_paid_content.require_verify', 'Account verification required before enabling paid content monetization', 'Make sure you enable account verification before you enable this option.', '0', '', 'checkbox', 2, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'paid_content'), 0),
-- ('shaun_paid_content.commission_fee', 'Platform fee (%)', 'if you enter 20%, it means that the platform will receive 20% and the user will receive 80%.', '20', '', 'number', 3, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'paid_content'), 0),
-- ('shaun_paid_content.commission_referral', 'Commission for referral (%)', 'If a member shares a paid post to friends and their friends subscribe or pay to unlock, the member will earn commission based on this setting. Referral commission will be shared from platform fee. For profile subscription, the referral commission only send at the First billing cycle.', '20', '', 'number', 4, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'paid_content'), 0),
-- ('shaun_paid_content.user_create', 'Users can create paid content', '', '1', '', 'checkbox', 5, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'paid_content'), 0),
-- ('shaun_paid_content.page_create', 'Pages can create paid content', '', '1', '', 'checkbox', 6, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'paid_content'), 0),
-- ('shaun_paid_content.user_subscriber_remind_day', 'Auto-renew profile subscriber subscription will be notified to users in advance (days)', '', '1', '', 'text', 7, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'paid_content'), 0),
-- ('shaun_paid_content.thumb_default', 'Paid content default blurred thumbnail', "If Imagick extension is not enable and members don't upload thumb photo, system will show this purred image for paid content. We recommend using an image with a resolution of 1024x1024 for the default paid content thumbnail.", '', '{"default":"images/default/paid_content.png","style_width":"200"}', 'image', 8, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'paid_content'), 0);

-- INSERT INTO `{prefix}subscription_types` (`type`, `order`, `class`) VALUES
-- ('user_subscriber', 1, 'Packages\\ShaunSocial\\PaidContent\\Subscription\\SubscriptionTypeUserSubscriber');

INSERT INTO `{prefix}wallet_transaction_types` (`type`,`order`, `is_root`, `class`) VALUES
-- ('paid_content', 8, 0, 'Packages\\ShaunSocial\\PaidContent\\Wallet\\WalletTransactionTypePaidContent'),
('root_buy_post_fee', 9, 1, 'Packages\\ShaunSocial\\PaidContent\\Wallet\\WalletTransactionTypeRootBuyPostFee'),
('root_subscriber_fee', 10, 1, 'Packages\\ShaunSocial\\PaidContent\\Wallet\\WalletTransactionTypeRootSubscriberFee');

INSERT INTO `{prefix}wallet_transaction_sub_types` (`parent_type_id`, `type`, `class`) VALUES
-- ((SELECT `id` FROM `{prefix}wallet_transaction_types` WHERE `type` = 'paid_content'), 'paid_content_buy_post', 'Packages\\ShaunSocial\\PaidContent\\Wallet\\PaidContent\\PaidContentTransactionTypeBuyPost'),
-- ((SELECT `id` FROM `{prefix}wallet_transaction_types` WHERE `type` = 'paid_content'), 'paid_content_user_subscriber', 'Packages\\ShaunSocial\\PaidContent\\Wallet\\PaidContent\\PaidContentTransactionTypeUserSubscriber'),
-- ((SELECT `id` FROM `{prefix}wallet_transaction_types` WHERE `type` = 'commission'), 'paid_content_buy_post', 'Packages\\ShaunSocial\\PaidContent\\Wallet\\Commission\\PaidContentTransactionTypeBuyPost'),
-- ((SELECT `id` FROM `{prefix}wallet_transaction_types` WHERE `type` = 'commission'), 'paid_content_user_subscriber', 'Packages\\ShaunSocial\\PaidContent\\Wallet\\Commission\\PaidContentTransactionTypeUserSubscriber'),
((SELECT `id` FROM `{prefix}wallet_transaction_types` WHERE `type` = 'payment'), 'root_buy_post_fee', 'Packages\\ShaunSocial\\PaidContent\\Wallet\\Payment\\PaidContentTransactionTypeRootBuyPostFee'),
((SELECT `id` FROM `{prefix}wallet_transaction_types` WHERE `type` = 'payment'), 'root_subscriber_fee', 'Packages\\ShaunSocial\\PaidContent\\Wallet\\Payment\\PaidContentTransactionTypeRootSubscriberFee'),
((SELECT `id` FROM `{prefix}wallet_transaction_types` WHERE `type` = 'payment'), 'tip', 'Packages\\ShaunSocial\\PaidContent\\Wallet\\Payment\\PaidContentTransactionTypeTip');

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('subscriber_packages', 'Packages\\ShaunSocial\\PaidContent\\Models\\SubscriberPackage'),
('user_post_paid_orders', 'Packages\\ShaunSocial\\PaidContent\\Models\\UserPostPaidOrder');