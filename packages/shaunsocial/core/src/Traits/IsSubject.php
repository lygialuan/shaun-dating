<?php


namespace Packages\ShaunSocial\Core\Traits;

trait IsSubject
{
    public function getSubjectType()
    {
        return $this->getTable();
    }

    public function isSubject()
    {
        return true;
    }

    public static function getResourceClass()
    {
        return '';
    }

    public function getTitle()
    {
        return '';
    }

    public function getHref()
    {
        return '';
    }

    public function getAdminHref()
    {
        return $this->getHref();
    }

    public function getOgImage()
    {
        return '';
    }

    public function canShareProfile($userId)
    {
        return true;   
    }

    public function setSimpleResuouce($isSimple)
    {

    }

    public function isSimpleResource()
    {
        return false;
    }

    public function canDeleteSubject()
    {
        return true;
    }
}
