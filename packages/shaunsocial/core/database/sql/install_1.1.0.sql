INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_core', '1.1.0', NOW(), NOW());

INSERT INTO `{prefix}currencies` (`code`, `symbol`, `name`, `is_default`, `created_at`, `updated_at`) VALUES
('USD', '$', 'US dollar', 1, NOW(), NOW()),
('EUR', '€', 'Euro', 0, NOW(), NOW()),
('JPY', '¥', 'Japanese yen', 0, NOW(), NOW()),
('GBP', '£', 'British pound sterling', 0, NOW(), NOW()),
('CNY', '¥', 'Chinese yuan', 0, NOW(), NOW()),
('AUD', '$', 'Australian dollar', 0, NOW(), NOW()),
('CAD', '$', 'Canadian dollar', 0, NOW(), NOW()),
('CHF', 'CHF', 'Swiss franc', 0, NOW(), NOW()),
('HKD', '$', 'Hong Kong dollar', 0, NOW(), NOW()),
('NZD', '$', 'New Zealand dollar', 0, NOW(), NOW()),
('VND', '₫', 'Viet Nam dong', 0, NOW(), NOW()),
('SGD', '$', 'Singapore dollar', 0, NOW(), NOW());

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('mobile.android_package_name', 'Android package name', '', '', '', 'text', 2, 3, 12, 0),
('mobile.apple_bundle_id', 'Apple bundle id', '', '', '', 'text', 3, 3, 12, 0),
('mobile.apple_shared_secret', 'Apple shared secret', '', '', '', 'text', 3, 3, 12, 0);

INSERT INTO `{prefix}storage_services` (`name`, `key`, `config`, `is_default`, `extra`) VALUES
('Wasabi S3', 'wasabi', '{"key":"","secret":"","region":"","bucket":"","url":""}', 0, '');

UPDATE `{prefix}layout_maps` SET `router` = 'sp_detail' WHERE `router` = 'page_detail';

-- INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
-- ('site.home_feed_type', 'Home feed updates', '', '', '{"0":"Updates from everyone", "1":"Updates from following members and hashtags"}', 'select', 25, 2, 4, 0);

INSERT INTO `{prefix}layout_maps` (`router`, `layout_class`) VALUES
('media', 'Packages\\ShaunSocial\\Core\\Repositories\\Helpers\\Layout\\MediaLayout'),
('bookmarks', 'Packages\\ShaunSocial\\Core\\Repositories\\Helpers\\Layout\\BookmarkLayout');

UPDATE `{prefix}settings` SET `description` = 'We recommend using an image with a resolution of 200x40 for the site logo.' WHERE `key` = 'site.logo';
UPDATE `{prefix}settings` SET `description` = 'We recommend using an image with a resolution of 200x40 for the site logo darkmode.' WHERE `key` = 'site.logo_darkmode';
UPDATE `{prefix}settings` SET `description` = 'We recommend using an image with a resolution of 200x200 for og site.' WHERE `key` = 'site.og_image';
UPDATE `{prefix}settings` SET `description` = 'We recommend using an image with a resolution of 1024x395 for the default user cover image.' WHERE `key` = 'feature.cover_default';
UPDATE `{prefix}settings` SET `description` = 'We recommend using an image with a resolution of 300x300 for the default user avatar image.' WHERE `key` = 'feature.avatar_default';

UPDATE `{prefix}user_fcm_tokens` SET `hash`= MD5(`token`);