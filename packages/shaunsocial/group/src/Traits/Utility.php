<?php


namespace Packages\ShaunSocial\Group\Traits;

use Packages\ShaunSocial\Core\Traits\CacheSearchPagination;
use Packages\ShaunSocial\Group\Enum\GroupType;
use Packages\ShaunSocial\Group\Models\GroupBlock;
use Packages\ShaunSocial\Group\Models\GroupMember;

trait Utility
{
    use CacheSearchPagination;
    
    public function filterGroupList($groups, $viewer) {
        $isAdmin = $viewer ? $viewer->isModerator() : false;
        $viewerId = $viewer ? $viewer->id : 0;
        if ($isAdmin) {
            return $groups;
        }
        return $groups->filter(function ($group, $key) use ($viewerId) {
            return $group->checkStatus() && ! GroupBlock::getBlock($viewerId, $group->id);
        });
    }

    public function getCacheSearchGroupPagination($name, $builer, $page)
    {
        return $this->getCacheSearchPagination($name, $builer, $page, 0, config('shaun_group.search_pagination_time'));
    }

    public function addBuilderPostHome($query, $viewer)
    {
        if ($viewer->group_count) {
            if ($viewer->group_count > config('shaun_core.source.max_query_join')) {
                $query->orWhere(function($query) use ($viewer) {
                    $query->where('source_type','groups');
                    $query->whereIn('source_id', function($select) use ($viewer) {
                        $select->from('group_members')
                            ->select('group_id')
                            ->where('user_id', $viewer->id);
                    });
                    $query->where('has_source', true);
                    $query->where('source_privacy', '!=' , GroupType::HIDDEN);
                });
            } else {
                $query->orWhere(function($query) use ($viewer) {
                    $query->where('source_type','groups');
                    $query->whereIn('source_id', GroupMember::getGroupIdsByUser($viewer->id));
                    $query->where('has_source', true);
                    $query->source_privacy('source_privacy', '!=' , GroupType::HIDDEN);
                });
            }
        }
    }
}
