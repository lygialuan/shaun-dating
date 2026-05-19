@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card-bar">
    <select id="page_id" class="form-select">
        @foreach ($pages as $item)
            <option @if ($item->id == $id) selected @endif value="{{$item->id}}">{{$item->getTranslatedAttributeValue('title')}}</option>
        @endforeach
    </select>
    <a class="btn-filled-blue admin_modal_ajax" data-url="{{ route('admin.layout.edit',[$id])}}" href="javascript:void(0);" id="">{{__('Edit Page Info')}}</a>
    <button type="button" id="save_change" class="btn-filled-blue">
        {{'Save change'}}
    </button>
</div>
<div class="row admin-layout-row">
    <input type="hidden" id="block_remove_ids"/>
    <div class="admin-layout-col page_content col-md-8">
        @if ($hasOneColumn)
            <div class="admin-card">           
                <div class="admin-card-top">
                    <h5 class="admin-card-top-title">{{__('Header & Footer')}}</h5>
                </div>
                <div class="layout-block-container row" >
                    <div class="layout-block-col-title col-12">{{__('Header Section')}}</div>
                    <div class="layout-block-col col-12 viewType" data-view="header">
                        <div id="centerContent_header" data-view="header" class="center">
                            @foreach ($contents['header']['center'] as $content)
                                @include('shaun_core::admin.partial.layout.content')
                            @endforeach
                        </div>
                    </div>
                    <div class="layout-block-col-title col-12">{{__('CONTENT AREA')}}</div>
                    <div class="layout-block-col col-12">
                        <div id="centerContent_area" class="text-center">
                            <div class="layout-block-col-placeholder">{{__('Content Block')}}</div>
                        </div>
                    </div>
                    <div class="layout-block-col-title col-12">{{__('Footer Section')}}</div>
                    <div class="layout-block-col col-12 viewType" data-view="footer">
                        <div id="centerContent_footer" data-view="footer" class="center">
                            @foreach ($contents['footer']['center'] as $content)
                                @include('shaun_core::admin.partial.layout.content')
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            @foreach ($viewTypes as $key => $name)
                <div class="admin-card">           
                    <div class="admin-card-top">
                        <h5 class="admin-card-top-title">{{$name}}</h5>
                    </div>
                    <div class="viewType layout-block-container row" data-view="{{$key}}">
                        <div class="layout-block-col-title">{{__('Top Section')}}</div>
                        <div class="col-12 layout-block-col">
                            <div id="topContent_{{$key}}" data-view="{{$key}}" class="top">
                                @foreach ($contents[$key]['top'] as $content)
                                    @include('shaun_core::admin.partial.layout.content')
                                @endforeach
                            </div>
                        </div>
                        <div class="col-7"><div class="layout-block-col-title">{{$key != 'mobile'?__('Center Content Section'):__('Main Content Section')}}</div></div>
                        @if ($key!='mobile')
                        <div class="col-5"><div class="layout-block-col-title">{{__('Right Content Section')}}</div></div>
                        @endif
                        <div class="layout-block-col {{$key!='mobile'?'col-7':'col-12'}}">
                            <div id="centerContent_{{$key}}" data-view="{{$key}}" class="center">
                                @foreach ($contents[$key]['center'] as $content)
                                    @include('shaun_core::admin.partial.layout.content')
                                @endforeach
                            </div>
                        </div>
                        <div class="col-5 layout-block-col {{$key!='mobile'?'':'d-none'}}">         
                            <div id="rightContent_{{$key}}" data-view="{{$key}}" class="right">
                                @foreach ($contents[$key]['right'] as $content)
                                    @include('shaun_core::admin.partial.layout.content')
                                @endforeach
                            </div>
                        </div>
                        <div class="layout-block-col-title">{{__('Bottom Section')}}</div>
                        <div class="col-12 layout-block-col">
                            <div id="bottomContent_{{$key}}" data-view="{{$key}}" class="bottom">
                                @foreach ($contents[$key]['bottom'] as $content)
                                    @include('shaun_core::admin.partial.layout.content')
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <div class="admin-layout-col col-md-4">
        <div class="admin-card">
            <div class="admin-card-top mb-3">
                <h5 class="admin-card-top-title">{{__('Available Widgets')}}</h5>
            </div>
            <div class="admin-card-bar mb-3">
                <div class="admin-card-search-bar-wrap mw-100">
                    <span class="material-symbols-outlined notranslate admin-card-search-bar-icon"> search </span>
                    <input type="text" value="" id="name" class="admin-card-search-bar-input form-control" placeholder="{{__('Search')}}"/>
                </div>
            </div>
            <div id="blockContent" class="available_widgets_list">
                @foreach ($blocks as $block)
                <div class="available_widgets_list_item drag" data-component="{{$block->component}}">
                    <div class="title">{{$block->title}}</div>
                    <div class="layout-block-col-item-actions" style="display: none;">
                        <a href="javascript:void(0);" class="content_edit" data-content="" data-id="0" data-position="" data-title="{{$block->title}}" data-package="{{$block->package}}" data-enable_title="1" data-class="{{$block->class}}" data-component="{{$block->component}}" data-role_access='["all"]' data-params="{}"><span class="layout-block-col-item-actions-icon material-symbols-outlined notranslate"> edit </span></a>
                        <a href="javascript:void(0);" class="content_remove" data-id="0"><span class="layout-block-col-item-actions-icon material-symbols-outlined notranslate"> close </span></a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/layout.js') }}"></script>
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script src="{{ asset('admin/js/translation.js') }}"></script>
<script>
    adminLayout.initEdit('{{ route('admin.layout.index')}}', '{{ route('admin.layout.edit_block')}}', '{{ route('admin.layout.store_blocks')}}');
</script>
@endpush
