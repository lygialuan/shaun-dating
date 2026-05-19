@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__("Manage Values of ':attributeName'", ['attributeName' => $attribute->name])}}</h5>
        <a 
            class="button admin_modal_ajax btn-filled-blue" 
            data-url="{{route('admin.dating.interest_attribute.value.create', ['attribute_id' => $attributeId])}}" 
            href="javascript:void(0);"
        >
            {{__('Create new')}}
        </a>
    </div>
    
    <form method="get">
        <div class="admin-card-bar">
            <select name="status" class="form-select">
                @foreach ($statusArray as $value=> $statusName)
                    <option @if ($value == $status) selected @endif value="{{$value}}">{{$statusName}}</option>
                @endforeach
            </select>

            <button type="submit" class="btn-filled-blue" href="javascript:void(0);" id="searchUser">{{__('Search')}}</button>
        </div>
    </form>
    <div class="admin-card-body table-responsive">
        <form id="listing_tag_form" method="POST" action="{{route('admin.dating.interest_attribute.value.store_manage')}}">
            {{ csrf_field() }}
                <input type="hidden" id="action" name="action">
            <table class="admin-table table table-hover">
                <thead>
                    <th width="30">
                        <input class="form-check-input col-check check_all" type="checkbox">
                    </th>
                    <th>
                        {{__('Name')}}
                    </th>
                    <th>
                        {{__('Status')}}
                    </th>
                    <th width="150">
                        {{__('Actions')}}
                    </th>
                </thead>
                <tbody>
                    @foreach ($attributeValues as $attributeValue)
                    <tr>
                        <td width="30">
                            <input class="form-check-input col-check check_item" type="checkbox" name="ids[]" value="{{$attributeValue->id}}" >
                        </td>
                        <td>
                            {{$attributeValue->getTranslatedAttributeValue('name')}}
                        </td>
    
                        <td>
                            @if ($attributeValue->is_active)
                                {{__('Yes')}}
                            @else
                                {{__('No')}}
                            @endif
                        </td>
    
                        <td class="actions-cell" width="150">
                            <a 
                                class="admin_modal_ajax" 
                                href="javascript:void(0)" 
                                data-url="{{route('admin.dating.interest_attribute.value.create',['id' => $attributeValue->id, 'attribute_id' => $attributeId])}}"
                            >
                                {{__('Edit')}}
                            </a>
                            <a 
                                class="button-red admin_modal_confirm_delete" 
                                href="javascript:void(0)" 
                                data-url="{{route('admin.dating.interest_attribute.value.delete',['id' => $attributeValue->id, 'attribute_id' => $attributeId])}}"
                            >
                                {{__('Delete')}}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $attributeValues->withQueryString()->links('shaun_core::admin.partial.paginate') }}
            <div class="form-actions">
                <button type="button" id="delete_button" class="btn-filled-red">
                    {{__('Delete')}}
                </button>
                <button type="button" id="active_button" class="btn-filled-blue">
                    {{__('Active')}}
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/hashtag.js') }}"></script>
<script src="{{ asset('admin/js/translation.js') }}"></script>
<script src="{{ asset('admin/js/dating.js') }}"></script>
<script>
    adminTranslate.add({
        'confirm_delete_attribute_value' : '{{addslashes(__('Are you sure you want to delete these attribute values? All their content that they created will be deleted. This cannot be undone!'))}}',
        'confirm_active_attribute_value' : '{{addslashes(__('Are you sure you want to active these attribute values?'))}}',
        'confirm_pending_attribute_value' : '{{addslashes(__('Are you sure you want to approve these attribute values?'))}}',
    });
    adminCore.initCheckAll();
    adminHashtag.initListing('{{route('admin.hashtag.store_active')}}', '{{route('admin.hashtag.index')}}');
    adminDating.initAttributeValueListing();
</script>
@endpush