<?php


namespace Packages\ShaunSocial\Core\Traits;

use Illuminate\Support\Facades\App;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Models\Translation;

trait HasTranslations
{
    public function initializeHasTranslations()
    {
        $this->with[] = 'translations';
    }

    public static function bootHasTranslations()
    {
        static::created(function ($model) {
            if (! $model->translatable()) {
                return;
            }

            $model->createTranslations();
        });

        static::deleted(function ($model) {
            if (! $model->translatable()) {
                return;
            }

            $model->deleteAllAttribute();
        });
    }

    public function translations()
    {
        return $this->hasMany(Translation::class, 'foreign_key', $this->getKeyName())
            ->where('table_name', $this->getTable());
    }

    public function createTranslations($keyIgnore = null)
    {
        $languages = Language::getAll();
        $translatableAttributes = $this->getTranslatableAttributes();

        foreach ($translatableAttributes as $attribute) {
            $data = $languages->mapWithKeys(function ($item, $key) use ($attribute) {
                return [$item->key => $this->{$attribute}];
            })->toArray();
            if ($keyIgnore) {
                unset($data[$keyIgnore]);
            }
            $this->saveAttributeTranslations($attribute, $data);
        }
    }

    public function createTranslationsWithKey($key) 
    {
        $translatableAttributes = $this->getTranslatableAttributes();
        foreach ($translatableAttributes as $attribute) {
            $this->{$attribute} = $this->getTranslatedAttributeValue($attribute, $key);
        }

        $this->createTranslations($key);
    }

    public function saveAttributeTranslations($attribute, $translations)
    {
        $data = [];
        foreach ($translations as $locale => $value) {
            $data[] = [
                'table_name' => $this->getTable(),
                'column_name' => $attribute,
                'foreign_key' => $this->getKey(),
                'locale' => $locale,
                'value' => $value ? $value : '',
            ];
        }
        Translation::insert($data);
    }

    public function saveAttributeTranslation($attribute, $data, $locale)
    {
        $translation = $this->translations()->where('column_name', $attribute)->where('locale', $locale)->first();
        if ($translation) {
            $translation->update(['value' => $data]);
        }
    }

    public function updateTranslations($locale)
    {
        $translatableAttributes = $this->getTranslatableAttributes();
        foreach ($translatableAttributes as $attribute) {
            $this->saveAttributeTranslation($attribute, $this->{$attribute}, $locale);
        }
    }

    public function translatable()
    {
        if (isset($this->translatable) && $this->translatable == false) {
            return false;
        }

        return ! empty($this->getTranslatableAttributes());
    }

    public function getTranslatableAttributes()
    {
        return property_exists($this, 'translatable') ? $this->translatable : [];
    }

    public function deleteAllAttribute()
    {
        $this->translations()
            ->whereIn('column_name', $this->getTranslatableAttributes())
            ->delete();
    }

    public function getTranslatedAttributeValue($attribute, $locale = null)
    {
        if (! $this->id) {
            return '';
        }

        if (! $locale) {
            $locale = $locale = App::getLocale();
        }

        $translation = $this->translations->first(function ($value, $key) use ($attribute, $locale) {
            return $value->column_name == $attribute && $value->locale == $locale;
        });

        return $translation ? $translation->value : '';
    }
}
