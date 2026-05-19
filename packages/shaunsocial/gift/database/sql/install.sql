INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_gift', '1.1.0', NOW(), NOW());

INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
('Gift', 2, 'gift', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_gift');

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('shaun_gift.enable', 'Enable', "If this option is disable, member can't access Gift function.", '0', '', 'checkbox', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'gift'), 0),
('shaun_gift.platform_fee', 'Platform fee (%)', "This is the fee the system will charge for each gift transaction.", '0', '', 'text', 2, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'gift'), 0);

INSERT INTO `{prefix}permission_groups` (`name`, `key`, `created_at`, `updated_at`, `package`, `order`) VALUES
('Gift', 'gift', NOW(), NOW(), 'shaun_gift', (SELECT MAX(p.`order`) + 1 FROM `{prefix}permission_groups` as p));

INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Allow gift receiving', 'gift.allow_gift_receiving', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'gift'), NOW(), NOW(), 'checkbox', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), ''),
('Manage Gifts', 'gift.manage_gifts', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'gift'), NOW(), NOW(), 'checkbox', 1, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

INSERT INTO `{prefix}translations`(`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'gift.allow_gift_receiving') ,'en', "You do not have permission to receive gifts.");

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('gift_transactions', 'Packages\\ShaunSocial\\Gift\\Models\\GiftTransaction');

INSERT INTO `{prefix}wallet_transaction_sub_types` (`parent_type_id`, `type`, `class`) VALUES
(4, 'gift_fee', 'Packages\\ShaunSocial\\Gift\\Wallet\\Payment\\GiftFee');

INSERT INTO `{prefix}gifts` (`name`, `key`, `icon_default`, `price`, `is_active`, `order`) VALUES
('Heart', 'heart', 'images/default/gift/heart.png', 10, 1, 1),
('Castle', 'castle', 'images/default/gift/castle.png', 20, 1, 2),
('Chocolate Box', 'chocolate_box', 'images/default/gift/chocolate-box.png', 30, 1, 3),
('Crown', 'crown', 'images/default/gift/crown.png', 40, 1, 4),
('Diamond Ring', 'diamond_ring', 'images/default/gift/diamond-ring.png', 50, 1, 5),
('Diamond', 'diamond', 'images/default/gift/diamond.png', 50, 1, 6),
('Fire', 'fire', 'images/default/gift/fire.png', 40, 1, 7),
('Fruits', 'fruits', 'images/default/gift/fruits.png', 30, 1, 8),
('Galaxy', 'galaxy', 'images/default/gift/galaxy.png', 20, 1, 9),
('Hug', 'hug', 'images/default/gift/hug.png', 10, 1, 10),
('Kiss Lips', 'kiss_lips', 'images/default/gift/kiss-lips.png', 20, 1, 11),
('Love Letter', 'love_letter', 'images/default/gift/love-letter.png', 30, 1, 12),
('Luxury Car', 'luxury_car', 'images/default/gift/luxury-car.png', 40, 1, 13),
('Music', 'music', 'images/default/gift/music.png', 50, 1, 14),
('Private Jet', 'private_jet', 'images/default/gift/private-jet.png', 60, 1, 15),
('Rose Flower', 'rose_flower', 'images/default/gift/rose-flower.png', 70, 1, 16),
('Teddy Bear', 'teddy_bear', 'images/default/gift/teddy-bear.png', 80, 1, 17),
('Turtle', 'turtle', 'images/default/gift/turtle.png', 90, 1, 18),
('Wine Glasses', 'wine_glasses', 'images/default/gift/wine-glasses.png', 100, 1, 19),
('Yacht', 'yacht', 'images/default/gift/yacht.png', 200, 1, 20);

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'heart'), 'en', 'Heart'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'castle'), 'en', 'Castle'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'chocolate_box'), 'en', 'Chocolate Box'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'crown'), 'en', 'Crown'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'diamond_ring'), 'en', 'Diamond Ring'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'diamond'), 'en', 'Diamond'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'fire'), 'en', 'Fire'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'fruits'), 'en', 'Fruits'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'galaxy'), 'en', 'Galaxy'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'hug'), 'en', 'Hug'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'kiss_lips'), 'en', 'Kiss Lips'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'love_letter'), 'en', 'Love Letter'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'luxury_car'), 'en', 'Luxury Car'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'music'), 'en', 'Music'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'private_jet'), 'en', 'Private Jet'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'rose_flower'), 'en', 'Rose Flower'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'teddy_bear'), 'en', 'Teddy Bear'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'turtle'), 'en', 'Turtle'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'wine_glasses'), 'en', 'Wine Glasses'),
('gifts', 'name', (SELECT id FROM `{prefix}gifts` WHERE `key` = 'yacht'), 'en', 'Yacht');

