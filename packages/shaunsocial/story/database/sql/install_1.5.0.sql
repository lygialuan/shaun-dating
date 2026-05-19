INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Max video size (duration - seconds) can upload', 'story.video_max_duration', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'story'), NOW(), NOW(), 'number', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'story.video_max_duration') ,'en', "You only can share a story video of a max [x] second(s) long.");