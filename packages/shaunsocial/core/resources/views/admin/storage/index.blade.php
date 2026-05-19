@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    @if ($transfer)
        <div class="admin-message message-success w-full" role="alert">           
            {{__('Files are currently being transferred. :number files on the process.',['number'=> $localService->getCountFile()])}}
        </div>
    @endif
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Storage System')}}</h5>
    </div>
    <p class="admin-card-help">{{__("View and manage storage system services. The storage system is used to handle file uploads for your site. You can configure this to use Amazon's S3 or CloudFront service or any CDN.")}}</p>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Name')}}
                </th>
                <th>
                    {{__('File')}}
                </th>
                <th>
                    {{__('Storage Used')}}
                </th>
                <th>
                    {{__('Default')}}
                </th>
                <th>
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($services as $service)
                <tr>
                    <td>
                        {{$service->name}}
                    </td>
                    <td>
                        {{$service->getCountFile()}}
                    </td>
                    <td>
                        {{formatNumberNoRound($service->getTotalSize(),'bytes')}}
                    </td>
                    <td>
                        @if ($service->is_default)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a href="{{route('admin.storage.edit',$service->id)}}">
                            {{__('Edit')}}
                        </a>
                        @if (!$transfer && $service->is_default && $service->key != 'public')
                            <a class="admin_modal_confirm_delete" href="javascript:void(0);" data-url="{{route('admin.storage.transfer',$service->id)}}" data-content="{{addslashes(__('This will start the process of transferring stored files from local to this storage service.'))}}">
                                {{__('Transfer')}}
                            </a>
                        @endif
                        @if ($transfer && $service->key == $transfer)
                            <a class="admin_modal_confirm_delete" href="javascript:void(0);" data-url="{{route('admin.storage.stop_transfer')}}" data-content="{{addslashes(__('This will stop the process of transferring stored files from local to this storage service.'))}}">
                                {{__('Stop transfer')}}
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