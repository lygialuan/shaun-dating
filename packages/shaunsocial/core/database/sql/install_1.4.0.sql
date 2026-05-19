-- INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
-- ('Landing page settings', 2, 'landing', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_core');

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('spam.capcha_type', 'Captcha Type', '', 'recaptcha', '{"recaptcha":"reCAPTCHA", "turnstile":"Cloudflare Turnstile"}', 'select', 3, 2, 7, 0),
('spam.turnstile_site_key', 'Turnstile site key', 'You can obtain API credentials at: <a href="https://blog.cloudflare.com/turnstile-private-captcha-alternative">https://blog.cloudflare.com/turnstile-private-captcha-alternative</a>.', '', '', 'text', 5, 2, 7, 0),
('spam.turnstile_secret_key', 'Turnstile secret key', 'You can obtain API credentials at: <a href="https://blog.cloudflare.com/turnstile-private-captcha-alternative">https://blog.cloudflare.com/turnstile-private-captcha-alternative</a>.', '', '', 'text', 5, 2, 7, 0),
('chat.enable_bubble_chat', 'Enable Bottom Bubble Chat', "If this option is disable, users can't not see bottom bubble chat.", '1', '', 'checkbox', 4, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'chat'), 0),
('site.header_mobile_logo_mobile', 'Header logo on mobile', 'We recommend using an image with a resolution of 200x40 for the header logo on mobile.', '', '{"default":"images/default/logo.png","style_width":"200"}', 'image', 4, 1, 3, 0),
('site.header_mobile_logo_mobile_darkmode', 'Header logo on mobile Dark mode', 'We recommend using an image with a resolution of 200x40 for the header logo on mobile darkmode.', '', '{"default":"images/default/logo_darkmode.png","style_width":"200"}', 'image', 22, 1, 3, 0),
-- ('landing.type', '', '', '2', '{"0":"Default landing page", "1":"Custom landing page", "2":"Landing page dating"}', 'radio', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'landing'), 0),
('feature.phone_verify', 'Phone verification signup step', 'Add an extra step to ask member to verify their phone number before using the platform.', '0', '', 'checkbox', 3, 2, 5, 0),
('spam.send_phone_enable_recapcha', 'Enable reCAPTCHA in send sms', '', '0', '', 'checkbox', 11, 2, 7, 0);

UPDATE `{prefix}settings` SET `description` = 'Enable captcha in signup form' WHERE `key` = 'spam.signup_enable_recapcha';
UPDATE `{prefix}settings` SET `description` = 'Enable captcha in login form' WHERE `key` = 'spam.login_enable_recapcha';
UPDATE `{prefix}settings` SET `description` = 'Enable captcha in contact form' WHERE `key` = 'spam.contact_enable_recapcha';
UPDATE `{prefix}settings` SET `description` = 'Enable captcha in share email' WHERE `key` = 'spam.share_email_enable_recapcha';
UPDATE `{prefix}settings` SET `description` = 'Enable captcha in invite email' WHERE `key` = 'spam.invite_email_enable_recapcha';

-- UPDATE `{prefix}layout_pages` SET `title` = 'Default Landing Page' WHERE `router` = 'landing_page.index';
-- UPDATE `{prefix}translations` SET `value` = 'Default Landing Page' WHERE `table_name` = 'layout_pages' AND `column_name` = 'title' AND `foreign_key` = (SELECT `id` FROM `{prefix}layout_pages` WHERE `router` = 'landing_page.index');

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('audios', 'Packages\\ShaunSocial\\Core\\Models\\Audio');

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('feature.audio_max_duration', 'Audio duration', 'Set the max allowed seconds for the audio (0 means no limit).', '120', '', 'number', 3, 2, 8, 0),
('feature.invite_only', 'Invitation only sign up mode', 'Allows you to switch sites and apps to invitation only sign up mode. If enabled, to sign up a new account, users need to do via a link sent by an existing user inside the system or invitation code.', '0', '', 'checkbox', 4, 2, 5, 0);

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('subscriptions', 'Packages\\ShaunSocial\\Core\\Models\\Subscription');

-- INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
-- ('Maximum number of post (not include Vibb) can share per day (0 is unlimited)', 'post.max_per_day', 0, 2, NOW(), NOW(), 'number', 0, 6, ''),
-- ('Maximum number comments (include replies) can share per day (0 is unlimited)', 'post.comment_max_per_day', 0, 2, NOW(), NOW(), 'number', 0, 7, '');

-- INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
-- ('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'post.max_per_day') ,'en', "To prevent spam, we only allow you to share a maximum of [x] posts per day."),
-- ('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'post.comment_max_per_day') ,'en', "To prevent spam, we only allow you to share a maximum of [x] comments(repiles) per day.");

INSERT INTO `{prefix}sms_providers` (`id`, `name`, `class`, `config`, `type`, `is_default`) VALUES
(1, 'Twilio', 'Packages\\ShaunSocial\\Core\\Library\\Sms\\Twilio', '' ,'twilio', 1),
(2, 'Clickatell', 'Packages\\ShaunSocial\\Core\\Library\\Sms\\Clickatell', '' ,'clickatell', 0);