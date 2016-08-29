{{-- Is User Authorized? --}}
@if(Auth::check())
    {{-- Messages Menu --}}
    <li class="dropdown notifications-menu">

        <!-- Menu toggle button -->
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="glyphicon glyphicon-envelope"></i>
            <span class="label label-success unread-notifications">{{{ $unreadNotifications > 0 ? $unreadNotifications : '' }}}</span>
        </a>
        <ul class="dropdown-menu">
            <li class="header">
                You have {{{ $unreadNotifications or 0 }}} 
                new message{{ $unreadNotifications == 1 ? '' : 's' }}
            </li>
            <li>
                <!-- inner menu: contains the messages -->
                <ul class="menu">

                    @foreach($notifications as $message)
                        <li>
                            <a href="{{ route('notification.detail', $message->id) }}">
                                <h4>
                                    {{{ $message->subject }}}
                                    <small><i class="fa fa-clock-o"></i> {{{ $message->timeForHumans }}}</small>
                                </h4>
                                <p>
                                    {{{ $message->flatBody }}}
                                </p>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <!-- /.menu -->
            </li>
            <li class="footer"><a href="/inbox">See All Messages</a></li>
        </ul>
    </li>
    <!-- /.messages-menu -->

    <!-- User Account Menu -->
    <li class="dropdown user user-menu">
        <!-- Menu Toggle Button -->
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="glyphicon glyphicon-user"></i>
            <!-- hidden-xs hides the username on small devices so only the image appears. -->
            <span class="hidden-xs">{{ $user->username }} ({{ $userType }})</span>
        </a>
        <ul class="dropdown-menu">
            <!-- The user image in the menu -->
            <li class="user-header">
                <p>
                    {{{ $user->userable->fullName }}} - {{{ $userType }}}
                </p>
            </li>
            <!-- Menu Body -->
            {{-- Show a select box if the user has more than one role --}}
            @if($user->roles->count() > 1)
                <li class="user-body">
                {!! Form::open(['route' => 'users.change_role', 'id' => 'choose-role-form']) !!}
                    <label>Choose Role</label>
                    {!! Form::select(
                        'user_choose_role', 
                        $user->roles->lists('name', 'id')->all(),
                        Session::get('user.current_role_id'),
                        ['id' => 'user-choose-role']
                    ) !!}
                {!! Form::close() !!}
                </li>
            @endif

            <!-- Menu Footer-->
            <li class="user-footer">
                <div class="pull-left">
                    <a href="{{ route('account') }}" class="btn btn-default btn-flat">Profile</a>
                    {{-- Facility Reset Login --}}
                    @if($user->userable_type == 'Facility' && $user->userable->disciplines->count() > 1)
                        <a href="{{ route('facilities.login') }}" class="btn btn-default btn-flat">Reset Filter</a>
                    {{-- Instructor Reset Login --}}
                    @elseif($user->userable_type == 'Instructor' && $user->userable->activeFacilities()->get()->count() > 1)
                        <a href="{{ route('instructors.login.reset') }}" class="btn btn-default btn-flat">Reset Filter</a>
                    @endif
                </div>
                <div class="pull-right">
                    <a href="{{ route('logout') }}" class="btn btn-default btn-flat" data-confirm="Are you sure you want to log out?">Sign out</a>
                </div>
            </li>
        </ul>
    </li>

    {{-- Logout --}}
    <li class="hidden-xs">
        <a href="/logout" data-confirm="Are you sure you want to log out?">{!! Icon::log_out() !!} Logout</a>
    </li>  
@else
    {!! HTML::nav('login', 'Login') !!}
@endif