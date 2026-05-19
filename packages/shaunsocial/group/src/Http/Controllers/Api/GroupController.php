<?php


namespace Packages\ShaunSocial\Group\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Models\UserActionLog;
use Packages\ShaunSocial\Group\Http\Requests\AcceptJoinRequestValidate;
use Packages\ShaunSocial\Group\Http\Requests\AcceptMultiJoinRequestValidate;
use Packages\ShaunSocial\Group\Http\Requests\AcceptPostPendingValidate;
use Packages\ShaunSocial\Group\Http\Requests\AddAdminValidate;
use Packages\ShaunSocial\Group\Http\Requests\AdminValidate;
use Packages\ShaunSocial\Group\Http\Requests\DeleteJoinRequestValidate;
use Packages\ShaunSocial\Group\Http\Requests\DeleteMyPostPendingValidate;
use Packages\ShaunSocial\Group\Http\Requests\DeletePostPendingValidate;
use Packages\ShaunSocial\Group\Http\Requests\DeleteValidate;
use Packages\ShaunSocial\Group\Http\Requests\GetAdminValidate;
use Packages\ShaunSocial\Group\Http\Requests\GetBlocksValidate;
use Packages\ShaunSocial\Group\Http\Requests\GetGroupAllValidate;
use Packages\ShaunSocial\Group\Http\Requests\GetJoinRequestValidate;
use Packages\ShaunSocial\Group\Http\Requests\GetManageGroupValidate;
use Packages\ShaunSocial\Group\Http\Requests\GetMembersValidate;
use Packages\ShaunSocial\Group\Http\Requests\GetMyPostPendingValidate;
use Packages\ShaunSocial\Group\Http\Requests\GetPostPendingValidate;
use Packages\ShaunSocial\Group\Http\Requests\GetPostValidate;
use Packages\ShaunSocial\Group\Http\Requests\GetPostWithHashtagValidate;
use Packages\ShaunSocial\Group\Http\Requests\GetProfileValidate;
use Packages\ShaunSocial\Group\Http\Requests\GetRuleValidate;
use Packages\ShaunSocial\Group\Http\Requests\GroupValidate;
use Packages\ShaunSocial\Group\Http\Requests\RemoveAdminValidate;
use Packages\ShaunSocial\Group\Http\Requests\RemoveBlockValidate;
use Packages\ShaunSocial\Group\Http\Requests\RemoveJoinRequestValidate;
use Packages\ShaunSocial\Group\Http\Requests\RemoveMemberValidate;
use Packages\ShaunSocial\Group\Http\Requests\SearchPostValidate;
use Packages\ShaunSocial\Group\Http\Requests\SearchUserForAdminValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreBlockValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreCategoryValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreDescriptionValidate;
use Packages\ShaunSocial\Group\Repositories\Api\GroupRepository;
use Packages\ShaunSocial\Group\Http\Requests\StoreGroupValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreHashtagValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreHideValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreJoinValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreLeaveValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreNameValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreNotifySettingValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreOpenValidate;
use Packages\ShaunSocial\Group\Http\Requests\StorePinValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreRuleOrderValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreRuleValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreSettingValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreTranferValidate;
use Packages\ShaunSocial\Group\Http\Requests\StoreTypePrivate;
use Packages\ShaunSocial\Group\Http\Requests\UploadCoverValidate;
use Packages\ShaunSocial\Group\Http\Requests\DeleteRuleValidate;

class GroupController extends ApiController
{
    protected $groupRepository;

    public function __construct(GroupRepository $groupRepository)
    {
        if (! setting('shaun_group.enable')) {
            throw new MessageHttpException(__('Do not support this method.'));
        }

        $this->groupRepository = $groupRepository;
    }

    public function get_categories()
    {
        $result = $this->groupRepository->get_categories();

        return $this->successResponse($result);
    }

    public function store(StoreGroupValidate $request)
    {
        $request->mergeIfMissing([
            'hashtags' => [],
            'description' => '',
        ]);

        $result = $this->groupRepository->store($request->only([
            'name', 'categories', 'hashtags', 'description', 'type'
        ]), $request->user());
        
        UserActionLog::create([
            'user_id' => $request->user()->id,
            'type' => 'create_group'
        ]);

        return $this->successResponse($result);
    }

    public function get_profile(GetProfileValidate $request)
    {
        $result = $this->groupRepository->get_profile($request->id);

        return $this->successResponse($result);
    }

    public function store_rule(StoreRuleValidate $request)
    {
        $request->mergeIfMissing([
            'id' => '',
        ]);
        
        $this->groupRepository->store_rule($request->only([
            'id', 'group_id', 'title', 'description'
        ]));

        return $this->successResponse();
    }

