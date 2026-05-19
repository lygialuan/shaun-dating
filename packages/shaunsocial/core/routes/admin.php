<?php


use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\MobileControler;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\AuthController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\CacheController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\CountryController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\CurrencyController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\DashboardController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\GenderController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\HashtagController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\LanguageController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\LogController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\MailController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\MenuController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\OpenidController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\PageController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\ReportController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\RoleController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\SettingController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\StorageController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\TaskController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\TranslationController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\UserController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\LayoutController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\SubscriptionController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\ThemeControler;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\UtilityController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\ContentWarningController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\LinkIconController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\SmsProviderController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\TranslateProviderController;
use Packages\ShaunSocial\Core\Http\Controllers\Admin\TwoFactorProviderController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/
Route::group(['prefix' => env('APP_ADMIN_PREFIX', 'admin'), 'as' => 'admin.', 'middleware' => ['web', 'is.admin']], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/chart', [DashboardController::class, 'chart'])->name('dashboard.chart');
    Route::get('/dashboard/clear_cache', [DashboardController::class, 'clear_cache'])->name('dashboard.clear_cache');
    Route::get('/dashboard/change_language/{key}', [DashboardController::class, 'change_language'])->name('dashboard.change_language');

    Route::get('/roles', [RoleController::class, 'index'])->name('role.index');
    Route::get('/roles/create/{id?}', [RoleController::class, 'create'])->where('id', '[0-9]+')->name('role.create');
    Route::get('/roles/permission/{id}', [RoleController::class, 'permission'])->where('id', '[0-9]+')->name('role.permission');
    Route::post('/roles/store', [RoleController::class, 'store'])->name('role.store');
    Route::post('/roles/store_permission', [RoleController::class, 'store_permission'])->name('role.store_permission');
    Route::get('/roles/delete/{id}', [RoleController::class, 'delete'])->where('id', '[0-9]+')->name('role.delete');
    Route::get('/roles/translate_permission/{package?}', [RoleController::class, 'translate_permission'])->name('role.translate_permission');
    Route::post('/roles/store_default', [RoleController::class, 'store_default'])->name('role.store_default');

    Route::get('/settings/site', [SettingController::class, 'site'])->name('setting.site');
    Route::get('/settings/general', [SettingController::class, 'general'])->name('setting.general');
    Route::get('/settings/mobile_general', [SettingController::class, 'mobile_general'])->name('setting.mobile_general');
    Route::post('/settings/store', [SettingController::class, 'store'])->name('setting.store');
    Route::get('/settings/delete_image/{id}', [SettingController::class, 'delete_image'])->name('setting.delete_image');
    Route::get('/settings/translate/{package?}', [SettingController::class, 'translate'])->name('setting.translate');
    Route::get('/settings/suggest/{text?}', [SettingController::class, 'suggest'])->name('setting.suggest');
    Route::post('/settings/check_fmpeg', [SettingController::class, 'check_fmpeg'])->name('setting.check_fmpeg');

    Route::get('/languages', [LanguageController::class, 'index'])->name('language.index');
    Route::get('/languages/create/{id?}', [LanguageController::class, 'create'])->where('id', '[0-9]+')->name('language.create');
    Route::post('/languages/store', [LanguageController::class, 'store'])->name('language.store');
    Route::get('/languages/delete/{id}', [LanguageController::class, 'delete'])->where('id', '[0-9]+')->name('language.delete');    
    Route::get('/languages/edit_phrase/{id}', [LanguageController::class, 'edit_phrase'])->where('id', '[0-9]+')->name('language.edit_phrase');
    Route::get('/languages/download_phrase/{id}', [LanguageController::class, 'download_phrase'])->where('id', '[0-9]+')->name('language.download_phrase');
    Route::get('/languages/upload_phrase/{id}', [LanguageController::class, 'upload_phrase'])->where('id', '[0-9]+')->name('language.upload_phrase');
    Route::post('/languages/store_upload_phrase/{id}', [LanguageController::class, 'store_upload_phrase'])->where('id', '[0-9]+')->name('language.store_upload_phrase');
    Route::post('/languages/store_phrase/{id}', [LanguageController::class, 'store_phrase'])->where('id', '[0-9]+')->name('language.store_phrase');

    Route::get('/translations/edit_model/{model}/{column}/{id}/{empty?}', [TranslationController::class, 'edit_model'])->where('model', '(.*)')->where('column', '(.*)')->where('id', '[0-9]+')->name('translation.edit_model');
    Route::post('/translations/store_model', [TranslationController::class, 'store_model'])->name('translation.store_model');

    Route::get('/pages', [PageController::class, 'index'])->name('page.index');
    Route::get('/pages/create/{id?}/{language?}', [PageController::class, 'create'])->where('id', '[0-9]+')->where('language', '[a-z]+')->name('page.create');
    Route::post('/pages/store', [PageController::class, 'store'])->name('page.store');
    Route::get('/pages/delete/{id}', [PageController::class, 'delete'])->where('id', '[0-9]+')->name('page.delete');

    Route::get('/menus', [MenuController::class, 'index'])->name('menu.index');
    Route::get('/menus/create_item/{menu_id}/{id?}', [MenuController::class, 'create_item'])->where('menu_id', '[0-9]+')->where('id', '[0-9]+')->name('menu.create_item');
    Route::post('/menus/store_item', [MenuController::class, 'store_item'])->name('menu.store_item');
    Route::post('/menus/store_order', [MenuController::class, 'store_order'])->name('menu.store_order');
    Route::get('/menus/delete_item/{id}', [MenuController::class, 'delete_item'])->where('id', '[0-9]+')->name('menu.delete_item');

    Route::get('/mails', [MailController::class, 'index'])->name('mail.index');
    Route::get('/mails/{id?}', [MailController::class, 'template'])->where('id', '[0-9]+')->name('mail.template');
    Route::post('/mails/store_template', [MailController::class, 'store_template'])->name('mail.store_template');
    Route::get('/mails/test', [MailController::class, 'test'])->name('mail.test');
    Route::post('/mails/store_test', [MailController::class, 'store_test'])->name('mail.store_test');
    Route::get('/mails/translate/{package?}', [MailController::class, 'translate'])->name('mail.translate');

    Route::get('/tasks', [TaskController::class, 'index'])->name('task.index');

    Route::get('/caches', [CacheController::class, 'index'])->name('cache.index');
    Route::post('/caches/store', [CacheController::class, 'store'])->name('cache.store');

    Route::match(['GET', 'POST'], '/logs', [LogController::class, 'index'])->name('log.index');
    Route::get('/logs/download/{file_name?}', [LogController::class, 'download'])->where('file_name', '(.*)')->name('log.download');

    Route::get('/storages', [StorageController::class, 'index'])->name('storage.index');
    Route::get('/storages/edit/{id}', [StorageController::class, 'edit'])->where('id', '[0-9]+')->name('storage.edit');
    Route::post('/storages/store', [StorageController::class, 'store'])->name('storage.store');
    Route::get('/storages/transfer/{id}', [StorageController::class, 'transfer'])->where('id', '[0-9]+')->name('storage.transfer');
    Route::get('/storages/stop_transfer', [StorageController::class, 'stop_transfer'])->name('storage.stop_transfer');

    Route::get('/hashtags/create', [HashtagController::class, 'create'])->name('hashtag.create');
    Route::post('/hashtags/store', [HashtagController::class, 'store'])->name('hashtag.store');
    Route::post('/hashtags/store_active', [HashtagController::class, 'store_active'])->name('hashtag.store_active');
    Route::get('/hashtags', [HashtagController::class, 'index'])->name('hashtag.index');

    Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::get('/users', [UserController::class, 'index'])->name('user.index');
    Route::get('/users/create/{id?}', [UserController::class, 'create'])->where('id', '[0-9]+')->name('user.create');
    Route::post('/users/store', [UserController::class, 'store'])->name('user.store');
    Route::post('/users/store_manage', [UserController::class, 'store_manage'])->name('user.store_manage');
    Route::get('/users/login_as/{id}', [UserController::class, 'login_as'])->where('id', '[0-9]+')->name('user.login_as');
    Route::get('/users/change_password/{id}', [UserController::class, 'change_password'])->where('id', '[0-9]+')->name('user.change_password');
    Route::post('/users/store_change_password/{id}', [UserController::class, 'store_change_password'])->where('id', '[0-9]+')->name('user.store_change_password');
    Route::get('/users/remove_login_all_devices/{id}', [UserController::class, 'remove_login_all_devices'])->where('id', '[0-9]+')->name('user.remove_login_all_devices');
    Route::get('/users/export_csv', [UserController::class, 'export_csv'])->name('user.export_csv');
    Route::get('/users/store_phone_unverify/{id}', [UserController::class, 'store_phone_unverify'])->where('id', '[0-9]+')->name('user.store_phone_unverify');
    Route::get('/users/store_phone_verify/{id}', [UserController::class, 'store_phone_verify'])->where('id', '[0-9]+')->name('user.store_phone_verify');

    Route::get('/openids', [OpenidController::class, 'index'])->name('openid.index');
    Route::get('/openids/create/{id?}', [OpenidController::class, 'create'])->where('id', '[0-9]+')->name('openid.create');
    Route::post('/openids/store', [OpenidController::class, 'store'])->name('openid.store');
    Route::get('/openids/delete/{id}', [OpenidController::class, 'delete'])->where('id', '[0-9]+')->name('openid.delete');
    Route::post('/openids/store_order', [OpenidController::class, 'store_order'])->name('openid.store_order');

    Route::get('/reports', [ReportController::class, 'index'])->name('report.index');
    Route::get('/reports/delete/{id}', [ReportController::class, 'delete'])->where('id', '[0-9]+')->name('report.delete');
    Route::post('/reports/multi_delete', [ReportController::class, 'multi_delete'])->name('report.multi_delete');
    Route::get('/reports/category', [ReportController::class, 'category'])->name('report.category');
    Route::get('/reports/create_category/{id?}', [ReportController::class, 'create_category'])->where('id', '[0-9]+')->name('report.create_category');
    Route::post('/reports/store_category', [ReportController::class, 'store_category'])->name('report.store_category');
    Route::post('/reports/store_category_order', [ReportController::class, 'store_category_order'])->name('report.store_category_order');
    Route::get('/reports/delete_category/{id}', [ReportController::class, 'delete_category'])->where('id', '[0-9]+')->name('report.delete_category');

    Route::get('/layouts/{id?}', [LayoutController::class, 'index'])->where('id', '[0-9]+')->name('layout.index');
    Route::get('/layouts/edit_block/{component?}/{id?}', [LayoutController::class, 'edit_block'])->where('id', '[0-9]+')->where('component', '[A-Za-z]+')->name('layout.edit_block');
    Route::post('/layouts/store_blocks', [LayoutController::class, 'store_blocks'])->name('layout.store_blocks');
    Route::get('/layouts/edit/{id?}', [LayoutController::class, 'edit'])->where('id', '[0-9]+')->name('layout.edit');
    Route::post('/layouts/store', [LayoutController::class, 'store'])->where('id', '[0-9]+')->name('layout.store');
    Route::post('/layouts/get_data_block', [LayoutController::class, 'get_data_block'])->name('layout.get_data_block');
    Route::post('/layouts/upload_file_block', [LayoutController::class, 'upload_file_block'])->name('layout.upload_file_block');

    Route::get('/genders', [GenderController::class, 'index'])->name('gender.index');
    Route::get('/genders/create/{id?}', [GenderController::class, 'create'])->where('id', '[0-9]+')->name('gender.create');
    Route::post('/genders/store', [GenderController::class, 'store'])->name('gender.store');
    Route::post('/genders/store_order', [GenderController::class, 'store_order'])->name('gender.store_order');
    Route::get('/genders/delete/{id}', [GenderController::class, 'delete'])->where('id', '[0-9]+')->name('gender.delete');

    Route::get('/mobile/broadcast_message', [MobileControler::class, 'broadcast_message'])->name('mobile.broadcast_message');
    Route::post('/mobile/store_broadcast_message', [MobileControler::class, 'store_broadcast_message'])->name('mobile.store_broadcast_message');
    
    Route::get('/themes', [ThemeControler::class, 'index'])->name('theme.index');
    Route::get('/themes/create/{id?}', [ThemeControler::class, 'create'])->where('id', '[0-9]+')->name('theme.create');
    Route::post('/themes/store', [ThemeControler::class, 'store'])->where('id', '[0-9]+')->name('theme.store');
    Route::get('/themes/setting/{type}/{id}', [ThemeControler::class, 'setting'])->whereIn('type', ['light', 'dark'])->where('id', '[0-9]+')->name('theme.setting');
    Route::post('/themes/store_setting', [ThemeControler::class, 'store_setting'])->name('theme.store_setting');
    Route::get('/themes/delete/{id}', [ThemeControler::class, 'delete'])->where('id', '[0-9]+')->name('theme.delete');
    Route::post('/themes/store_active', [ThemeControler::class, 'store_active'])->name('theme.store_active');
    Route::get('/themes/store_reset_setting/{type}/{id}', [ThemeControler::class, 'store_reset_setting'])->whereIn('type', ['light', 'dark'])->where('id', '[0-9]+')->name('theme.store_reset_setting');

    Route::get('/utility/user_suggest/{text}', [UtilityController::class, 'user_suggest'])->name('utility.user_suggest');

    Route::get('/currencies', [CurrencyController::class, 'index'])->name('currency.index');
    Route::get('/currencies/create/{id?}', [CurrencyController::class, 'create'])->where('id', '[0-9]+')->name('currency.create');
    Route::post('/currencies/store', [CurrencyController::class, 'store'])->name('currency.store');
    Route::get('/currencies/delete/{id}', [CurrencyController::class, 'delete'])->where('id', '[0-9]+')->name('currency.delete');
    Route::post('/currencies/store_default', [CurrencyController::class, 'store_default'])->name('currency.store_default');

    Route::get('/countries', [CountryController::class, 'index'])->name('country.index');
    Route::get('/countries/create/{id?}', [CountryController::class, 'create'])->where('id', '[0-9]+')->name('country.create');
    Route::post('/countries/store', [CountryController::class, 'store'])->name('country.store');
    Route::get('/countries/delete/{id}', [CountryController::class, 'delete'])->where('id', '[0-9]+')->name('country.delete');
    Route::post('/countries/store_order', [CountryController::class, 'store_order'])->name('country.store_order');
    Route::post('/countries/store_active', [CountryController::class, 'store_active'])->name('country.store_active');
    
    Route::get('/countries/state/{country_id}', [CountryController::class, 'state'])->where('country_id', '[0-9]+')->name('country.state.index');
    Route::get('/countries/create_state/{country_id}/{id?}', [CountryController::class, 'create_state'])->where('country_id', '[0-9]+')->where('id', '[0-9]+')->name('country.state.create');
    Route::post('/countries/store_state', [CountryController::class, 'store_state'])->name('country.state.store');
    Route::get('/countries/delete_state/{id}', [CountryController::class, 'delete_state'])->where('id', '[0-9]+')->name('country.state.delete');
    Route::post('/countries/store_state_order', [CountryController::class, 'store_state_order'])->name('country.state.store_order');
    Route::post('/countries/store_state_active', [CountryController::class, 'store_state_active'])->name('country.state.store_active');
    Route::get('/countries/import_state/{country_id}', [CountryController::class, 'import_state'])->where('country_id', '[0-9]+')->name('country.state.import');
    Route::post('/countries/store_import_state/{country_id}', [CountryController::class, 'store_import_state'])->where('country_id', '[0-9]+')->name('country.state.store_import');

    Route::get('/countries/city/{state_id}', [CountryController::class, 'city'])->where('state_id', '[0-9]+')->name('country.city.index');
    Route::get('/countries/create_city/{state_id}/{id?}', [CountryController::class, 'create_city'])->where('state_id', '[0-9]+')->where('id', '[0-9]+')->name('country.city.create');
    Route::post('/countries/store_city', [CountryController::class, 'store_city'])->name('country.city.store');
    Route::get('/countries/delete_city/{id}', [CountryController::class, 'delete_city'])->where('id', '[0-9]+')->name('country.city.delete');
    Route::post('/countries/store_city_order', [CountryController::class, 'store_city_order'])->name('country.city.store_order');
    Route::post('/countries/store_city_active', [CountryController::class, 'store_city_active'])->name('country.city.store_active');
    Route::get('/countries/import_city/{state_id}', [CountryController::class, 'import_city'])->where('state_id', '[0-9]+')->name('country.city.import');
    Route::post('/countries/store_import_city/{state_id}', [CountryController::class, 'store_import_city'])->where('state_id', '[0-9]+')->name('country.city.store_import');

    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('/subscriptions/detail/{id}', [SubscriptionController::class, 'detail'])->where('id', '[0-9]+')->name('subscription.detail');
    Route::get('/subscriptions/cancel/{id}', [SubscriptionController::class, 'cancel'])->where('id', '[0-9]+')->name('subscription.cancel');
    Route::get('/subscriptions/resume/{id}', [SubscriptionController::class, 'resume'])->where('id', '[0-9]+')->name('subscription.resume');

    Route::get('/content_warnings/category', [ContentWarningController::class, 'category'])->name('content_warning.category');
    Route::get('/content_warnings/create_category/{id?}', [ContentWarningController::class, 'create_category'])->where('id', '[0-9]+')->name('content_warning.create_category');
    Route::post('/content_warnings/store_category', [ContentWarningController::class, 'store_category'])->name('content_warning.store_category');
    Route::get('/content_warnings/delete/{id?}', [ContentWarningController::class, 'delete_category'])->where('id', '[0-9]+')->name('content_warning.delete_category');
    Route::post('/content_warnings/store_order', [ContentWarningController::class, 'store_order'])->name('content_warning.store_order');

    Route::get('/sms_providers', [SmsProviderController::class, 'index'])->name('sms_provider.index');
    Route::get('/sms_providers/edit/{id}', [SmsProviderController::class, 'edit'])->where('id', '[0-9]+')->name('sms_provider.edit');
    Route::post('/sms_providers/store', [SmsProviderController::class, 'store'])->name('sms_provider.store');
    Route::get('/sms_providers/test/{id}', [SmsProviderController::class, 'test'])->where('id', '[0-9]+')->name('sms_provider.test');
    Route::post('/sms_providers/store_test', [SmsProviderController::class, 'store_test'])->name('sms_provider.store_test');

    Route::get('/two_factor_providers', [TwoFactorProviderController::class, 'index'])->name('two_factor_provider.index');
    Route::get('/two_factor_providers/edit/{id}', [TwoFactorProviderController::class, 'edit'])->where('id', '[0-9]+')->name('two_factor_provider.edit');
    Route::post('/two_factor_providers/store', [TwoFactorProviderController::class, 'store'])->name('two_factor_provider.store');
    Route::get('/two_factor_providers', [TwoFactorProviderController::class, 'index'])->name('two_factor_provider.index');
    Route::get('/two_factor_providers/remove_user/{id}', [TwoFactorProviderController::class, 'remove_user'])->where('id', '[0-9]+')->name('two_factor_provider.remove_user');

    Route::get('/link_icons', [LinkIconController::class, 'index'])->name('link_icon.index');
    Route::get('/link_icons/create/{id?}', [LinkIconController::class, 'create'])->where('id', '[0-9]+')->name('link_icon.create');
    Route::post('/link_icons/store', [LinkIconController::class, 'store'])->name('link_icon.store');
    Route::get('/link_icons/delete/{id}', [LinkIconController::class, 'delete'])->where('id', '[0-9]+')->name('link_icon.delete');

    Route::get('/translate_providers', [TranslateProviderController::class, 'index'])->name('translate_provider.index');
    Route::get('/translate_providers/edit/{id}', [TranslateProviderController::class, 'edit'])->where('id', '[0-9]+')->name('translate_provider.edit');
    Route::post('/translate_providers/store', [TranslateProviderController::class, 'store'])->name('translate_provider.store');
});

Route::group(['prefix' => env('APP_ADMIN_PREFIX', 'admin'), 'as' => 'admin.', 'middleware' => ['web', 'is.admin.guest']], function () {
    Route::get('/login', [AuthController::class, 'index'])->name('auth.index');
    Route::post('/login', [AuthController::class, 'login'])->where('id', '[0-9]+')->name('auth.login');
});
