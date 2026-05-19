<?php


namespace Packages\ShaunSocial\Group\Repositories\Helpers\Layout;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Repositories\Helpers\Layout\BaseLayout;
use Packages\ShaunSocial\Group\Models\Group;

class GroupProfileLayout extends BaseLayout
{
    public function render(Request $request)
    {
        $request->merge([
            'router' => 'group.profile'
        ]);

        $id = $request->get('id', $request->id);

        if (!$id || ! $group = Group::findByField('id', $id)) {
            return $this->renderNotFound(__('Group is not found'));
        }
        $keywords = '';
        $description = $group->description;
        $hashtags = $group->getHashtags();
        if ($hashtags) {
            $keywords = $hashtags->pluck('name')->join(', ');
        }
        
        return $this->renderData($request, null, [
            'title' => $group->name,
            'ogImage' => $group->getCover(),
            'keywords' => $keywords,
            'description' => $description
        ]);
    }
}
