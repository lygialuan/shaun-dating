@extends('shaun_core::download_data.layouts.master')

@section('content')
<div class="container w800">
	<div class="box">
        <ul class="list6 info">
            <li>
                <label>{{__('Profile URL')}}: </label>
                <div><a href="{{$href}}">{{$href}}</a></div>
            </li>
            <li>
                <label>{{__('Name')}}: </label>
                <div>{{$name}}</div>
            </li>
            @if ($is_page)
                <li>
                    <label>{{__('Description')}}: </label>
                    <div class="warp">{{$description}}</div>
                </li>
                <li>
                    <label>{{__('Categories')}}: </label>
                    <div class="warp">{{$categories}}</div>
                </li>
                @if ($hashtags)
                    <li>
                        <label>{{__('Hashtags')}}: </label>
                        <div class="warp">{{$hashtags}}</div>
                    </li>
                @endif
                @if ($websites)
                    <li>
                        <label>{{__('Websites')}}: </label>
                        <?php $websiteArray = explode(' ', $websites) ?>
                        <div>
                            @foreach ($websiteArray as $website)
                                <div><a class="break" href="{{$website}}" target="_blank">{{$website}}</a></div>
                            @endforeach
                        </div>
                    </li>
                @endif
                @if ($open_hours['type']['value'] != 'none')
                    <li>
                        <label>{{__('Open hours')}}: </label>
                        <div class="warp">{{$open_hours['type']['title']}}</div>
                        @if ($open_hours['type']['value'] == 'hours')
                            <div>
                                <?php $dayOfWeeks = getPageInfoDayOfWeekList(); ?>
                                @foreach ($open_hours['values'] as $key=>$value)
                                    <div>{{$dayOfWeeks[$key]}}: {{$value['start']}} - {{$value['end']}}</div>
                                @endforeach
                            </div>
                        @endif
                    </li>
                @endif
                @if ($price['value'])
                    <li>
                        <label>{{__('Price Range')}}</label>
                        <div class="warp">{{$price['title']}}</div>
                    </li>
                @endif
                @if ($address)
                    <li>
                        <label>{{__('Address')}}: </label>
                        <div class="warp">{{$address}}</div>
                    </li>
                @endif
                @if ($phone_number)
                    <li>
                        <label>{{__('Phone number')}}: </label>
                        <div class="warp">{{$phone_number}}</div>
                    </li>
                @endif
                @if ($email)
                    <li>
                        <label>{{__('Email')}}: </label>
                        <div class="warp">{{$email}}</div>
                    </li>
                @endif
                <li>
                    <label>{{__('Rating')}}: </label>
                    <div class="warp">{{formatNumber($review_score,2)}} ({{formatNumberNoRound($review_count)}} {{__('review(s)')}})</div>
                </li>
            @else
                @if ($gender)
                    <li>
                        <label>{{__('Gender')}}: </label>
                        <div>{{$gender}}</div>
                    </li>
                @endif

                @if ($bio)
                    <li>
                        <label>{{__('Bio')}}: </label>
                        <div class="warp">{{$bio}}</div>
                    </li>
                @endif

                @if ($about)
                    <li>
                        <label>{{__('About')}}: </label>
                        <div class="warp">{{$about}}</div>
                    </li>
                @endif

                @if ($location)
                    <li>
                        <label>{{__('Location')}}: </label>
                        <div>{{$location}}</div>
                    </li>
                @endif

                @if ($birthday)
                    <li>
                        <label>{{__('Birthday')}}: </label>
                        <div>{{$birthday}}</div>
                    </li>
                @endif

                @if ($links)
                    <li>
                        <label>{{__('Links')}}: </label>
                        <?php $linkArray = explode(' ', $links) ?>
                        <div>
                            @foreach ($linkArray as $link)
                                <div><a class="break" href="{{$link}}" target="_blank">{{$link}}</a></div>
                            @endforeach
                        </div>
                    </li>
                @endif

                @if ($follower_count)
                    <li>
                        <label>{{__('Follower')}}: </label>
                        <div><a href="user_follower.html">{{$follower_count}}</a></div>
                    </li>
                @endif

                @if ($following_count)
                    <li>
                        <label>{{__('Following')}}: </label>
                        <div><a href="user_following.html">{{$following_count}}</a></div>
                    </li>
                @endif
            @endif
        </ul>
    </div>
    <div class="box">
        <ul class="item-box">
            <li>
                <div class="items">
                    <h3 class="item-header">{{__('Your Information')}}</h3>
                    @if ($page)
                    <div class="items pd20">
                        <div class="item-content">                            
                            <a class="item-link" href="page.html">{{__('Pages')}}</a>
                        </div>
                    </div>
                    @endif
                    @if ($admin_page)
                    <div class="items pd20">
                        <div class="item-content">                            
                            <a class="item-link" href="admin_page.html">{{__('Managers')}}</a>
                        </div>
                    </div>
                    @endif
                    @if (!empty($post_id))
                    <div class="items pd20">
                        <div class="item-content">                            
                            <a class="item-link" href="post.html">{{__('Posts')}}</a>
                        </div>
                    </div>
                    @endif
                    @if (!empty($room_id))
                    <div class="items pd20">
                        <div class="item-content">                            
                            <a class="item-link" href="room.html">{{__('Messages')}}</a>
                        </div>
                    </div>
                    @endif
                </div>
            </li>
        </ul>
    </div>
</div>

@stop