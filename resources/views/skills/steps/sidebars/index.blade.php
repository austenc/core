<p class="lead">Views</p>
<div class="list-group">
	<a href="{{ route('steps.index') }}" class="list-group-item {{ Request::is('steps') && Input::get('review') == null && Input::get('inputs') == null ? 'active' : null }}">
		All Steps
		<span class="badge">{{ $totalSteps }}</span>
	</a>
	<a href="{{ route('steps.index', ['review' => true]) }}" class="list-group-item {{ Request::is('steps') && Input::get('review') != null ? 'active' : null }}">
		Review Steps 
		<span class="badge">{{ $reviewable }}</span>
	</a>
	<a href="{{ route('steps.index', ['inputs' => true]) }}" class="list-group-item {{ Request::is('steps') && Input::get('inputs') != null ? 'active' : null }}">
		Input Steps
		<span class="badge">{{ $inputSteps }}</span>
	</a>
</div>