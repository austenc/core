<ul class="sidebar-menu">
    {{-- People --}}
    @include('core::navigation.partials.people')

    {{-- Events --}}
    {!! HTML::nav('events', 'Events', 'calendar') !!}
    
    {{-- Scoring --}}
    @include('core::navigation.partials.scoring')

    {{-- Reports --}}
    {!! HTML::nav('reports', 'Reports', 'stats') !!}

    {{-- State Services --}}
    @if(View::exists('navigation.services'))
        @include('navigation.services')
    @endif

    <li class="treeview {!! HTML::activeClass(['import', 'testitems', 'users', 'testplans', 'trainings', 'exams', 'certifications', 'skills', 'tasks', 'skillexams']) !!}">
        <a href="#">
            <i class="glyphicon glyphicon-cog"></i>
            <span>Settings</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">                   
            {!! HTML::nav('scantron/adjust', 'Scanform Print Offsets') !!}
            {!! HTML::nav('adas', 'Manage ADA\'s') !!}         
            {!! HTML::nav('agencies', 'Manage Agencies') !!}  
        </ul>
    </li>    
</ul>