<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\ModelMap;

trait HasObject
{
    protected $object = null;

    public function initializeHasObject()
    {
        $this->fillable[] = 'object_type';
        $this->fillable[] = 'object_id';
    }

    public function getSubjectModel()
    {
        return ModelMap::getModel($this->object_type);
    }

    public function getSubject()
    {
        if (! $this->object) {
            $model = $this->getSubjectModel();
            $this->object = $model::findByField('id', $this->object_id);
        }

        return $this->object;
    }

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function getSubjectResource()
    {
        $model = $this->getSubjectModel();
        $resourceClass = $model::getResourceClass();

        return new $resourceClass($this->getSubject());
    }
}
