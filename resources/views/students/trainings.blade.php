@extends('core::layouts.default')

@section('content')
	<div class="row">
		<div class="col-md-8">
			<h1>Trainings</h1>
		</div>
	</div>

	@if( ! $trainings->isEmpty())
		<div class="well">
			<table class="table table-striped" id="trainings-table">
				<thead>
					<tr>
						<th>Training</th>
						<th>Status</th>
						<th>{{ Lang::choice('instructor', 1) }}</th>
						<th>Started</th>
						<th>Ended</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@foreach ($trainings as $training)
						@if($training->pivot->status == 'failed')
						<tr class="danger">
						@elseif($training->pivot->status == 'passed')
						<tr class="success">
						@elseif($training->pivot->status == 'attending')
						<tr class="warning">
						@else
						<tr>
						@endif
							<td>{{ $training->name }}</td>
							<td>
								@if($training->pivot->archived)
								<span class="label label-default">
								@elseif($training->pivot->status == 'passed')
								<span class="label label-success">
								@elseif($training->pivot->status == 'failed')
								<span class="label label-danger">
								@else
								<span class="label label-warning">
								@endif
									{{ Lang::get('core::training.status_'.$training->pivot->status) }}
								</span>
							</td>
							<td>{{ $training->inc_name }}</td>
							<td>{{ date('m/d/Y',strtotime($training->pivot->started)) }}</td>
							<td>
								{{ isset($training->pivot->ended) ? date('m/d/Y',strtotime($training->pivot->ended)) : '' }}
							</td>
							<td>
								<div class="btn-group pull-right">
									<a href="{{ route('students.training_detail', [Auth::user()->userable->id, $training->pivot->id]) }}" class="btn-icon btn pull-right" data-toggle="modal" data-target="#training-detail" title="View Training Detail">{!! Icon::search() !!}</a>
								</div>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	@else
		<p class="well">No trainings on record.</p>
	@endif

	{!! HTML::modal('training-detail') !!}
@stop