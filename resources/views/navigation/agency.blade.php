<ul class="sidebar-menu">
	{!! HTML::nav('students', Lang::choice('core::terms.student', 2), 'user') !!}
    {!! HTML::nav('instructors', Lang::choice('core::terms.instructor', 2), 'education') !!}       
    {!! HTML::nav('facilities', Lang::choice('core::terms.facility', 2), 'home') !!}
    {!! HTML::nav('events', 'Events', 'calendar') !!} 
	{!! HTML::nav('reports', 'Reports', 'stats') !!} 
    {!! HTML::nav('account', 'Profile', 'cog') !!}
</ul>