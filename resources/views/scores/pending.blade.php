@extends('core::layouts.default')

@section('content')
	<h1>Scores Pending Review</h1>
	@if(empty($pendingScores))
		<p class="well">There are no pending scores to review at this time.</p>
	@else
		<table class="table table-hover table-striped">
			<thead>
				<tr>
					<th>Student</th>
					<th>Type</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				@foreach($pendingScores as $s)
				<tr>
					<td>{{{ $s->student }}}</td>
					<td>
						{{{ $s->type }}}
					</td>
					<td>
						{{-- does this person have a skill test as well? --}}
						@if(empty($s->knowledge))
							<a href="{{ route('scores.review', [$s->skill]) }}" class="btn btn-primary">Review</a>
						@else
							<a href="{{ route('scores.review', [$s->knowledge, $s->skill]) }}" class="btn btn-primary">Review</a>
						@endif
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	@endif
@stop