@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ $title }}</h5>
        <div class="d-flex gap-2">
            <a class="button admin_modal_ajax btn-filled-blue" data-url="{{route('admin.country.city.import', $state->id)}}" href="javascript:void(0);">{{__('Import City')}}</a>
            <a class="button admin_modal_ajax btn-filled-blue" data-url="{{route('admin.country.city.create', $state->id)}}" href="javascript:void(0);">{{__('Create City')}}</a>
        </div>
    </div>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th width="40"></th>
                <th>
                    {{__('Name')}}
                </th>
                <th width="100">
                    {{__('Status')}}
                </th>
                <th width="150">
                    {{__('Active')}}
                </th>
            </thead>
            <tbody id="cities_list">
                @foreach ($cities as $city)
                <tr data-id="{{$city->id}}">
                    <td>
                        <span role="button" class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                    </td>
                    <td>
                        {{$city->getTranslatedAttributeValue('name')}}
                    </td>
                    <td>
                        @if ($city->is_active)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td class="actions-cell" width="150">
                        <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.country.city.create', [$city->state_id, $city->id])}}">
                            {{__('Edit')}}
                        </a>
                        <a class="button-red admin_modal_confirm_delete" href="javascript:void(0);" data-url="{{route('admin.country.city.delete',$city->id)}}">
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
    adminCountry.initCitiesListing('{{route('admin.country.city.store_order')}}', '{{route('admin.country.city.store_active')}}');
</script>
@endpush