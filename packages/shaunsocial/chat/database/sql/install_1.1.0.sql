INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('chat.support_files', 'Supported file formats', '', 'txt,doc,docx,csv,xls,xlsx,ppt,pptx,pdf,ai,psd', '', 'text', 3, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'chat'), 0);

INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_chat', '1.1.0', NOW(), NOW());

