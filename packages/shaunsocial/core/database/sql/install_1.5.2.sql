-- INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
-- ('Watermark', 2, 'watermark', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_core');

-- INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
-- ('watermark.enable', 'Enable', "Auto insert text watermark into the photos in post. If enable, the watermark only apply for the newly added photos", '0', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'watermark'), 0),
-- ('watermark.text', 'Text', 'If left blank, the system will automatically use the domain name and username for the text watermark. Ex: domainame.com/@abc', '', '', 'text', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'watermark'), 0),
-- ('watermark.position', 'Position', '', 'center', '{"top_left":"Top Left", "top_center": "Top Center", "top_right": "Top Right", "middle_left": "Middle Left", "center":"Center", "middle_right": "Middle Right", "bottom_left": "Bottom Left", "bottom_center": "Bottom Center", "bottom_right": "Bottom Right"}', 'select', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'watermark'), 0),
-- ('watermark.text_color', 'Text color', '', '#ffffff', '', 'color', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'watermark'), 0),
-- ('watermark.text_size', 'Text font size (px)', '', '24', '', 'text', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'watermark'), 0),
-- ('watermark.text_style', 'Text style', '', 'regular', '{"regular": "Regular", "medium": "Medium", "semibold": "Semibold", "bold": "Bold"}', 'select', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'watermark'), 0);

INSERT INTO `{prefix}menus` (`name`, `alias`, `support_icon`, `support_child`) VALUES 
('Profile menu', 'profile', 1, 1);

UPDATE `{prefix}menu_items` SET `menu_id` = (SELECT `id` FROM `{prefix}menus` WHERE `alias` = 'profile') WHERE `menu_id` = 1 AND `alias` IN ('list', 'bookmark', 'setting', 'invite', 'logout');

UPDATE `{prefix}settings` SET `name` = "User's default appearance mode after sign up" WHERE `key` = 'site.appearance_mode_default';
UPDATE `{prefix}settings` SET `description` = 'If you set dark as default for example, the dark mode will auto set default for all new sign up users.' WHERE `key` = 'site.appearance_mode_default';
UPDATE `{prefix}settings` SET `name` = "User's default video auto play mode after sign up" WHERE `key` = 'site.autoplay_video_default';

INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
('Progressive Web App', 2, 'pwa', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_core');

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('pwa.enable', 'Enable', "", '0', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'pwa'), 0),
('pwa.icon', 'Icon', "", '', '{"default":"images/default/pwa/icon.png"}', 'image', 2, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'pwa'), 0),
('pwa.json', 'Firebase json', "", '', '', 'textarea', 3, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'pwa'), 0),
('pwa.key_pair', 'Firebase key pair', "", '', '', 'text', 4, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'pwa'), 0);

INSERT INTO `{prefix}report_categories` (`name`, `is_active`, `order`, `created_at`, `updated_at`, `is_ai`) VALUES
('Report from Ai', '1', '1', NOW(), NOW(), '1');

UPDATE `{prefix}permissions` SET `name` = "Manage Users" WHERE `key` = 'admin.user.manage';
UPDATE `{prefix}permissions` SET `name` = "Manage Link Profile Icons" WHERE `key` = 'admin.link_icon.manage';
UPDATE `{prefix}permissions` SET `name` = "Manage Static Pages" WHERE `key` = 'admin.page.manage';
UPDATE `{prefix}permissions` SET `name` = "Manage Translation Providers" WHERE `key` = 'admin.translate_provider.manage';

INSERT INTO `{prefix}mail_templates` (`type`, `subject`, `content`, `vars`, `created_at`, `updated_at`, `name`) VALUES
('inactive_user_report', '', '', '[link]', NOW(), NOW(), 'Auto Inactive by system email');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('mail_templates', 'subject', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'inactive_user_report'), 'en', '[[site_name]] Your account is disabled because it has been reported too many times by members.'),
('mail_templates', 'content', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'inactive_user_report'), 'en', '<p>[header]</p><p>Your account is disabled because it has been reported to many times by members. Contact site admin at the below link for more details.</p><p><a href="[link]">[link]</a></p><p>[footer]</p>');

INSERT INTO `{prefix}layout_maps` (`router`, `layout_class`) VALUES
('explore', 'Packages\\ShaunSocial\\Core\\Repositories\\Helpers\\Layout\\ExploreLayout');