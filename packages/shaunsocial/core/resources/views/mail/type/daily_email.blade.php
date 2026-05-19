<table border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0 10px; margin-top: 10px">
    <tbody>
        @foreach ($notifications as $notification)
        <tr sell-p>
            <td width="40" style="vertical-align: top">
                <a href="{{$notification['href']}}">
                    <img style="border-radius: 100%;" width="40" height="40" src="{{$notification['from']['avatar']}}"/>
                </a>
            </td>
            <td width="10"></td>
            <td>
                <a href="{{$notification['href']}}" style="color: #4F4F4F; text-decoration: none;">
                    <p style="margin-top: 0; margin-bottom: 5px"><b>{{$notification['from']['name']}}</b> {{$notification['message']}}</p>
                    @includeIf($notification['package'].'::notification.'.$notification['type'], ['notification' => $notification])
                    <p style="margin-top: 0; margin-bottom: 0">{{$notification['created_at_full']}}</p>
                </a>
            </td>
        </tr>   
        @endforeach                                                
    </tbody>
</table>
<div>
    <a href="{{route('web.notification.list')}}">{{__('View all notifications')}}</a>
</div>