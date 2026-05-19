UPDATE `{prefix}permissions` SET `name` = 'Manage Subscriptions' WHERE `key` = 'admin.subscription.manage';

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('content_translate.enable', 'Enable', 'Enable auto translate for post, comment, reply and message', '0', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'content_translate'), 0);

INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Manage Translate Providers', 'admin.translate_provider.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 28, '');

INSERT INTO `{prefix}translate_providers` (`id`, `name`, `class`, `config`, `type`, `is_default`) VALUES
(1, 'Google', 'Packages\\ShaunSocial\\Core\\Library\\Translate\\Google', '' ,'google', 1);