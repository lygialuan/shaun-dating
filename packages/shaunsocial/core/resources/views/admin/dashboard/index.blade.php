@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-grid-block-cont">
    <div class="row">
        @if(auth()->user()->isSuperAdmin())
            <div class="col-md-6">
                <div class="admin-card">
                    <div class="admin-card-top">
                        <h5 class="admin-card-top-title"><a @hasPermission('admin.user.manage') href="{{route('admin.user.index')}}" @endHasPermission>{{__('Users')}}</a></h5>
                    </div>
                    <div class="admin-card-number">{{formatNumberNoRound($userCount)}}</div>
                    <div class="admin-card-body">
                        <div class="admin-progress-wrap">
                            <div class="admin-progress-top">
                                <span class="admin-progress-top-label">{{__('Online')}}</span>
                                <div class="admin-progress-top-number">{{formatNumberNoRound($userOnlineCount)}}/{{formatNumberNoRound($userCount)}}</div>
                            </div>
                            <div class="admin-progress progress">
                                @php
                                    $percent = round($userOnlineCount/$userCount,2)*100;
                                @endphp
                                <div class="progress-bar" style="width: {{$percent}}%" ></div>
                            </div>
                        </div>
                        <div class="admin-progress-wrap">
                            <div class="admin-progress-top">
                                <span class="admin-progress-top-label">{{__('Last online 15 days ago')}}</span>
                                <div class="admin-progress-top-number">{{formatNumberNoRound($userActiveCount)}}/{{formatNumberNoRound($userCount)}}</div>
                            </div>
                            <div class="admin-progress progress">
                                @php
                                    $percent = round($userActiveCount/$userCount,2)*100;
                                @endphp
                                <div class="progress-bar" style="width: {{$percent}}%" ></div>
                            </div>
                        </div>
                        <div class="admin-progress-wrap">
                            <div class="admin-progress-top">
                                <span class="admin-progress-top-label">{{__('Last online more than 15 days')}}</span>
                                <div class="admin-progress-top-number">{{formatNumberNoRound($userInActiveCount)}}/{{formatNumberNoRound($userCount)}}</div>
                            </div>
                            <div class="admin-progress progress">
                                @php
                                    $percent = round($userInActiveCount/$userCount,2)*100;
                                @endphp
                                <div class="progress-bar" style="width: {{$percent}}%" ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="admin-card">
                    <div class="admin-card-top">
                        <h5 class="admin-card-top-title"><a @hasPermission('admin.user_page.manage') href="{{route('admin.user_page.index')}}" @endHasPermission>{{__('Sub Profiles')}}</a></h5>
                    </div>
                    <div class="admin-card-number">{{formatNumberNoRound($pageCount)}}</div>
                    <div class="admin-card-body">
                        <div class="admin-progress-wrap">
                            <div class="admin-progress-top">
                                <span class="admin-progress-top-label">{{__('Online')}}</span>
                                <div class="admin-progress-top-number">{{formatNumberNoRound($pageOnlineCount)}}/{{formatNumberNoRound($pageCount)}}</div>
                            </div>
                            <div class="admin-progress progress">
                                @php
                                    $percent = 0;
                                    if ($pageCount) {
                                        $percent = round($pageOnlineCount/$pageCount,2)*100;
                                    }
                                @endphp
                                <div class="progress-bar" style="width: {{$percent}}%" ></div>
                            </div>
                        </div>
                        <div class="admin-progress-wrap">
                            <div class="admin-progress-top">
                                <span class="admin-progress-top-label">{{__('Last online 15 days ago')}}</span>
                                <div class="admin-progress-top-number">{{formatNumberNoRound($pageActiveCount)}}/{{formatNumberNoRound($pageCount)}}</div>
                            </div>
                            <div class="admin-progress progress">
                                @php
                                    $percent = 0;
                                    if ($pageCount) {
                                        $percent = round($pageActiveCount/$pageCount,2)*100;
                                    }
                                @endphp
                                <div class="progress-bar" style="width: {{$percent}}%" ></div>
                            </div>
                        </div>
                        <div class="admin-progress-wrap">
                            <div class="admin-progress-top">
                                <span class="admin-progress-top-label">{{__('Last online more than 15 days')}}</span>
                                <div class="admin-progress-top-number">{{formatNumberNoRound($pageInActiveCount)}}/{{formatNumberNoRound($pageCount)}}</div>
                            </div>
                            <div class="admin-progress progress">
                                @php
                                    $percent = 0;
                                    if ($pageCount) {
                                        $percent = round($pageInActiveCount/$pageCount,2)*100;
                                    }
                                @endphp
                                <div class="progress-bar" style="width: {{$percent}}%" ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="admin-card">
                    <div class="admin-card-top">
                        <h5 class="admin-card-top-title">{{__('Site Info')}}</h5>
                    </div>
                    <div class="admin-card-body">
                        <div class="admin-progress-wrap">
                            <div class="admin-progress-top">
                                <span class="admin-progress-top-label"><a @hasPermission('admin.group.manage') href="{{route('admin.group.index')}}" @endHasPermission >{{__('Groups')}}</a></span>
                                <div class="admin-progress-top-number">{{$groupCount}}</div>
                            </div>
                            <hr>
                        </div>
                        <div class="admin-progress-wrap">
                            <div class="admin-progress-top">
                                <span class="admin-progress-top-label"><a @hasPermission('admin.hashtag.manage') href="{{route('admin.hashtag.index')}}" @endHasPermission >{{__('Hashtags')}}</a></span>
                                <div class="admin-progress-top-number">{{$hashtagCount}}</div>
                            </div>
                            <hr>
                        </div>
                        <div class="admin-progress-wrap">
                            <div class="admin-progress-top">
                                <span class="admin-progress-top-label"><a @hasPermission('admin.report.manage') href="{{route('admin.report.index')}}" @endHasPermission >{{__('Reports')}}</a></span>
                                <div class="admin-progress-top-number">{{$reportCount}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="admin-card">
                    <div class="admin-card-top">
                        <h5 class="admin-card-top-title"><a @hasPermission('admin.user.manage') href="{{route('admin.user.index')}}" @endHasPermission>{{__('Users')}}</a></h5>
                    </div>
                    <div>
                        <canvas id="userChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="admin-card">
                    <div class="admin-card-top">
                        <h5 class="admin-card-top-title"><a @hasPermission('admin.user_page.manage') href="{{route('admin.user_page.index')}}" @endHasPermission>{{__('Sub Profiles')}}</a></h5>
                    </div>
                    <div>
                        <canvas id="pageChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="admin-card">
                    <div class="admin-card-top">
                        <h5 class="admin-card-top-title">
                            <a @hasPermission('admin.report.manage') href="{{route('admin.report.index')}}" @endHasPermission >{{__('Reports')}}</a>
                        </h5>
                    </div>
                    <div>
                        <canvas id="reportChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="admin-card">
                    <div class="admin-card-top">
                        <h5 class="admin-card-top-title">
                            {{__('Groups')}}
                        </h5>
                    </div>
                    <div>
                        <canvas id="groupChart"></canvas>
                    </div>
                </div>
            </div>
            @hasPermission('admin.setting.site,admin.setting.general')
                <div class="col-md-12">
                    <div class="admin-card">
                        <div class="admin-card-top">
                            <h5 class="admin-card-top-title">{{__('Notes')}}</h5> 
                        </div>
                        <div class="admin-card-body">
                            <textarea id="note" class="form-control" rows="5" placeholder="{{__('Please enter your notes')}}">{{setting('site.note')}}</textarea>
                        </div>
                    </div>
                </div>
            @endHasPermission
        @else
            <div class="col-md-12">
                <div class="admin-card text-center py-5">
                    <h5 class="mb-0">{{__('Welcome to admin dashboard')}}</h5>
                </div>
            </div>
        @endif
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{asset('admin/js/lib/chart.js')}}"></script>
<script src="{{ asset('admin/js/dashboard.js') }}"></script>
<script>
    adminDashboard.init('{{ route('admin.setting.store')}}', '{{ route('admin.dashboard.chart')}}');
</script>
@endpush