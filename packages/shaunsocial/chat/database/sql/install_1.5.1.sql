INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('chat_messages', 'Packages\\ShaunSocial\\Chat\\Models\\ChatMessage');

INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Manage Messages', 'admin.chat.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 22, '');
