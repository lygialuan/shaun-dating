@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ $title }}</h5>
        <a class="button admin_modal_ajax btn-filled-blue" data-url="{{route('admin.country.create')}}" href="javascript:void(0);">{{__('Create new')}}</a>
    </div>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th width="40"></th>
                <th>
                    {{__('ISO')}}
                </th>
                <th>
                    {{__('Name')}}
                </th>
                <th width="150">
                    {{__('Number of States')}}
                </th>
                <th width="100">
                    {{__('Active')}}
                </th>
                <th width="200">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody id="countries_list">
                @foreach ($countries as $country)
                <tr data-id="{{$country->id}}">
                    <td>
                        <span role="button" class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                    </td>
                    <td>
                        {{$country->country_iso}} 
                    </td>
                    <td>
                        {{$country->getTranslatedAttributeValue('name')}}
                    </td>     
                    <td>
                        {{$country->state_count}} 
                    </td>
                    <td>
                        @if ($country->is_active)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>    
                    <td class="actions-cell" width="150">
                        <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.country.create',$country->id)}}">
                            {{__('Edit')}}
                        </a>
                        <a href="{{route('admin.country.state.index',$country->id)}}">
                            {{__('Manage States')}}
                        </a>
                        <a class="button-red admin_modal_confirm_delete" href="javascript:void(0);" data-url="{{route('admin.country.delete',$country->id)}}">
                            {{__('Delete')}}
                        </a>
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
<script src="{{ asset('admin/js/translation.js') }}"></script>
<script src="{{ asset('admin/js/country.js') }}"></script>
<script>
    adminCountry.initCountriesListing('{{route('admin.country.store_order')}}');
</script>
@endpush