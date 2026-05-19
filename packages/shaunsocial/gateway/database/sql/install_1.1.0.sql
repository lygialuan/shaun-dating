INSERT INTO `{prefix}gateways` (`name`, `key`, `class`, `show`, `order`, `created_at`, `updated_at`) VALUES
('Paystack', 'paystack', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Paystack', 1 ,6, NOW(), NOW()),
('Flutterwave', 'flutterwave', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Flutterwave', 1, 7, NOW(), NOW()),
('CCBill', 'ccbill', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\CCBill', 1 ,8, NOW(), NOW()),
('RazorPay', 'razorpay', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\RazorPay', 1 ,9, NOW(), NOW()),
('Binance', 'bitnance', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Binance', 1 ,10, NOW(), NOW()),
('Cashfree', 'cashfree', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Cashfree', 1 ,11, NOW(), NOW()),
('NowPayments', 'nowpayments', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\NowPayments', 1 ,12, NOW(), NOW());