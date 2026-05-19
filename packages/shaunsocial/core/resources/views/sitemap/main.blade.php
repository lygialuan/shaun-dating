<?php echo '<?xml version="1.0" encoding="UTF-8"?>';?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($files as $file)
        <sitemap>
            <loc>{{$file->getUrl()}}</loc>
            <lastmod>{{$file->created_at->isoFormat('YYYY-MM-DD')}}</lastmod>
        </sitemap>
    @endforeach
</sitemapindex>