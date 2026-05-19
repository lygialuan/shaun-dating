DELETE FROM `{prefix}permissions` WHERE `key` = 'admin.chat.manage';

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'chat.allow') ,'en', "You don't have send message permission.");