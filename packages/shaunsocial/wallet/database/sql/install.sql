INSERT INTO `{prefix}permission_groups` (`name`, `key`, `created_at`, `updated_at`, `package`, `order`) VALUES
('eWallet', 'wallet', NOW(), NOW(), 'shaun_wallet', (SELECT MAX(p.`order`) + 1 FROM `{prefix}permission_groups` as p));

INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Wallets', 'admin.wallet.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 26, ''),
('Deposit packages', 'admin.wallet.package_manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 27, ''),
('Transfer funds requests', 'admin.wallet.withdraw_manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 28, ''),
('Transfer Mass Funds', 'admin.wallet.mass_funds', 0, 1, NOW(), NOW(), 'checkbox', 1, 29, ''),
("System's billing activities", 'admin.wallet.billing_activity', 0, 1, NOW(), NOW(), 'checkbox', 1, 30, ''),
('Transfer funds', 'wallet.transfer_fund', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'wallet'), NOW(), NOW(), 'checkbox', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), ''),
('Send funds', 'wallet.send_fund', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'wallet'), NOW(), NOW(), 'checkbox', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
('eWallet', 2, 'wallet', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_wallet');

INSERT INTO `{prefix}role_permissions` (`role_id`, `permission_id`, `value`) VALUES
(2, (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'wallet.transfer_fund'), '1'),
(2, (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'wallet.send_fund'), '1');

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('shaun_wallet.enable', 'Enable', '', '1', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'wallet'), 0),
('shaun_wallet.token_name', 'Token name', 'By default, the system will automatically create token names based on the first 4 chars of the site name. You can change to the name you want here.', 'DATECOIN', '', 'text', 2, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'wallet'), 0),
('shaun_wallet.exchange_rate', 'Exchange rate', '', '1', '', 'number', 3, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'wallet'), 0),
('shaun_wallet.fund_transfer_enable', 'Enable fund transfer (to allow member to withdraw money)', '', '1', '', 'checkbox', 4, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'wallet'), 0),
('shaun_wallet.fund_transfer_verify_enable', 'Only verified profiles can transfer funds (make sure the user profile verification option is enabled before you enable this function).', '', '0', '', 'checkbox', 5, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'wallet'), 0),
('shaun_wallet.fund_transfer_paypal_enable', 'Transfer fund to Paypal', '', '1', '', 'checkbox', 6, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'wallet'), 0),
('shaun_wallet.fund_transfer_paypal_minimum', 'Paypal minimum withdrawal amounts', '', '10', '', 'number', 7, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'wallet'), 0),
('shaun_wallet.fund_transfer_paypal_fee', 'Paypal fee (%)', '', '2', '', 'number', 8, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'wallet'), 0),
('shaun_wallet.fund_transfer_bank_enable', 'Transfer funds to a bank account', '', '1', '', 'checkbox', 9, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'wallet'), 0),
('shaun_wallet.fund_transfer_bank_minimum', 'Bank minimum withdrawal amounts', '', '10', '', 'number', 10, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'wallet'), 0),
('shaun_wallet.fund_transfer_bank_fee', 'Bank fee (%)', '', '2', '', 'number', 11, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'wallet'), 0),
('shaun_wallet.amount_notify_balance', 'Remind users to deposit more when the balance in the e-wallet is lower than', '', '5', '', 'text', 12, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'wallet'), 0);

INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_wallet', '1.4.0', NOW(), NOW());

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('wallet_orders', 'Packages\\ShaunSocial\\Wallet\\Models\\WalletOrder'),
('wallet_withdraws', 'Packages\\ShaunSocial\\Wallet\\Models\\WalletWithdraw'),
('wallet_transactions', 'Packages\\ShaunSocial\\Wallet\\Models\\WalletTransaction');

INSERT INTO `{prefix}wallet_transaction_types` (`type`,`order`, `is_root`, `class`) VALUES
('buy', 1, 0, 'Packages\\ShaunSocial\\Wallet\\Wallet\\WalletTransactionTypeBuy'),
('send', 2, 0, 'Packages\\ShaunSocial\\Wallet\\Wallet\\WalletTransactionTypeSend'),
('withdraw', 3, 0, 'Packages\\ShaunSocial\\Wallet\\Wallet\\WalletTransactionTypeWithdraw'),
('payment', 4, 0, 'Packages\\ShaunSocial\\Wallet\\Wallet\\WalletTransactionTypePayment'),
('root_buy', 5, 1, 'Packages\\ShaunSocial\\Wallet\\Wallet\\WalletTransactionTypeRootBuy'),
('root_withdraw', 6, 1, 'Packages\\ShaunSocial\\Wallet\\Wallet\\WalletTransactionTypeRootWithdraw');
-- ('commission', 7, 0, 'Packages\\ShaunSocial\\Wallet\\Wallet\\WalletTransactionTypeCommission');


INSERT INTO `{prefix}mail_templates` (`type`, `subject`, `content`, `vars`, `created_at`, `updated_at`, `name`, `package`) VALUES
( 'wallet_balance_notify', '', '', '[link]', NOW(), NOW(), 'Remind users to deposit more when the balance in the ewallet is lower', 'shaun_wallet');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('mail_templates', 'subject', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'wallet_balance_notify'), 'en', '[site_name] Your account balance is running low'),
('mail_templates', 'content', (SELECT `id` FROM `{prefix}mail_templates` WHERE `type` = 'wallet_balance_notify'), 'en', '<p>[header]</p><p>Balance of your eWallet is running low.</p><p>The services that you’re paying using the eWallet on [site_name] will stop serving soon if your account runs out of money.</p><p>To keep your current services running, make a payment to add money to your ewallet.</p><p><a href="[link]">Make Payment Now</a></p><p>[footer]</p>');

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'wallet.transfer_fund') ,'en', "You don't have funds transferring permission."),
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'wallet.send_fund') ,'en', "You don't have funds sending permission.");