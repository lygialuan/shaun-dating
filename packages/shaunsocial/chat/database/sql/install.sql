INSERT INTO `{prefix}permission_groups` (`name`, `key`, `created_at`, `updated_at`, `package`, `order`) VALUES
('Chat', 'chat', NOW(), NOW(), 'shaun_chat', (SELECT MAX(p.`order`) + 1 FROM `{prefix}permission_groups` as p));

INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Manage Messages', 'admin.chat.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 22, ''),
('Allow sending messages', 'chat.allow', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'chat'), NOW(), NOW(), 'checkbox', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), ''),
('Max voice message length (duration - seconds) can record', 'chat.send_audio_max_duration', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'chat'), NOW(), NOW(), 'number', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

INSERT INTO `{prefix}role_permissions` (`role_id`, `permission_id`, `value`) VALUES
(2, (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'chat.allow'), '1'),
(2, (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'chat.send_audio_max_duration'), '120');

INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
('Chat', 2, 'chat', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_chat');

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('chat.send_photo_max', 'Message - maximum photos can be sent', 'Maximum photos that member can send to friend per message (0 or empty setting is unlimited).', '10', '', 'number', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'chat'), 0),
('chat.send_text_max', 'Message - maximum characters can be sent', 'Maximum characters that member can send to friend per message (0 or empty setting is unlimited).', '1000', '', 'number', 2, 2,(SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'chat'), 0),
('chat.support_files', 'Supported file formats', '', 'txt,doc,docx,csv,xls,xlsx,ppt,pptx,pdf,ai,psd', '', 'text', 3, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'chat'), 0),
('chat.enable_bubble_chat', 'Enable Bottom Bubble Chat', "If this option is disable, users can't not see bottom bubble chat.", '1', '', 'checkbox', 4, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'chat'), 0),
('chat.enable_gifs', 'Enable Tenor Gifs', 'Allow gif at chat', '1', '', 'checkbox', 5, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'chat'), 0);

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('chat_rooms', 'Packages\\ShaunSocial\\Chat\\Models\\ChatRoom'),
('chat_messages', 'Packages\\ShaunSocial\\Chat\\Models\\ChatMessage');

INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_chat', '1.5.1', NOW(), NOW());

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'chat.allow') ,'en', "You don't have send message permission."),
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'chat.send_audio_max_duration') ,'en', "The voice has stoped because you’ve reached the max voice message length ([x] seconds).");