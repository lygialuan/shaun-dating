<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasTranslations;

class MailTemplate extends Model
{
    use HasCacheQueryFields;
    use HasTranslations;

    protected $translatable = [
        'subject',
        'content',
    ];

    protected $cacheQueryFields = [
        'type',
    ];

    protected $fillable = [
        'subject',
        'content',
        'package',
    ];

    public function getKeyNameTranslate()
    {
        return 'MAIL_'.strtoupper($this->type).'_NAME';
    }
}
