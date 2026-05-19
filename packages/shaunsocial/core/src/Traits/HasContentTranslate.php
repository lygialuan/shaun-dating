<?php


namespace Packages\ShaunSocial\Core\Traits;

trait HasContentTranslate
{
    public function supportContentTranslate($field)
    {
        $fields = $this->getContentLanguageFieldAttributes();

        return setting('content_translate.enable') && in_array($field, $fields) && trim(clearDataForContentTranslate($this->getContentForTranslate($field)));
    }

    public function getContentForTranslate($field)
    {
        $fields = $this->getContentLanguageFieldAttributes();

        if (in_array($field, $fields)) {
            $content = $this->{$field};
            if (method_exists($this, 'supportMention')) {
                $content = $this->getMentionContent(null);
            }

            return $content;
        }

        return '';
    }

    public function getContentLanguageFieldAttributes()
    {
        return property_exists($this, 'contentLanguageFields') ? $this->contentLanguageFields : [];
    }
}
