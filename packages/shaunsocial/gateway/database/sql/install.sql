INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Manage Payment Gateways', 'admin.gateway.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 11, '');

INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_gateway', '1.2.0', NOW(), NOW());

INSERT INTO `{prefix}gateways` (`name`, `key`, `class`, `show`, `order`, `created_at`, `updated_at`) VALUES
('Paypal', 'paypal' ,'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Paypal', 1, 1, NOW(), NOW()),
('Stripe', 'stripe','Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Stripe', 1, 2, NOW(), NOW()),
('Apple', 'apple', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Apple', 0, 3, NOW(), NOW()),
('Google', 'google', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Google', 0, 4, NOW(), NOW()),
('Wallet', 'wallet','Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Wallet', 0, 5, NOW(), NOW()),
('Paystack', 'paystack', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Paystack', 1 ,6, NOW(), NOW()),
('Flutterwave', 'flutterwave', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Flutterwave', 1, 7, NOW(), NOW()),
('CCBill', 'ccbill', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\CCBill', 1 ,8, NOW(), NOW()),
('RazorPay', 'razorpay', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\RazorPay', 1 ,9, NOW(), NOW()),
('Binance', 'bitnance', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Binance', 1 ,10, NOW(), NOW()),
('Cashfree', 'cashfree', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Cashfree', 1 ,11, NOW(), NOW()),
('NowPayments', 'nowpayments', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\NowPayments', 1 ,12, NOW(), NOW());