@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Page Categories')}}</h5>
        <a class="button admin_modal_ajax btn-filled-blue" data-url="{{route('admin.user_page.category.create')}}" href="javascript:void(0);">{{__('Create new')}}</a>
    </div>
    <div class="admin-card-body">
        <div class="admin-menu-table">
            <div class="admin-menu-table-header">
                <div class="admin-menu-table-col">{{__('Name')}}</div>
                <div class="admin-menu-table-col admin-menu-table-col-status">{{__('Active')}}</div>
                <div class="admin-menu-table-col admin-menu-table-col-action">{{__('Action')}}</div>
            </div>
            <div class="admin-menu-table-body" id="user_page_categories_list">
                @foreach ($categories as $category)
                <div class="admin-menu-table-body-wrap" data-id="{{ $category->id }}">
                    <div class="admin-menu-table-body-row">
                        <div class="admin-menu-table-col">
                            <span class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                            <span>{{$category->getTranslatedAttributeValue('name')}}</span>                                            
                        </div>
                        <div class="admin-menu-table-col admin-menu-table-col-status">
                            <span>
                                @if ($category->is_active)
                                    {{__('Yes')}}
                                @else
                                    {{__('No')}}
                                @endif
                            </span>                                            
                        </div>
                        <div class="actions-cell admin-menu-table-col admin-menu-table-col-action">
                            <a class="admin_modal_ajax" href="javascript:void(0)" data-url="{{route('admin.user_page.category.create',$category->id)}}">
                                {{__('Edit')}}
                            </a>
                            <a class="button-red admin_modal_confirm_delete" data-url="{{route('admin.user_page.category.delete',$category->id)}}">
                                {{__('Delete')}}
                            </a>
                        </div>
                    </div>
                    @if ($category->childs)
                        <div class="menu_child admin-menu-table-body-wrap" id="menu_child_{{ $category->id }}">                                       
                            @foreach ($category->childs as $child)
                                <div class="admin-menu-table-body-row" data-id="{{ $child->id }}">
                                    <div class="admin-menu-table-col admin-menu-table-col-name">
                                        <span class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                                        <span>{{$child->getTranslatedAttributeValue('name')}}</span>
                                    </div>
                                    <div class="admin-menu-table-col admin-menu-table-col-status">
                                        <span>
                                            @if ($child->is_active)
                                                {{__('Yes')}}
                                            @else
                                                {{__('No')}}
                                            @endif
                                        </span>                                            
                                    </div>
                                    <div class="actions-cell admin-menu-table-col admin-menu-table-col-action">
                                        <a class="admin_modal_ajax" href="javascript:void(0)" data-url="{{route('admin.user_page.category.create',$child->id)}}">
                                            {{__('Edit')}}
                                        </a>
                                        <a class="button-red admin_modal_confirm_delete" href="javascript:void(0)" data-url="{{route('admin.user_page.category.delete',$child->id)}}">
                                            {{__('Delete')}}
                                        </a>
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
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/translation.js') }}"></script>
<script src="{{ asset('admin/js/user_page.js') }}"></script>
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script>
    adminUserPage.initCategoriesList('{{route('admin.user_page.category.store_order')}}');
</script>
@endpush