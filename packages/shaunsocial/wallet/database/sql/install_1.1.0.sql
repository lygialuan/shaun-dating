INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Transfer Mass Funds', 'admin.wallet.mass_funds', 0, 1, NOW(), NOW(), 'checkbox', 1, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), ''),
("System's billing activities", 'admin.wallet.billing_activity', 0, 1, NOW(), NOW(), 'checkbox', 1, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'wallet.transfer_fund') ,'en', "You don't have funds transferring permission."),
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'wallet.send_fund') ,'en', "You don't have funds sending permission.");