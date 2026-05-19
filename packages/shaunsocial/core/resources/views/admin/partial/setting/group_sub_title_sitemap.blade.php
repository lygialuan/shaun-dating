<div class="control-help control-help-text mb-3">
    @php   
        $sitemapId = \Packages\ShaunSocial\Core\Models\Key::getValue('sitemap_id');
    @endphp
    @if (! $sitemapId)
        {{__("The site map URL is not available yet because the cron job has not been configured yet. Please check and configure at System settings -> Tasks")}}
    @else
        {{__("This is URL of your site map")}} <a target="_blank" href="{{route('web.sitemap.index')}}">{{route('web.sitemap.index')}}</a>
    @endif 
</div>