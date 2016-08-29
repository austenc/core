<ul class="sidebar-menu">
	@if(is_array(Auth::user()->userable->actions) && in_array('Testing', Auth::user()->userable->actions))
		{!! HTML::nav('events', 'Events', 'calendar') !!}
	@endif

	@if(is_array(Auth::user()->userable->actions) && in_array('Training', Auth::user()->userable->actions))
		{!! HTML::nav('students', 'Students', 'user') !!}

		{!! HTML::nav('reports', 'Reports', 'stats') !!}
	@endif
	
	{!! HTML::nav('account', 'Profile', 'cog') !!}
</ul>