<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\ModelMap;

trait HasSource
{
    protected $source = null;

    public function initializeHasSource()
    {
        $this->fillable[] = 'source_type';
        $this->fillable[] = 'source_id';
        $this->fillable[] = 'has_source';
        $this->fillable[] = 'source_privacy';
    }

    public function supportSource()
    {
        return true;
    }

    public function getSourceModel()
    {
        return ModelMap::getModel($this->source_type);
    }

    public function getSource()
    {
        if (! $this->source && $this->source_type && $this->source_id) {
            $model = $this->getSourceModel();
            $this->source = $model::findByField('id', $this->source_id);
        }

        return $this->source;
    }

    public function setSource($source)
    {
        $this->source = $source;
    }

    public function getSourceResource()
    {
        if ($this->has_source) {
            $model = $this->getSourceModel();
            $resourceClass = $model::getSourceResourceClass();
    
            return new $resourceClass($this->getSource());
        }
        
        return null;
    }

    public function addToSource()
    {
        if ($this->has_source) {
            $source = $this->getSource();
            if ($source) {
                $source->recentObjectForSource($this);
            }
        }
    }

    public function checkShowWithSource($viewerId)
    {
        if ($this->has_source) {
            $source = $this->getSource();
            if ($source) {
                return $source->checkShowWithSource($viewerId);
            }
            return false;
        }

        return true;
    }

    public function checkNotificationWithSource($viewerId)
    {
        if ($this->has_source) {
            $source = $this->getSource();
            if ($source) {
                return $source->checkNotificationWithSource($viewerId);
            }
            return false;
        }

        return true;
    }

    public function getSourceMemberLabel()
    {
        if ($this->has_source) {
            $source = $this->getSource();
            if ($source) {
                return $source->getSourceMemberLabel($this->user_id);
            }
        }

        return '';
    }

    public function isSourceOwner($source)
    {
        return ($this->source_type == $source->getSubjectType() && $this->source_id == $source->id);
    }

    public function canEditWithSource($viewerId)
    {
        if ($this->has_source) {
            $source = $this->getSource();
            if ($source) {
                return $source->canEditWithSource($this, $viewerId);
            }
        }

        return false;
    }

    public function canDeleteWithSource($viewerId)
    {
        if ($this->has_source) {
            $source = $this->getSource();
            if ($source) {
                return $source->canDeleteWithSource($this, $viewerId);
            }
        }

        return false;
    }

    public function isAdminOfSource($viewerId)
    {
        if ($this->has_source) {
            $source = $this->getSource();
            if ($source) {
                return $source->isAdminOfSource($viewerId);
            }
            return false;
        }

        return true;
    }


    public function canCommentWithSource($viewerId)
    {
        if ($this->has_source) {
            $source = $this->getSource();
            if ($source) {
                return $source->canCommentWithSource($this, $viewerId);
            }
            return false;
        }

        return true;
    }

    public function canViewWithSource($viewerId)
    {
        if ($this->has_source) {
            $source = $this->getSource();
            if ($source) {
                return $source->canViewWithSource($this, $viewerId);
            }
            return false;
        }

        return true;
    }

    public function deleteWithSource()
    {
        if ($this->has_source) {
            $source = $this->getSource();
            if ($source) {
                $source->deleteWithSource($this);
            }
        }
    }

    public function addStatisticWithSource($type, $viewer)
    {
        if ($this->has_source) {
            $source = $this->getSource();
            if ($source) {
                $source->addStatisticWithSource($type, $viewer, $this);
            }
        }
    }

    public function doHashTagPostWithSource($type, $data)
    {
        if ($this->has_source) {
            $source = $this->getSource();
            if ($source) {
                $source->doHashTagPostWithSource($type, $data, $this);
            }
        }
    }
}
