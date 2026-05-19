<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Sitemap extends Model
{
    protected $fillable = [
        'url',
        'changefreq'
    ];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($sitemap) {
            $sitemap->changefreq = setting('sitemap.schedule');
        });
    }
}
