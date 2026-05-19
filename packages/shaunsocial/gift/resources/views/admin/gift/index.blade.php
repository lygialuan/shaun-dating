@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ $title }}</h5>
        <a 
            class="button admin_modal_ajax btn-filled-blue" 
            data-url="{{route('admin.gift.create')}}" 
            href="javascript:void(0);">{{__('Create new')}}
        </a>
    </div>
     <form method="get">
        <div class="admin-card-bar">
            <div class="admin-card-search-bar-wrap">
                <span class="material-symbols-outlined notranslate admin-card-search-bar-icon"> search </span>
                <input type="text" value="{{$name}}" name="name" class="admin-card-search-bar-input form-control" placeholder="{{__('Search')}}"/>
            </div>
            <button type="submit" class="btn-filled-blue" href="javascript:void(0);" id="searchUser">{{__('Search')}}</button>
        </div>
    </form>
    <div class="admin-card-body">
        <div class="admin-menu-table">
            <table class="admin-table table table-hover">
                <thead>
                    <th width="40"></th>
                    <th>
                        {{__('Name')}}
                    </th>
                    <th>
                        {{__('Price')}}
                    </th>
                    <th>
                        {{__('Icon')}}
                    </th>
                    <th width="100">
                        {{__('Status')}}
                    </th>
                    <th width="250">
                        {{__('Actions')}}
                    </th>
                </thead>
                <tbody id="gift_list">
                    @foreach ($gifts as $gift)
                        <tr data-id="{{$gift->id}}">
                            <td>
                                <span role="button" class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                            </td>
                            <td>
                                {{$gift->getTranslatedAttributeValue('name')}}
                            </td>
                            <td>
                                {{$gift->getPrice()}}
                            </td>
                            <td>
                                <img width="50" class="img-fluid" src="{{$gift->getIconUrl()}}">
                            </td>
                            <td>
                                @if ($gift->is_active)
                                    {{__('Yes')}}
                                @else
                                    {{__('No')}}
                                @endif
                            </td>
                            <td class="actions-cell" width="200">
                                <a class="admin_modal_ajax" href="javascript:void(0)" data-url="{{route('admin.gift.create', $gift->id)}}">
                                    {{__('Edit')}}
                                </a>
                                <a class="button-red admin_modal_confirm_delete" href="javascript:void(0)" data-url="{{route('admin.gift.delete',$gift->id)}}">
                                    {{__('Delete')}}
                                </a>
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
<script src="{{ asset('admin/js/gift.js') }}"></script>
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script>
    adminGift.initStoreOrder('{{route('admin.gift.store_order')}}');
</script>
@endpush