<nav id="adminNavbarMain" class="admin-navbar">
    <a href="javascript:void(0);" class="toggle-sidebar-menu d-lg-none" id="toggleSidebarMenu"><span class="material-symbols-outlined notranslate"> menu </span></a>
    <div class="admin-navbar-search-global">
        <span class="material-symbols-outlined notranslate admin-navbar-search-global-icon"> search </span>
        <input type="text" class="admin-navbar-search-global-input form-control" id="global-search" placeholder="{{__('Search')}}">
        <div id="display-suggestion"></div>
    </div>
    <div class="admin-navbar-avatar">
        <a target="_blank" href="{{auth()->user()->getHref()}}">
            <img src="{{auth()->user()->getAvatar()}}" class="rounded-full" />
        </a>
    </div>
</nav>
@push('scripts-body')
<script>
    adminCore.initLeftSidebarMenu();
    adminCore.initSearch('{{route('admin.setting.suggest')}}');
    // adminCore.scrollHeaderAdmin();
</script>
@endpush