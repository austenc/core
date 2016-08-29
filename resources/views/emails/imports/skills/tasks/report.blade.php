<h2>Skill Task Import Report</h2>
<p>The following {{ $exam->name }} Skill Task records were processed at {{ date('m/d/Y H:i:s') }}. </p>

@if(isset($records['active']))
	<strong>Active</strong>
	@foreach($records as $i => $task)
		<p>{{ $i }}. {{ $task['title'] }}</p>
	@endforeach
	<br><br>
@endif

@if(isset($records['draft']))
	<strong>Draft</strong>
	@foreach($records as $i => $task)
		<p>{{ $i }}. {{ $task['title'] }}</p>
	@endforeach
	<br><br>
@endif

@if(isset($records['archived']))
	<strong>Archived</strong>
	@foreach($records as $i => $task)
		<p>{{ $i }}. {{ $task['title'] }}</p>
	@endforeach
	<br><br>
@endif

<strong>Login at {!! link_to('/') !!} to gain access to your account.</strong>