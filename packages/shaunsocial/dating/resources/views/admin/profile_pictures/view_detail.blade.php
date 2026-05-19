@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ $title }}</h5>
    </div>
    <section class="modal-card-body">
        <div class="card-content">
            <form id="profile_pictures_form" method="POST" action="{{ route('admin.dating.profile_pictures.store') }}">
                <div id="errors"></div>
                {{ csrf_field() }}
                <input type="hidden" name="id" value="{{ $user->id }}">
                <div class="row">
                    @if($user)
                        @php
                            $photos = $user->getPhotoVerifyItem('pending');
                        @endphp

                        @if($photos && $photos->isNotEmpty())
                            @foreach($photos as $photo)
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mb-4">
                                    <div class="card shadow-sm h-100 border-0">
                                        <a href="{{ $photo->getFile()?->getUrl() }}" target="_blank">
                                            <img 
                                                src="{{ $photo->getFile()?->getUrl() }}" 
                                                class="card-img-top img-fluid"
                                                style="height: 250px; object-fit: cover;"
                                            >
                                        </a>
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label class="mb-0">
                                                    <input 
                                                        type="radio" 
                                                        name="photos[{{ $photo->id }}]" 
                                                        value="reject"
                                                        checked
                                                    >
                                                    <span class="small">
                                                        {{ __('Reject') }}
                                                    </span>
                                                </label>
                                                <label class="mb-0">
                                                    <input 
                                                        type="radio" 
                                                        name="photos[{{ $photo->id }}]" 
                                                        value="approve"
                                                        {{ $photo->status == 'approved' ? 'checked' : '' }}
                                                    >
                                                    <span class="small">
                                                        {{ __('Approve') }}
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center py-5">
                                {{ __('No photos found') }}
                            </div>
                        @endif
                    @endif
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-filled-blue" id="profile_pictures_submit">{{__('Save Changes')}}</button>
                </div>
            </form>
        </div>
    </section>
</div>
@stop
