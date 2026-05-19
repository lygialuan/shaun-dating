<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\ModelMap;

trait HasSubject
{
    protected $subject = null;

    public function initializeHasSubject()
    {
        $this->fillable[] = 'subject_type';
        $this->fillable[] = 'subject_id';
    }

    public function getSubjectModel()
    {
        return ModelMap::getModel($this->subject_type);
    }

    public function getSubject()
    {
        if (! $this->subject) {
            $model = $this->getSubjectModel();
            $this->subject = $model::findByField('id', $this->subject_id);
        }

        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getSubjectResource()
    {
        $model = $this->getSubjectModel();
        $resourceClass = $model::getResourceClass();
        if (! $resourceClass) {
            return null;
        }
        return new $resourceClass($this->getSubject());
    }

    public function checkHasSubject($subject) 
    {
        return $this->subject_type == $subject->getSubjectType() && $this->subject_id == $subject->id;
    }

    static public function findSubject($subject)
    {
        return self::where('subject_id', $subject->id)->where('subject_type', $subject->getSubjectType())->first();
    }
}
