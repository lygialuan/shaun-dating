<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\History;

trait HasHistory
{
    public function supportHistory()
    {
        return true;
    }

    public function initializeHasHistory()
    {
        $this->fillable[] = 'is_edited';
        $this->casts['is_edited'] = 'boolean';
    }

    public static function bootHasHistory()
    {
        static::deleted(function ($model) {
            History::where('subject_type', $model->getSubjectType())->where('subject_id', $model->id)->delete();
        });
    }

    public function getHistoryContentFirst()
    {
        return '';
    }
}
