@isset($breadcrumbs)
<section class="breadcrumb-section">
    <ul class="breadcrumb-list">
      @foreach ($breadcrumbs as $breadcrumb)
        @if (isset($breadcrumb['route']))
          <li>
            <a href="{{route($breadcrumb['route'], $breadcrumb['data'] ?? [])}}">{{$breadcrumb['title']}}</a>
            <span class="material-symbols-outlined notranslate breadcrumb-arrow-right"> keyboard_arrow_right </span>
          </li>
        @else
          <li>{{$breadcrumb['title']}}</li>
        @endif
      @endforeach
    </ul>
</section>
@endisset