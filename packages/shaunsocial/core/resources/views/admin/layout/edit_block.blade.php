<header class="modal-card-head">
    <p class="modal-card-title">{{$block->title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="block_edit_form" method="post" onsubmit="return false;">
            <div id="errors"></div>
            <div class="form-group">
                <label class="control-label">{{__('Title')}}</label>
                <input id="title" class="form-control" name="title" @if ($content) readonly value="{{$content->getTranslatedAttributeValue('title') }}" @endif type="text">
                @if ($content)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$content->getTable(),'title',$content->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>
            <div class="form-group" @if ($block->component  != 'HtmlBlock') style="display:none" @endif>
                <label class="control-label">{{__('Content')}}</label>
                <textarea class="form-control" id="content" name="textarea" @if ($content) readonly @endif>@if ($content) {{$content->getTranslatedAttributeValue('content') }} @endif</textarea>
                @if ($content)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$content->getTable(),'content',$content->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>
            @includeIf($block->package.'::admin.partial.layout.block.'.$block->component)
            <div class="form-group">
                <label class="control-label">{{__('Enable title')}}</label>
                <div class="form-check form-switch">
                    <input id="enable_title" class="form-check-input" name="enable_title" type="checkbox" value="1">
                </div>   
            </div>
            @include('shaun_core::admin.partial.role.access',['fieldName'=> 'role_access','values'=>[]])
            
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="block_edit_submit">{{__('Save changes')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>