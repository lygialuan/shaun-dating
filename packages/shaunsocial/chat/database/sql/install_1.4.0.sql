INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Max voice message length (duration - seconds) can record', 'chat.send_audio_max_duration', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'chat'), NOW(), NOW(), 'number', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'chat.send_audio_max_duration') ,'en', "The voice has stoped because you’ve reached the max voice message length ([x] seconds).");