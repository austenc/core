<ul class="sidebar-menu">
	{!! HTML::nav('students/'.Auth::user()->userable->id.'/tests', 'Tests', 'list-alt') !!}
	{!! HTML::nav('students/'.Auth::user()->userable->id.'/trainings', 'Trainings', 'book') !!}
	{!! HTML::nav('account', 'Profile', 'cog') !!}
</ul>