<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Key;
use Packages\ShaunSocial\Core\Models\StorageFile;

class SitemapController extends Controller
{
    public function index(Request $request)
    {   
        $sitemapId = Key::getValue('sitemap_id');
        if (setting('sitemap.enable') && $sitemapId) {
            $file = StorageFile::findByField('id', $sitemapId);
            $xml = Cache::remember('sitemap_file_'.$sitemapId, config('shaun_core.cache.time.model_query'), function () use ($file) {
                if ($file->service_key == 'public') {
                    return file_get_contents(storage_path('app/public/').$file->storage_path);
                }
                return file_get_contents($file->getUrl());
            });
            return response($xml)->header('Content-Type', 'application/xml');
        }
        return view('shaun_core::app'); 
    }
}
