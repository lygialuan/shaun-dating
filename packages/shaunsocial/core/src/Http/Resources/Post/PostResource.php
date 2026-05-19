<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Post;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Http\Resources\ContentWarning\ContentWarningCategoryResource;

class PostResource extends JsonResource
{
    use Utility;

    public function toArray($request)
    {
        $viewer = $request->user();
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = $viewer ? $viewer->isModerator() : false;
        $parent = $this->getParent();
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');
        $canViewContent = $this->canShowContent($viewerId);
        $refCode = ($viewer ? $viewer->ref_code : '');
        if ($refCode) {
            $this->setRefCode($refCode);
        }
        $href = $this->getHref();

        if ($this->isSimpleResource()) {
            return [
                'id' => $this->id,
                'user' => $this->getUserResource(),
                'type' => $this->type,
                'content' => $this->makeContentHtml($this->getMentionContent($viewer)),
                'og_image' => $this->getOgImage(),
                'ref_code' => $this->getRefCode(),
                'content_warning_categories' => ContentWarningCategoryResource::collection($this->getContentWarningCategories()),
            ];
        }

        $canCreatePostPaidContent = $this->getUser()->canCreatePostPaidContent();

        return [
            'id' => $this->id,
            'type' => $this->type,
            'content' => $this->makeContentHtml($this->getMentionContent($viewer)),
            'items' => $canViewContent ? ItemResource::collection($this->getItems()) : null,
            'like_count' => $this->like_count,
            'comment_count' => $this->comment_count + $this->reply_count,
            'created_at' => $this->created_at->setTimezone($timezone)->diffForHumans(),
            'user' => $this->getUserResource(),
            'is_liked' => $this->getLike($viewerId) ? true : false,
            'mentions' => $this->getMentionUsersResource($viewer),
            'is_bookmarked' => $this->getBookmark($viewerId) ? true : false,
            'parent' => $parent ? new PostResource($parent) : null,
            'enable_notify' => $this->getNotificationStop($viewerId) ? false : true,
            'canDelete' => $this->canDelete($viewerId) || $isAdmin,
            'href' => $href,
            'canEdit' => $this->canEdit($viewerId) || $isAdmin,
            'isEdited' => $this->is_edited ? true : false,
            'created_at_full' => $this->created_at->setTimezone($timezone)->toDateTimeString(),
            'create_at_timestamp' => $this->created_at->timestamp,
            'is_ads' => setting('shaun_advertising.enable') && $this->isAdvertising(),
            'canBoot' => setting('shaun_advertising.enable') && $this->canBoot($viewerId),
            'comment_privacy' => $this->comment_privacy,
            'canComment' => $this->canComment($viewerId) || $isAdmin,
            'canChangeCommentPrivacy' => $this->canChangeCommentPrivacy($viewerId),
            'content_warning_categories' => ContentWarningCategoryResource::collection($this->getContentWarningCategories()),
            'canChangeContentWarning' => $this->canChangeContentWarning($viewerId),
            'canReport' => $this->canReport($viewerId),
	        'view_count' => $this->view_count,
            'engagement_count' => $this->like_count + $this->comment_count,
			'source' => $this->getSourceResource(),
            'inSource' => $this->getIn('source'),
            'type_box_label' => $this->getTypeBoxLabel(),
            'type_label' => $this->getTypeLabel(),
            'is_pin' => $this->isPin(),
            'canShare' => $this->canShare($viewerId),
            'canPin' => $this->canPin($viewerId),
            'canUnPin' => $this->canUnPin($viewerId),
            'canPinProfile' => $this->canPinProfile($viewer),
            'canUnPinProfile' => $this->canUnPinProfile($viewer),
            'canPinHome' => $this->canPinHome($viewer),
            'canUnPinHome' => $this->canUnPinHome($viewer),
            'stop_comment' => $this->stop_comment,
            'canStopComment' =>  $this->canStopComment($viewerId) || $isAdmin,
            'thumb' => $this->getThumb(),
            'canViewContent' => $canViewContent,
            'is_paid' => $this->is_paid,
            'content_amount' => $this->content_amount,
            'paid_type' => $this->paid_type,
            'paid_item_content' => $this->getPaidItemContent(),
            'canSendMessage' => $this->canSendMessage($viewerId),
            'og_image' => $this->getOgImage(),
            'ref_code' => $this->getRefCode(),
            'owner_ref_code' => $this->getUser()->ref_code,
            'earn_amount' => $this->earn_amount,
            'canChangePaidContent' => $this->canChangePaidContent($viewerId),
            'ownerIsCreator' => $canCreatePostPaidContent,
            'canShareEarn' => $canCreatePostPaidContent && $this->is_paid && $viewerId && $viewerId != $this->user_id && setting('shaun_paid_content.commission_referral'),
            'canContentTranslate' => $this->supportContentTranslate('content') && $viewerId != $this->user_id,
        ];
    }
}
