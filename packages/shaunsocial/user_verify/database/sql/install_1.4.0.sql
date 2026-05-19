INSERT INTO `{prefix}mail_templates` (`type`, `subject`, `content`, `vars`, `created_at`, `updated_at`, `name`, `package`) VALUES
('user_verify_reject', '', '', '[reason],[link]', NOW(), NOW(), 'When admin reject verify', 'shaun_user_verify');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('mail_templates', 'subject', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'user_verify_reject'), 'en', '[[site_name]] Verification request update'),
('mail_templates', 'content', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'user_verify_reject'), 'en', '<p>[header]</p><p>Your <a href="[link]">profile</a> verification request has been rejected. Here is comment from admin: [reason]</p><p>[footer]</p>');