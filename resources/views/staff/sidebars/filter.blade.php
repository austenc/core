<p class="lead">Views</p>
<div class="list-group">
	<a href="{{ route(Route::currentRouteName(), ['s' => 'active']) }}" class="list-group-item {{ $filter == 'active' ? 'active' : null }}">
		{!! Icon::star() !!} Active 
		@if(isset($count['active']))
			<span class="badge">{{ $count['active'] }}</span>
		@endif
	</a>

	<a href="{{ route(Route::currentRouteName(), ['s' => 'all']) }}" class="list-group-item {{ $filter == 'all' ? 'active' : null }}">
		{!! Icon::dashboard() !!} All
		@if(isset($count['all']))
			<span class="badge">{{ $count['all'] }}</span>
		@endif
	</a>
	
	<a href="{{ route(Route::currentRouteName(), ['s' => 'inactive']) }}" 
	class="{{ isset($count['inactive']) && $count['inactive'] > 0 ? 'alert-danger' : '' }} list-group-item {{ $filter == 'inactive' ? 'active' : null }}">
		{!! Icon::flag() !!} 
		@if(isset($count['inactive']) && $count['inactive'] > 0)
			<strong>Inactive</strong>
		@else
			Inactive
		@endif
		@if(isset($count['inactive']))
			<span class="badge">{{ $count['inactive'] }}</span>
		@endif
	</a>
</div>