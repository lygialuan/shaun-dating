<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Packages\ShaunSocial\Core\Models\LayoutPage;
use Packages\ShaunSocial\Core\Models\MenuItem;
use Packages\ShaunSocial\Core\Models\Theme;
use Packages\ShaunSocial\Core\Traits\Utility;

return new class extends Migration
{
    use Utility;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (alreadyUpdate('shaun_core', '1.1.0')) {
            $path = base_path('packages/shaunsocial/core/database/sql/install_1.1.0.sql');
            runSqlFile($path);

            //update menu
            $menus = MenuItem::all();
            $menus->each(function($menu){
                if (strpos($menu->url,'pages/') === 0) {
                    $menu->update([
                        'url' => str_replace('pages/', 'sp/', $menu->url)
                    ]);
                }
            });
            
            $menuArray = [
                [
                    'name' => 'Media',
                    'url' => 'media',
                    'menu_id' => 1,
                    'is_active' => true,
                    'icon_default' => 'images/default/menu/media.svg',
                    'type' => 'internal',
                    'role_access' => '["all"]',
                    'is_core' => true,
                    'alias' => 'media',
                    'order' => 3,
                ],
                [
                    'name' => 'Media',
                    'url' => 'media',
                    'menu_id' => 2,
                    'is_active' => true,
                    'icon_default' => 'images/default/menu/media.svg',
                    'type' => 'internal',
                    'role_access' => '["all"]',
                    'is_core' => true,
                    'alias' => 'media',
                    'order' => 3,
                ],
            ];

            foreach ($menuArray as $menu) {
                MenuItem::create($menu);
            }

            //update settings
            $themes = Theme::all();
            $themes->each(function($theme){
                $theme->update([
                    'settings_dark' => json_encode(getThemeSettingDarkDefault())
                ]);
            });

            //create bookmark page
            $data = [
                'type' => 'router',
                'title' => 'Bookmark Page',
                'router' => 'bookmark.index'
            ];
            // $this->createLayoutPage($data, 
            //     [
            //         'right' => [
            //             [
            //                 'view_type' => 'desktop',
            //                 'type' => 'component',
            //                 'component' => 'UserTrending',
            //                 'title' => 'Trending Users',
            //                 'enable_title' => true,
            //                 'role_access' => '["all"]',
            //                 'order' => 1,
            //                 'package' => 'shaun_core',
            //                 'class' => 'Packages\ShaunSocial\Core\Repositories\Helpers\Widget\UserTrendingWidget',
            //                 'params' => '{"item_number":"10"}'
            //             ],
            //         ],
            //         'center' => [
            //             [
            //                 'view_type' => 'desktop',
            //                 'type' => 'container',
            //                 'component' => 'BookmarkPage',
            //                 'title' => 'Container',
            //                 'enable_title' => false,
            //                 'role_access' => '["all"]',
            //                 'order' => 1,
            //                 'package' => 'shaun_core'
            //             ],
            //             [
            //                 'view_type' => 'mobile',
            //                 'type' => 'container',
            //                 'component' => 'BookmarkPage',
            //                 'title' => 'Container',
            //                 'enable_title' => false,
            //                 'role_access' => '["all"]',
            //                 'order' => 1,
            //                 'package' => 'shaun_core'
            //             ]
            //         ]
            //     ]
            // );

            //create media page
            $data = [
                'type' => 'router',
                'title' => 'Media Page',
                'router' => 'media.index'
            ];
            $this->createLayoutPage($data, 
                [
                    'center' => [
                        [
                            'view_type' => 'desktop',
                            'type' => 'container',
                            'component' => 'MediaPage',
                            'title' => 'Container',
                            'enable_title' => false,
                            'role_access' => '["all"]',
                            'order' => 1,
                            'package' => 'shaun_core'
                        ],
                        [
                            'view_type' => 'mobile',
                            'type' => 'container',
                            'component' => 'MediaPage',
                            'title' => 'Container',
                            'enable_title' => false,
                            'role_access' => '["all"]',
                            'order' => 1,
                            'package' => 'shaun_core'
                        ]
                    ]
                ]
            );


            //update layout for profile
            $page = LayoutPage::findByField('router', 'user.profile');
            $this->addLayoutContents($page, [
                'right' => [
                    [
                        'view_type' => 'desktop',
                        'type' => 'component',
                        'component' => 'UserTrending',
                        'title' => 'Trending Users',
                        'enable_title' => true,
                        'role_access' => '["all"]',
                        'order' => 1,
                        'package' => 'shaun_core',
                        'class' => 'Packages\ShaunSocial\Core\Repositories\Helpers\Widget\UserTrendingWidget',
                        'params' => '{"item_number":"10"}'
                    ],
                ],
                'top' => [
                    [
                        'view_type' => 'desktop',
                        'type' => 'container',
                        'component' => 'ProfileHeaderPage',
                        'title' => 'Container',
                        'enable_title' => false,
                        'role_access' => '["all"]',
                        'order' => 1,
                        'package' => 'shaun_core'
                    ],
                    [
                        'view_type' => 'mobile',
                        'type' => 'container',
                        'component' => 'ProfileHeaderPage',
                        'title' => 'Container',
                        'enable_title' => false,
                        'role_access' => '["all"]',
                        'order' => 1,
                        'package' => 'shaun_core'
                    ]
                ]
            ]);
            updatePackageVersion('shaun_core', '1.1.0');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
