INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Manage Payment Gateway Recurring', 'admin.gateway_recurring.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 11, '');

INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_gateway_recurring', '1.0.0', NOW(), NOW());

INSERT INTO `{prefix}gateway_recurrings` (`name`, `key`, `class`, `show`, `order`, `created_at`, `updated_at`) VALUES
('CCBill', 'ccbill', 'Packages\\ShaunSocial\\GatewayRecurring\\Repositories\\Helpers\\CCBill', 1 ,1, NOW(), NOW());

