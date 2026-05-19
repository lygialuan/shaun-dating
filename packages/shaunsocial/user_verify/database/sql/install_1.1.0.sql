INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_user_verify', '1.1.0', NOW(), NOW());

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('user_verify.user_page_badge', 'Page verified badge', '', '', '{"default":"images/default/user_verify/user_page_badge.svg","style_width":"50","extensions":"svg"}', 'image', 4, 2,  (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'user_verify'), 0);

UPDATE `{prefix}settings` SET `description` = 'This setting will affect pages and users.' WHERE `key` = 'user_verify.enable';