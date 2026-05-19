<?php


use Illuminate\Database\Migrations\Migration;
use Packages\ShaunSocial\Core\Models\LayoutContent;
use Packages\ShaunSocial\Core\Models\LayoutPage;
use Packages\ShaunSocial\Core\Models\MailTemplate;
use Packages\ShaunSocial\Core\Models\MenuItem;
use Packages\ShaunSocial\Core\Models\ReportCategory;
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
        if (alreadyUpdate('shaun_core', '1.5.2')) {
            $path = base_path('packages/shaunsocial/core/database/sql/install_1.5.2.sql');
            runSqlFile($path);

            $page = LayoutPage::where('router', 'user.profile')->first();
            $contents = LayoutContent::where('page_id', $page->id)->where('position', 'top')->where('type', 'container')->get();
            foreach ($contents as $content) {
                $content->delete();
            }

            $data = [
                'type' => 'router',
                'title' => 'Explore Page',
                'router' => 'explore.index'
            ];
            $this->createLayoutPage($data, 
                [
                    'center' => [
                        [
                            'view_type' => 'desktop',
                            'type' => 'container',
                            'component' => 'ExplorePage',
                            'title' => 'Container',
                            'enable_title' => false,
                            'role_access' => '["all"]',
                            'order' => 1,
                            'package' => 'shaun_core'
                        ],
                        [
                            'view_type' => 'mobile',
                            'type' => 'container',
                            'component' => 'ExplorePage',
                            'title' => 'Container',
                            'enable_title' => false,
                            'role_access' => '["all"]',
                            'order' => 1,
                            'package' => 'shaun_core'
                        ]
                    ]
                ]
            );

            //update menu
            $menuArray = [
                [
                    'name' => 'Explore',
                    'url' => 'explore',
                    'menu_id' => 1,
                    'is_active' => true,
                    'icon_default' => 'images/default/menu/explore.svg',
                    'type' => 'internal',
                    'role_access' => '["all"]',
                    'is_core' => true,
                    'alias' => 'documents',
                    'order' => 1
                ],
                [
                    'name' => 'Explore',
                    'url' => 'explore',
                    'menu_id' => 2,
                    'is_active' => true,
                    'icon_default' => 'images/default/menu/explore.svg',
                    'type' => 'internal',
                    'role_access' => '["all"]',
                    'is_core' => true,
                    'alias' => 'documents',
                    'order' => 1
                ]
            ];

            foreach ($menuArray as $menu) {
                MenuItem::create($menu);
            }

            $category = ReportCategory::getCategoryAi();
            $category->createTranslations('en');

            $mailTempletes = MailTemplate::where('type', 'inactive_user_report')->get();
            
            foreach ($mailTempletes as $mailTemplete) {
                $mailTemplete->createTranslationsWithKey('en');
            }

            updatePackageVersion('shaun_core', '1.5.2');
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
