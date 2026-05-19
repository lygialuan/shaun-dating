<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>{{$page_title}} | {{$site_title}}</title>

        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <link rel="stylesheet" type="text/css" href="{{$static_path}}css/main.css"/>
    </head>
    <body>
        <div class="full_header">
            <div class="container">
                
                <div class="logo-default">
                    <a href="javascript:void(0)"><img src="{{$static_path}}{{$logo}}" alt=""></a>
                </div>
                <div class="menu_large">
                    <a href="javascript:void(0)">
                        <img src="{{$static_path}}{{$user_avatar}}" id="member-avatar" prefix="50_square" width="45px">
                    </a>
                </div>
            </div>
        </div>
        @yield('content')
    </body>
</html>