INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_user_subscription', '1.2.0', NOW(), NOW());

INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Manage Packages', 'admin.user_subscription.manage_package', 0, 1, NOW(), NOW(), 'checkbox', 1, 34, ''),
('Customize Pricing Plans', 'admin.user_subscription.pricing_table', 0, 1, NOW(), NOW(), 'checkbox', 1, 35, '');

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('user_subscription_package', 'Packages\\ShaunSocial\\UserSubscription\\Models\\UserSubscriptionPackage'),
('user_subscriptions', 'Packages\\ShaunSocial\\UserSubscription\\Models\\UserSubscription'),
('user_subscription_plan', 'Packages\\ShaunSocial\\UserSubscription\\Models\\UserSubscriptionPlan');

INSERT INTO `{prefix}subscription_types` (`type`, `order`, `class`) VALUES
('user_subscription', 1, 'Packages\\ShaunSocial\\UserSubscription\\Subscription\\SubscriptionTypeUserSubscription');

INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
('Subscription', 2, 'user_subscription', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_user_subscription');

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('shaun_user_subscription.enable', 'Enable', 'If subscription is disabled, members will not be able to subscribe but active subscribers will work normally.', '1', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_subscription'), 0),
('shaun_user_subscription.remind_day', 'Auto-renew membership subscription will be notified to users in advance (days)', '', '5', '', 'number', 2, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_subscription'), 0);

INSERT INTO `{prefix}mail_templates` (`type`, `subject`, `content`, `vars`, `created_at`, `updated_at`, `name`, `package`) VALUES
('user_subscription_active', '', '', '[link]', NOW(), NOW(), 'When a user buys subscription successfully', 'shaun_user_subscription'),
('user_subscription_remind', '', '', '[link],[date]', NOW(), NOW(), 'When reminder subscription', 'shaun_user_subscription'),
('user_subscription_stop', '', '', '[link]', NOW(), NOW(), 'When stop subscription', 'shaun_user_subscription');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('mail_templates', 'subject', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'user_subscription_active'), 'en', 'Your subscription to [site_name] was successfully activated'),
('mail_templates', 'content', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'user_subscription_active'), 'en', '<p>[header]</p><p>You’ve been subscribed. If you’d like to change your subscription, you can do by clicking  the link below.</p><p><a href="[link]">Manage my subscriptions</a></p><p>[footer]</p>'),
('mail_templates', 'subject', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'user_subscription_remind'), 'en', 'Your upcoming subscription renewal on [site_name]'),
('mail_templates', 'content', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'user_subscription_remind'), 'en', '<p>[header]</p><p>This is just a friendly reminder that your subscription on [site_name] will renew automatically on [date].</p><p>You can manage your subscriptions by clicking on the link below</p><p><a href="[link]">Manage my subscriptions</a></p><p>[footer]</p>'),
('mail_templates', 'subject', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'user_subscription_stop'), 'en', 'Your subscription on [site_name] has been stopped'),
('mail_templates', 'content', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'user_subscription_stop'), 'en', '<p>[header]</p><p>You’ve been unsubscribed. If you’d like to change your subscription, you can do by clicking  the link below</p><p><a href="[link]">Manage my subscriptions</a></p><p>[footer]</p>');

INSERT INTO `{prefix}wallet_transaction_sub_types` (`parent_type_id`, `type`, `class`) VALUES
(4, 'user_subscription_buy', 'Packages\\ShaunSocial\\UserSubscription\\Wallet\\Payment\\UserSubscriptionTransactionTypeBuy');