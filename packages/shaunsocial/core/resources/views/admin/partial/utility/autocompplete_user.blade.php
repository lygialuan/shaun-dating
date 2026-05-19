<?php
    $multiple = !isset($multiple) ? true : $multiple;
    $userIds = collect(explode(',', $value));
    $users = $userIds->map(function ($item, int $key) {
        if ($item) {
            return Packages\ShaunSocial\Core\Models\User::findByField('id', $item);
        } else {
            return false;
        }
    })->filter(function ($item) {
        return $item;
    });
?>
<div>
    <input id="{{md5($id)}}_suggest" @if(!$multiple && count($users)) style="display:none" @endif class="input form-control" data-multiple="{{ $multiple }}">
    <div id="list_{{md5($id)}}_suggest" class="suggested-user-list">
        @foreach ($users as $user)
            <div class="suggested-user-item">
                <img class="suggested-user-item-avatar" src="{{$user->getAvatar()}}"/>
                <div class="suggested-user-item-name">{{$user->getName()}}</div>
                <a data-id="{{$user->id}}" class="suggested-user-item-action remove_{{md5($id)}}_suggest">
                    <span class="suggested-user-item-action-icon material-symbols-outlined notranslate">close</span>
                </a>
            </div>
        @endforeach
    </div>
</div>

@push('scripts-body')
<script>
    $( document ).ready(function() {
        $( 'body' ).on( 'click', 'a.remove_{{md5($id)}}_suggest', function( event ) {
            var ids = $('#{{md5($id)}}').val().trim().split(',').filter(function(i){return i})
            var index = ids.indexOf($(this).data('id').toString());
            if (index != -1) {
                ids.splice(index, 1)
                $('#{{md5($id)}}').val(ids.join(','))
                $('#{{md5($id)}}').trigger('blur')
            }
            event.preventDefault()
            $(this).parent().remove()

            var multiple = $('#{{md5($id)}}_suggest').data('multiple');
            if(!multiple && ids.length >= 1){
                $('#{{md5($id)}}_suggest').hide();
            } else {
                $('#{{md5($id)}}_suggest').show();
            }
        });
        $('#{{md5($id)}}_suggest').autocomplete({
            serviceUrl: function (query) {
                var url = '{{route('admin.utility.user_suggest',['text'=> 'abc'])}}'
                return url.replace('abc', query)
            },
            onSelect: function (suggestion) {
                var ids = $('#{{md5($id)}}').val().trim().split(',').filter(function(i){return i})
                if (ids.indexOf(suggestion.value) == -1) {
                    ids.push(suggestion.value)
                    $('#{{md5($id)}}').val(ids.join(','))
                    $('#{{md5($id)}}').trigger('blur')

                    var html = '<div class="suggested-user-item">' +
                                '<img class="suggested-user-item-avatar" src="'+ suggestion.avatar +'"/>' +
                                '<div class="suggested-user-item-name">' + suggestion.name + '</div>' +
                                '<a data-id="' + suggestion.value + '" class="suggested-user-item-action remove_{{md5($id)}}_suggest"><span class="suggested-user-item-action-icon material-symbols-outlined notranslate">close</span></a>' +
                                '</div>';
                    $('#list_{{md5($id)}}_suggest').append(html)
                }

                $('#{{md5($id)}}_suggest').val('')

                var multiple = $(this).data('multiple');
                if(!multiple && ids.length >= 1){
                    $(this).hide()
                }
            },
            transformResult: function(response) {
                response = $.parseJSON(response)
                return {
                    suggestions: $.map(response.data, function(dataItem) {
                        return { name: dataItem.name, value: dataItem.id.toString(), avatar: dataItem.avatar, user_name: dataItem.user_name };
                    })
                }
            },
            formatResult: function (suggestion) {
                return '<div class="autocomplete-suggestion-user-item"><img class="autocomplete-suggestion-user-item-avatar" src="' + suggestion.avatar+'" /><div><div class="autocomplete-suggestion-user-item-name"> '  + suggestion.name + '</div><div class="autocomplete-suggestion-user-item-user-name"> {{config('shaun_core.core.prefix_profile')}}'  + suggestion.user_name + '</div></div></div>';
            },
            'ignoreParams': true,
            'deferRequestBy' : 1000,
            'noCache' : true
        });
    });
    
</script>
@endpush