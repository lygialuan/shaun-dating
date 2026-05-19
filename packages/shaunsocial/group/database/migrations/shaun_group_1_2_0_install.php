<?php


use Illuminate\Database\Migrations\Migration;
use Packages\ShaunSocial\Core\Models\LayoutContent;
use Packages\ShaunSocial\Core\Models\LayoutPage;
use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\RolePermission;
use Packages\ShaunSocial\Group\Repositories\Helpers\Package;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (alreadyUpdate('shaun_group', '1.2.0')) {
            $page = LayoutPage::where('router', 'group.profile')->first();
            $contents = LayoutContent::where('page_id', $page->id)->where('position', 'top')->where('type', 'container')->get();
            foreach ($contents as $content) {
                $content->delete();
            }
            
            updatePackageVersion('shaun_group', '1.2.0');
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
