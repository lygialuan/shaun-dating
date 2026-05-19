@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">  
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Edit phrase')}}</h5>
        <div class="admin-card-top-action">
            <a class="btn-filled-blue dark" href="{{route('admin.language.download_phrase', $language->id)}}" href="javascript:void(0);">{{__('Download translate')}}</a>
            <a class="btn-filled-blue dark admin_modal_ajax" data-url="{{route('admin.language.upload_phrase', $language->id)}}" href="javascript:void(0);">{{__('Upload translate')}}</a>
        </div>
    </div>  
    <div class="admin-card-bar">
        <div class="admin-card-search-bar-wrap">
            <span class="material-symbols-outlined notranslate admin-card-search-bar-icon"> search </span>
            <input type="text" id="name" class="admin-card-search-bar-input form-control" placeholder="{{__('Search')}}"/>
        </div>
    </div>
    <div class="admin-card-body table-responsive">        
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Phrase')}}
                </th>
                <th>
                    {{__('Value')}}
                </th>
                <th width="120">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($phrases as $key => $phrase)
                <tr id="{{md5($key)}}" class="phrases">
                    <td class="phrases_key">
                        {!! $key !!}
                    </td>
                    <td class="phrases_value">
                        {!! $phrase !!}
                    </td>
                    <td class="actions-cell">
                        <a class="edit_phrases" href="javascript:void(0)">
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
<div id="modal-edit-phrase" class="modal">
    <div class="modal-background modal-close"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">{{__('Edit phrase')}}</p>
        </header>
        <section class="modal-card-body">        
            <div class="card-content">
                <div class="form-group">
                    <textarea class="form-control" id="value" value=""></textarea>
                </div>
        </section>
        <footer class="modal-card-foot">
            <button class="button btn-filled-blue modal-action-edit-phrase-save">{{__('Save')}}</button>
            <button class="button btn-filled-white modal-close">{{__('Cancel')}}</button>
        </footer>
    </div>
</div>
<script src="{{ asset('admin/js/language.js') }}"></script>
<script>
    adminLanguage.initEditPhrase('{{ route('admin.language.store_phrase', $language->id)}}');
</script>
@endpush