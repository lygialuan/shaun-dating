@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">    
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Reports Categories')}}</h5>
        <a class="btn-filled-blue admin_modal_ajax dark" data-url="{{route('admin.report.create_category')}}" href="javascript:void(0);">{{__('Create new')}}</a>
    </div>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th width="40"></th>
                <th>
                    {{__('Name')}}
                </th>
                <th>
                    {{__('Active')}}
                </th>
                <th>
                    {{__('Action')}}
                </th>
            </thead>
            <tbody id="categories_list">
                @foreach ($categories as $category)
                <tr data-id="{{$category->id}}">
                    <td>
                        <span role="button" class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                    </td>
                    <td>
                    {{$category->getTranslatedAttributeValue('name')}}
                    </td>
                    <td>
                        @if ($category->is_active)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a class="admin_modal_ajax" href="javascript:void(0)" data-url="{{route('admin.report.create_category',$category->id)}}">
                            {{__('Edit')}}
                        </a>
                        @if ($category->canDelete())
                            <a class="button-red admin_modal_confirm_delete" data-url="{{route('admin.report.delete_category',$category->id)}}" type="button">
                                {{__('Delete')}}
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script src="{{ asset('admin/js/report.js') }}"></script>
<script src="{{ asset('admin/js/translation.js') }}"></script>
<script>
    adminReport.initCategoriesListing('{{route('admin.report.store_category_order')}}');
</script>
@endpush