    public function get_rule(GetRuleValidate $request)
    {
        $result = $this->groupRepository->get_rule($request->id);

        return $this->successResponse($result);
    }

    public function store_rule_order(StoreRuleOrderValidate $request)
    {
        $this->groupRepository->store_rule_order($request->orders);

        return $this->successResponse();
    }

    public function delete_rule(DeleteRuleValidate $request)
    {
        $this->groupRepository->delete_rule($request->id);

        return $this->successResponse();
    }

    public function upload_cover(UploadCoverValidate $request)
    {
        $result = $this->groupRepository->upload_cover($request->file('file'), $request->id, $request->user());
    
        return $this->successResponse($result);
    }

    public function store_name(StoreNameValidate $request)
    {
        $this->groupRepository->store_name($request->name, $request->id);

        return $this->successResponse();
    }

    public function store_description(StoreDescriptionValidate $request)
    {
        $request->mergeIfMissing([
            'description' => '',
        ]);

        $this->groupRepository->store_description($request->description, $request->id);

        return $this->successResponse();
    }

    public function store_category(StoreCategoryValidate $request)
    {
        $this->groupRepository->store_category($request->categories, $request->id);

        return $this->successResponse();
    }

    public function store_hashtag(StoreHashtagValidate $request)
    {
        $this->groupRepository->store_hashtag($request->hashtags, $request->id);

        return $this->successResponse();
    }

    public function store_type_private(StoreTypePrivate $request)
    {
        $this->groupRepository->store_type_private($request->id);

        return $this->successResponse();
    }

    public function store_setting(StoreSettingValidate $request)
    {
        $this->groupRepository->store_setting($request->validated(), $request->id);

        return $this->successResponse();
    }

    public function store_join(StoreJoinValidate $request)
    {
        $result = $this->groupRepository->store_join($request->id, $request->user());

        return $this->successResponse($result);
    }

    public function remove_join_request(RemoveJoinRequestValidate $request)
    {
        $this->groupRepository->remove_join_request($request->id);

        return $this->successResponse();
    }

    public function get_join_request(GetJoinRequestValidate $request)
    {
        $page = $request->query('page') ? $request->query('page') : 1;

        $result = $this->groupRepository->get_join_request($request->query('query'), $page, $request->id);

        return $this->successResponse($result);
    }

    public function accept_join_request(AcceptJoinRequestValidate $request)
    {
        $this->groupRepository->accept_join_request($request->id);

        return $this->successResponse();
    }

    public function accept_multi_join_request(AcceptMultiJoinRequestValidate $request)
    {
        $this->groupRepository->accept_multi_join_request($request->request_ids, $request->id);

        return $this->successResponse();
    }

    public function delete_join_request(DeleteJoinRequestValidate $request)
    {
        $this->groupRepository->delete_join_request($request->id);

        return $this->successResponse();
    }

    public function delete_multi_join_request(AcceptMultiJoinRequestValidate $request)
    {
        $this->groupRepository->delete_multi_join_request($request->request_ids, $request->id);

        return $this->successResponse();
    }

    public function get_my_post_pending(GetMyPostPendingValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->groupRepository->get_my_post_pending($request->id, $page, $request->user());

        return $this->successResponse($result);
    }

    public function delete_my_post_pending(DeleteMyPostPendingValidate $request)
    {
        $this->groupRepository->delete_my_post_pending($request->id);

        return $this->successResponse();
    }

    public function get_post_pending(GetPostPendingValidate $request)
    {
        $result = $this->groupRepository->get_post_pending($request->validated());

        return $this->successResponse($result);
    }

    public function delete_post_pending(DeletePostPendingValidate $request)
    {
        $this->groupRepository->delete_post_pending($request->id, $request->user());

        return $this->successResponse();
    }

    public function accept_post_pending(AcceptPostPendingValidate $request)
    {
        $this->groupRepository->accept_post_pending($request->id, $request->user());

        return $this->successResponse();
    }

    public function store_transfer_owner(StoreTranferValidate $request)
    {
        $this->groupRepository->store_transfer_owner($request->id, $request->user_id, $request->user());

        return $this->successResponse();
    }

    public function search_user_for_admin(SearchUserForAdminValidate $request)
    {
        $result = $this->groupRepository->search_user_for_admin($request->text, $request->id, $request->user());

        return $this->successResponse($result);
    }

    public function add_admin(AddAdminValidate $request)
    {
        $result = $this->groupRepository->add_admin($request->id, $request->user_id, $request->user());

        return $this->successResponse($result);
    }

