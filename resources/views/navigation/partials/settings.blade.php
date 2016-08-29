<li class="treeview {{!! HTML::activeClass(['agencies', 'staff', 'admins', 'import', 'users', 'trainings', 'certifications', 'discipline', 'adas', 'permissions', 'scantron']) !!}}">
    <a href="#">
        <i class="glyphicon glyphicon-cog"></i>
        <span>Settings</span>
        <i class="fa fa-angle-left pull-right"></i>
    </a>
    <ul class="treeview-menu">                   
        <li class="dropdown-header">People</li>
        <li class="divider"></li>
        {!! HTML::nav('admins', 'Manage Admins') !!}
        {!! HTML::nav('staff', 'Manage Staff') !!}
        {!! HTML::nav('agencies', 'Manage Agencies') !!}  

        <li class="dropdown-header">Disciplines</li>
        <li class="divider"></li>
        {!! HTML::nav('trainings', 'Trainings') !!}
        {!! HTML::nav('discipline', 'Disciplines')!!}
        @if(Config::get('core.certification.show'))
            {!! HTML::nav('certifications', 'Certifications') !!}
        @endif

        <li class="dropdown-header">App Settings</li>
        <li class="divider"></li>
        {!! HTML::nav('account', 'Profile') !!}
        <li class="{{ Request::is('logs/*') ? 'active' : null }}">
            <a href="{{ route('logs.index') }}" target="_blank">View Logs</a>
        </li>
        {!! HTML::nav('adas', 'Manage ADA\'s') !!}         
        {!! HTML::nav('permissions', 'Roles / Permissions') !!}
        {!! HTML::nav('scantron/adjust', 'Scanform Print Offsets') !!}
    </ul>
</li> 