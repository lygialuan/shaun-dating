@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="row">
    @foreach ($menus as $menu)
        <div class="col-md-6">
            <div class="admin-card">
                <div class="admin-card-top">
                    <h5 class="admin-card-top-title">{{$menu->name}}</h5>
                    <a class="btn-filled-blue admin_modal_ajax" data-url="{{route('admin.menu.create_item',$menu->id)}}" href="javascript:void(0);">{{__('Create new')}}</a>
                </div>
                <div class="admin-card-body">
                    <div class="admin-menu-table">
                        <div class="admin-menu-table-header">
                            <div class="admin-menu-table-col">
                                {{__('Item')}}
                            </div>
                            <div class="admin-menu-table-col admin-menu-table-col-action">
                                {{__('Action')}}
                            </div>
                        </div>
                        <div class="admin-menu-table-body menu-list-core" id="menu_list_{{$menu->id}}">
                            @foreach ($menuItems[$menu->id] as $menuItem)
                                <div class="admin-menu-table-body-wrap" data-id="{{ $menuItem->id }}">
                                    <div class="admin-menu-table-body-row">
                                        <div class="admin-menu-table-col">
                                            <span class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                                            <span class="{{$menuItem->is_active ? '' : 'text-black-50'}}">{{$menuItem->getTranslatedAttributeValue('name')}}</span>                                            
                                        </div>
                                        <div class="actions-cell admin-menu-table-col admin-menu-table-col-action">
                                            <a class="admin_modal_ajax" href="javascript:void(0)" data-url="{{route('admin.menu.create_item',['menu_id'=>$menu->id,'id'=>$menuItem->id])}}">
                                                {{__('Edit')}}
                                            </a>
                                            @if ($menuItem->canDelete())
                                                <a class="button-red admin_modal_confirm_delete" data-url="{{route('admin.menu.delete_item',$menuItem->id)}}" href="javascript:void(0);">
                                                    {{__('Delete')}}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($menuItem->childs)
                                        <div class="menu_child admin-menu-table-body-wrap" id="menu_child_{{$menuItem->id}}">                                       
                                            @foreach ($menuItem->childs as $child)
                                                <div class="admin-menu-table-body-row" data-id="{{ $child->id }}">
                                                    <div class="admin-menu-table-col admin-menu-table-col-name">
                                                        <span class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                                                        <span class="{{$child->is_active ? '' : 'text-black-50'}}">{{$child->getTranslatedAttributeValue('name')}}</span>
                                                    </div>
                                                    <div class="actions-cell admin-menu-table-col admin-menu-table-col-action">
                                                        <a class="admin_modal_ajax" href="javascript:void(0)" data-url="{{route('admin.menu.create_item',['menu_id'=>$menu->id,'id'=>$child->id])}}">
                                                            {{__('Edit')}}
                                                        </a>
                                                        @if ($child->canDelete())
                                                            <a class="button-red admin_modal_confirm_delete" data-url="{{route('admin.menu.delete_item',$child->id)}}" href="javascript:void(0);">
                                                                {{__('Delete')}}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach                                                    
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>         
                </div>
            </div>
        </div>
    @endforeach
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script src="{{ asset('admin/js/menu.js') }}"></script>
<script src="{{ asset('admin/js/translation.js') }}"></script>
<script>
    adminMenu.initListing('{{ route('admin.menu.store_order')}}');
</script>
@endpush