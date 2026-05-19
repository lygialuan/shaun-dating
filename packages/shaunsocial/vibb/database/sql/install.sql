INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_vibb', '1.1.0', NOW(), NOW());

-- INSERT INTO `{prefix}permission_groups` (`name`, `key`, `created_at`, `updated_at`, `package`, `order`) VALUES
-- ('Vibb', 'vibb', NOW(), NOW(), 'shaun_vibb', (SELECT MAX(p.`order`) + 1 FROM `{prefix}permission_groups` as p));

-- INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
-- ('Vibb songs', 'admin.vibb.song_manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 25, ''),
-- ('Allow vibb creation', 'vibb.allow_create', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'vibb'), NOW(), NOW(), 'checkbox', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), ''),
-- ('Max video size (duration - seconds) can upload', 'vibb.video_max_duration', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'vibb'), NOW(), NOW(), 'number', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), ''),
-- ('Maximum number of vibbs can create per day (0 is unlimited)', 'vibb.max_per_day', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'vibb'), NOW(), NOW(), 'number', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

-- INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
-- ('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'vibb.allow_create') ,'en', "You don't have vibb creation permission."),
-- ('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'vibb.video_max_duration') ,'en', "You only can share a video of a max [x] second(s) long."),
-- ('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'vibb.max_per_day') ,'en', "To prevent spam, we only allow you to share a maximum of [x] vibbs per day.");

-- INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
-- ('Vibb', 2, 'vibb', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_vibb');

-- INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
-- ('shaun_vibb.enable', 'Enable', 'Members will not be able to create Vibbs if disabled. Previously created Vibbs will still work normally.', '1', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'vibb'), 0);

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('vibb_post_songs', 'Packages\\ShaunSocial\\Vibb\\Models\\VibbPostSong');