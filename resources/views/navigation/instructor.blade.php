@if(Session::has('discipline'))
<ul class="sidebar-menu">
	{!! HTML::nav('students', Lang::choice('core::terms.student', 2), 'user') !!}
	{!! HTML::nav('reports', 'Reports', 'stats') !!}
	{!! HTML::nav('account', 'Profile', 'cog') !!}
</ul>
@endif