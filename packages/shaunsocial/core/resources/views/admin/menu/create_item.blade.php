<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="menu_item_form" method="post" enctype="multipart/form-data" action="{{ route('admin.menu.store_item')}}">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $menuItem->id }}" />
            <input type="hidden" name="menu_id" value="{{$menu->id}}" />
            <input type="hidden" name="is_core" value="{{$menuItem->is_core ? 1 : 0}}" />
            @if ( $menuItem->is_core)
                <input type="hidden" name="url" value="{{$menuItem->url}}" />
            @endif
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" @if ($menuItem->id) readonly @endif value="{{$menuItem->getTranslatedAttributeValue('name') }}" type="text">
                @if ($menuItem->id)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$menuItem->getTable(),'name',$menuItem->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>
            @if (! $menuItem->is_core)
                <div id="url_content" @if ($menuItem->is_header) style="display:none;" @endif class="form-group">
                    <label class="control-label">{{__('Url')}}</label>
                    <input class="form-control" id="url" name="url" value="{{$menuItem->url }}" type="text">
                    <p id="url_help" @if ($menuItem->type == 'outbound') style="display:none;" @endif class="help">
                        {{setting('site.url')}}/<span id="url_prefix">{{$menuItem->url }}</span>
                    </p>
                </div>
            @endif
            @if (!count($menuItem->childs) && $menu->support_child)
                <div id="parent_content" @if ($menuItem->is_header) style="display:none;" @endif class="form-group">
                    <label class="control-label">{{__('Parent')}}</label>
                    <select id="parent_id" name="parent_id" class="form-select">
                        <option value="0"></option>
                        @foreach($menuItemParents as $menuItemParent)
                            <option @if ($menuItemParent->id == $menuItem->parent_id) selected @endif value="{{$menuItemParent->id}}">{{$menuItemParent->getTranslatedAttributeValue('name')}}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if (! $menuItem->is_core)
                <div id="type_content" @if ($menuItem->is_header) style="display:none;" @endif class="form-group">
                    <label class="control-label">{{__('Type')}}</label>
                    <select id="type" name="type" class="form-select">
                        <option @if ($menuItem->type == 'internal') selected @endif value="internal">{{__('Internal link')}}</option>
                        <option @if ($menuItem->type == 'outbound') selected @endif value="outbound">{{__('Outbound link')}}</option>
                    </select>
                </div>
            @endif
            @if ($menu->support_icon)
                <div class="form-group">
                    <label class="control-label">{{__('Icon')}}</label>
                    <input class="form-control" name="icon" type="file">
                    <?php $image = $menuItem->getIcon(); ?>
                    @if ($image) 
                        <img src="{{$image}}" class="mb-1" style="max-width: 50px"/>
                    @endif
                </div>
            @endif
            @if (! $menuItem->is_core)
                @if ($menu->id != config('shaun_core.menu.mobile_menu_id'))
                    <div class="form-group" @if ($menuItem->is_header) style="display:none;" @endif id="new_tab_content">
                        <label class="control-label">{{__('Open new tab')}}</label>
                        <div class="form-check form-switch">
                            <input  name="is_new_tab" class="form-check-input" type="checkbox" {{ $menuItem->is_new_tab ? 'checked' : ''}} value="1">
                        </div>
                    </div>
                @endif
                @if ($menu->support_child)
                    <div class="form-group">
                        <label class="control-label">{{__('Header')}}</label>              
                        <div class="form-check form-switch">
                            <input id="is_header" name="is_header" class="form-check-input" type="checkbox" {{ $menuItem->is_header ? 'checked' : ''}} value="1">
                        </div>
                    </div>
                @endif
            @endif
            @include('shaun_core::admin.partial.role.access',['fieldName'=> 'role_access','values'=>$menuItem->getRoleAccess()])
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>        
                <div class="form-check form-switch">
                    <input  name="is_active" class="form-check-input" type="checkbox" {{ $menuItem->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="menu_item_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminMenu.initCreateItem();
</script>