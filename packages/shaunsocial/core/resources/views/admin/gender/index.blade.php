@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">    
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Genders')}}</h5>
        <a class="btn-filled-blue admin_modal_ajax dark" data-url="{{route('admin.gender.create')}}" href="javascript:void(0);">{{__('Create new')}}</a>
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
                <th width="150">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody id="genders_list">
                @foreach ($genders as $gender)
                <tr data-id="{{$gender->id}}">
                    <td>
                        <span role="button" class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                    </td>
                    <td>
                    {{$gender->getTranslatedAttributeValue('name')}}
                    </td>
                    <td>
                        @if ($gender->is_active)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.gender.create',$gender->id)}}">
                            {{__('Edit')}}
                        </a>
                        <a class="button-red admin_modal_confirm_delete" data-url="{{route('admin.gender.delete',$gender->id)}}" href="javascript:void(0);">
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
<script src="{{ asset('admin/js/gender.js') }}"></script>
<script src="{{ asset('admin/js/translation.js') }}"></script>
<script>
    adminGender.initListing('{{route('admin.gender.store_order')}}');
</script>
@endpush