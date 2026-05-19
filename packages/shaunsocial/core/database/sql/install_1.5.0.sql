INSERT INTO `{prefix}two_factor_providers` (`id`, `name`, `description` ,`config`, `type`, `is_active`) VALUES
(1, 'Authentication App', 'Get codes from an app like Authy, 1Password, Microsoft, Authenticator, or Google Authenticator.', '', 'auth_app', 0),
(2, 'Sms','Receive a unique code via sms', '','sms', 0),
(3, 'Mail','Receive a unique code via mail', '','mail', 1);

INSERT INTO `{prefix}mail_templates` (`type`, `subject`, `content`, `vars`, `created_at`, `updated_at`, `name`) VALUES
('two_factory_send_code', '', '', '[code]', NOW(), NOW(), 'Two-factor verify email');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('two_factor_providers','name',1,'en','Authentication App'),
('two_factor_providers','description',1,'en','Get codes from an app like Authy, 1Password, Microsoft, Authenticator, or Google Authenticator.'),
('two_factor_providers','name',2,'en','Sms'),
('two_factor_providers','description',2,'en','Receive a unique code via sms.'),
('two_factor_providers','name',3,'en','Mail'),
('two_factor_providers','description',3,'en','Receive a unique code via mail.'),
('mail_templates', 'subject', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'two_factory_send_code'), 'en', '[[site_name]] Email Verification'),
('mail_templates', 'content', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'two_factory_send_code'), 'en', '<p>[header]</p><p>To verify your email address and continue, please enter code:</p><p><strong>[code]</strong></p><p>The verification code will expire in 5 minutes.</p><p>[footer]</p>');

INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`)  VALUES 
('Sitemap settings', '2', 'sitemap', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p));

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('feature.enable_two_factor', 'Two-factor authentication (2FA)', "Two-factor authentication (2FA) provides an additional layer of security beyond passwords and is strongly recommended. Your member's account is protected by requiring both your password and an authentication code from authenticated email.", '0', '', 'checkbox', 12, 2, 5, 0),
('site.autoplay_video_default', 'Video autoplay default', 'If enabled, videos will autoplay by default. Members can turn it off in Profile -> Settings -> Display', '1', '', 'checkbox', 13, 2, 5, 0),
-- ('post.enable_gifs', 'Enable Tenor Gifs', 'Allow gif at comment, reply and post', '1', '', 'checkbox', 4, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'post'), 0),
('feature.video_support_files', 'Allowed video file extensions', "Defines a list of video file extensions that members are allowed to upload. Separate extensions with commas. Empty means that you're allowed the following extensions mp4,wmv,3gp,mov,avi.", 'mp4,wmv,3gp,mov,avi', '', 'text', 4, 2, 8, 0),
('sitemap.enable', 'Enable', '', '1', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'sitemap'), 0),
('sitemap.schedule', 'Schedule updates', '', 'daily', '{"daily":"Daily", "weekly":"Weekly","monthly": "Monthly"}', 'select', 2, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'sitemap'), 0);

INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Manage Two-Factor Providers', 'admin.two_factor_provider.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 26, ''),
('Manage Link Icons', 'admin.link_icon.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 27, '');

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('two_factor_providers', 'Packages\\ShaunSocial\\Core\\Models\\TwoFactorProvider');

INSERT INTO `{prefix}link_icons` (`domain`, `default`, `icon_file_id`, `is_active`) VALUES
('www.facebook.com', 'images/default/link_icon/facebook.svg', 0, true),
('www.linkedin.com', 'images/default/link_icon/linkedin.svg', 0, true),
('x.com', 'images/default/link_icon/x.svg', 0, true),
('www.tiktok.com', 'images/default/link_icon/tiktok.svg', 0, true),
('www.youtube.com', 'images/default/link_icon/youtube.svg', 0, true);

INSERT INTO `{prefix}storage_services` (`name`, `key`, `config`, `is_default`, `extra`) VALUES
('Cloudflare R2', 'r2', '{"key":"","secret":"","bucket":"","endpoint":"","url":""}', 0, ''),
('Backblaze', 'backblaze', '{"key":"","secret":"","bucket":"","endpoint":"","url":""}', 0, '');

DELETE FROM `{prefix}settings` WHERE `key`='site.favicon_image';

UPDATE `{prefix}permissions` SET `group_id` = (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'post') WHERE `key` = 'post.max_per_day';
UPDATE `{prefix}permissions` SET `group_id` = (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'post') WHERE `key` = 'post.comment_max_per_day';