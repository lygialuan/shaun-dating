<div class="drag layout-block-col-item {{$content->type == 'component'?'':'layout-block-col-item-fixed'}}">
    <div class="title">{{$content->getTranslatedAttributeValue('title')}}</div>    
    <div class="layout-block-col-item-actions" @if ($content->type == 'container') style="display:none" @endif>
        <a href="javascript:void(0);" class="content_edit" data-content="{{$content->getTranslatedAttributeValue('content')}}" data-id="{{$content->id}}" data-package="{{$content->package}}" data-position="{{$content->position}}" data-title="{{$content->getTranslatedAttributeValue('title')}}" data-enable_title="{{$content->enable_title}}" data-class="{{$content->class}}" data-component="{{$content->component}}" data-role_access="{{$content->role_access}}" data-params="{{$content->params}}"><span class="layout-block-col-item-actions-icon material-symbols-outlined notranslate"> edit </span></a>
        <a href="javascript:void(0);" class="content_remove" data-id="{{$content->id}}"><span class="layout-block-col-item-actions-icon material-symbols-outlined notranslate"> close </span></a>
    </div>
</div>