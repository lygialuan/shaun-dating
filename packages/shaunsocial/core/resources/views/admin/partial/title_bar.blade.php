@isset($title)
  <section class="admin-title-section">
      <h1 class="admin-title">
        {{$title}}
      </h1>
      <div class="admin-title-action">
        <a class="btn-filled-blue" href="{{setting('site.url')}}" target="_blank">{{__('Visit site')}}</a>
        <a class="btn-filled-blue" href="{{route('admin.dashboard.clear_cache')}}">{{__('Clear cache')}}</a>
      </div>
  </section>
@endisset
