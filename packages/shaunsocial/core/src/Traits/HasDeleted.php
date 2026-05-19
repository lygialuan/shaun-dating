<?php


namespace Packages\ShaunSocial\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasDeleted
{
    public function initializeHasDeleted()
    {
        $this->fillable[] = 'is_delete';
        $this->casts['is_delete'] = 'boolean';
    }

    public function doDeleted()
    {
        $this->update([
            'is_delete' => true
        ]);
    }

    public function isDeleted()
    {
        return $this->is_delete;
    }

    public static function bootHasDeleted()
    {
        static::addGlobalScope('deleted', function (Builder $builder) {
            $builder->where('is_delete', false);
        });
    }
}
