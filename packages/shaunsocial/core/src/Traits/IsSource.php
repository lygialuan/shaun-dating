<?php


namespace Packages\ShaunSocial\Core\Traits;

trait IsSource
{
    public function isSource()
    {
        return true;
    }

    public function getSourcePrivacy()
    {
        return 0;
    }

    public function checkPermissionPost($viewerId)
    {
        return false;
    }

    public static function getResourceClass()
    {
        return '';
    }

    public function recentObjectForSource($subject)
    {
         
    }

    public function checkShowWithSource($viewerId)
    {
        return true;
    }

    public function getSourceMemberLabel($userId)
    {
        return '';
    }

    public function canEditWithSource($subject, $userId)
    {
        return false;
    }

    public function canDeleteWithSource($subject, $userId)
    {
        return false;
    }

    public function canCommentWithSource($subject, $userId)
    {
        return false;
    }

    public function canViewWithSource($subject, $userId)
    {
        return false;
    }

    public function checkNotificationWithSource($userId)
    {
        return true;
    }

    public function deleteWithSource($subject)
    {
        
    }

    public function addStatisticWithSource($type, $viewer, $subject)
    {
        
    }

    public function doHashTagPostWithSource($type, $data, $post)
    {

    }

    public function isAdminOfSource($userId)
    {
        return false;
    }

}
