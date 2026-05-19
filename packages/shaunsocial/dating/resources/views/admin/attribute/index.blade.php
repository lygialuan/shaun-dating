@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ $title }}</h5>
        <a 
            class="button admin_modal_ajax btn-filled-blue" 
            data-url="{{route('admin.dating.attribute.create')}}" 
            href="javascript:void(0);">{{__('Create new')}}
        </a>
    </div>
    <div class="admin-card-body">
        <div class="admin-menu-table">
            <table class="admin-table table table-hover">
                <thead>
                    <th width="40"></th>
                    <th>
                        {{__('Name')}}
                    </th>
                    <th width="100">
                        {{__('Status')}}
                    </th>
                    <th width="250">
                        {{__('Actions')}}
                    </th>
                </thead>
                <tbody id="dating_attributes_list">
                    @foreach ($attributes as $attribute)
                        <tr data-id="{{$attribute->id}}">
                            <td>
                                <span role="button" class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                            </td>
                            <td>
                                <img width="24" class="img-fluid" src="{{$attribute->getIconUrl()}}">
                                {{$attribute->getTranslatedAttributeValue('name')}}
                            </td>
                            <td>
                                @if ($attribute->is_active)
                                    {{__('Yes')}}
                                @else
                                    {{__('No')}}
                                @endif
                            </td>
                            <td class="actions-cell" width="200">
                                <a class="admin_modal_ajax" href="javascript:void(0)" data-url="{{route('admin.dating.attribute.create', $attribute->id)}}">
                                    {{__('Edit')}}
                                </a>
                                @if  ($attribute->id != 1)
                                <a href="{{route('admin.dating.attribute.value', $attribute->id)}}">
                                    {{__('Manage Values')}}
                                </a>
                                <a class="button-red admin_modal_confirm_delete" href="javascript:void(0)" data-url="{{route('admin.dating.attribute.delete',$attribute->id)}}">
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
</div>
@stop   

@push('scripts-body')
<script src="{{ asset('admin/js/translation.js') }}"></script>
<script src="{{ asset('admin/js/dating.js') }}"></script>
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script>
    adminDating.initAttributesList('{{route('admin.dating.attribute.store_order')}}');
</script>
@endpush