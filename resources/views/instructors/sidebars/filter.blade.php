<p class="lead">Views</p>
<div class="list-group">
	<a href="{{ route('students.index') }}" class="list-group-item {{ Request::is('students') && Input::get('s') == null ? 'active' : null }}">
		All
		@if(isset($count['all']))
			<span class="badge">{{ $count['all'] }}</span>
		@endif
	</a>
	<a href="{{ route('students.index', ['s' => 'passed']) }}" class="list-group-item {{ Request::is('students') && Input::get('s') == 'passed' ? 'active' : null }}">
		{{ Lang::get('core::training.status_passed') }}
		@if(isset($count['passed']))
			<span class="badge">{{ $count['passed'] }}</span>
		@endif
	</a>
	<a href="{{ route('students.index', ['s' => 'attending']) }}" class="list-group-item {{ Request::is('students') && Input::get('s') == 'attending' ? 'active' : null }}">
		{{ Lang::get('core::training.status_attending') }}
		@if(isset($count['attending']))
			<span class="badge">{{ $count['attending'] }}</span>
		@endif
	</a>
</div>