    public function remove_admin(RemoveAdminValidate $request)
    {
        $this->groupRepository->remove_admin($request->id, $request->user());

        return $this->successResponse();
    }

    public function store_leave(StoreLeaveValidate $request)
    {
        $this->groupRepository->store_leave($request->id, $request->user());

        return $this->successResponse();
    }

    public function get_post(GetPostValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->groupRepository->get_post($request->id, $page, $request->user());

        return $this->successResponse($result);
    }

    public function get_post_with_hashtag(GetPostWithHashtagValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->groupRepository->get_post_with_hashtag($request->id, $request->name, $page, $request->user());

        return $this->successResponse($result);
    }

    public function get_media(GetPostValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->groupRepository->get_media($request->id, $page, $request->user());

        return $this->successResponse($result);
    }

    public function get_explore(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->groupRepository->get_explore($page, $request->user());

        return $this->successResponse($result);
    }

    public function get_your_feed(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->groupRepository->get_your_feed($page, $request->user());

        return $this->successResponse($result);
    }

    public function get_for_you(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->groupRepository->get_for_you($page, $request->user());

        return $this->successResponse($result);
    }

    public function get_all(GetGroupAllValidate $request)
    {
        $request->mergeIfMissing([
            'page' => 1,
            'keyword' => '',
            'category' => ''
        ]);
        $result = $this->groupRepository->get_all($request->only(['page', 'keyword', 'category']), $request->user());

        return $this->successResponse($result);
    }

    public function get_admin(GetAdminValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->groupRepository->get_admin($page, $request->id);

        return $this->successResponse($result);
    }

    public function get_members(GetMembersValidate $request)
    {
        $page = $request->query('page') ? $request->query('page') : 1;

        $result = $this->groupRepository->get_members($request->query('query'), $page, $request->id);

        return $this->successResponse($result);
    }

    public function remove_member(RemoveMemberValidate $request)
    {
        $this->groupRepository->remove_member($request->id);

        return $this->successResponse();
    }

    public function get_blocks(GetBlocksValidate $request)
    {
        $page = $request->query('page') ? $request->query('page') : 1;

        $result = $this->groupRepository->get_blocks($request->query('query'), $page, $request->id);

        return $this->successResponse($result);
    }

    public function store_block(StoreBlockValidate $request)
    {
        $this->groupRepository->store_block($request->id, $request->user_id);

        return $this->successResponse();
    }

    public function remove_block(RemoveBlockValidate $request)
    {
        $this->groupRepository->remove_block($request->id);

        return $this->successResponse();
    }

    public function store_notify_setting(StoreNotifySettingValidate $request)
    {
        $this->groupRepository->store_notify_setting($request->all(), $request->id, $request->user());

        return $this->successResponse();
    }

    public function store_pin(StorePinValidate $request)
    {
        $this->groupRepository->store_pin($request->id, $request->post_id, $request->action, $request->user());

        return $this->successResponse();
    }

    public function get_report_overview(AdminValidate $request)
    {
        $result = $this->groupRepository->get_report_overview($request->id);

        return $this->successResponse($result);
    }

    public function get_report_chart(AdminValidate $request)
    {
        $result = $this->groupRepository->get_report_chart($request->id);

        return $this->successResponse($result);
    }

    public function get_admin_manage_config(AdminValidate $request)
    {
        $result = $this->groupRepository->get_admin_manage_config($request->id, $request->user());

        return $this->successResponse($result);
    }

    public function get_user_manage_config(GroupValidate $request)
    {
        $result = $this->groupRepository->get_user_manage_config($request->id, $request->user());

        return $this->successResponse($result);
    }

    public function store_hide(StoreHideValidate $request)
    {
        $this->groupRepository->store_hide($request->id);

        return $this->successResponse();
    }

    public function store_open(StoreOpenValidate $request)
    {
        $this->groupRepository->store_open($request->id);

        return $this->successResponse();
    }

    public function get_manage_group(GetManageGroupValidate $request)
    {
        $page = $request->page ?? 1;
        
        $result = $this->groupRepository->get_manage_group($request->status, $page, $request->user());

        return $this->successResponse($result);
    }

    public function get_joined(Request $request)
    {
        $page = $request->page ?? 1;

        $result = $this->groupRepository->get_joined($page, $request->user());

        return $this->successResponse($result);
    }

    public function delete(DeleteValidate $request)
    {
        $this->groupRepository->delete($request->id);

        return $this->successResponse();
    }

    public function search_post(SearchPostValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->groupRepository->search_post($request->id, $request->query('query'), $page, $request->user());

        return $this->successResponse($result);
    }
}