@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Translate Providers')}}</h5>
    </div>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Name')}}
                </th>
                <th>
                    {{__('Default')}}
                </th>
                <th>
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($providers as $provider)
                <tr>
                    <td>
                        {{$provider->name}}
                    </td>
                    <td>
                        @if ($provider->is_default)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a href="{{route('admin.translate_provider.edit',$provider->id)}}">
                            {{__('Edit')}}
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
@endpush