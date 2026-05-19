<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;

class MailRecipient extends Model
{
    protected $fillable = [
        'type',
        'to',
        'params',
    ];
}
