@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => ['students.store_ada', $student->id]]) !!}
<div class="col-md-9">
	<div class="row">
		<div class="col-xs-8">
			<h2>Current ADA</h2>
		</div>
		<div class="col-xs-4 back-link">
			<a href="{{ route('students.edit', $student->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to {{ $student->fullname }}</a>
		</div>
	</div>
	
	<div class="well" id="current-adas">
		<div class="form-group" style="margin-bottom:0px">
			@if( ! $student->adas->isEmpty())
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th>Name</th>
							<th>Status</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@foreach($student->adas as $i => $ada)
						@if($ada->pivot->status == 'accepted')
						<tr class="success">
						@else
						<tr>
						@endif
							<td>
								{{ $ada->name }}<br>
								<small>{{ $ada->abbrev }}</small>
							</td>
							<td>
								@if($ada->pivot->status == 'pending')
									<span class="label label-warning">
								@elseif($ada->pivot->status == 'accepted')
									<span class="label label-success">
								@else
									<span class="label label-danger">
								@endif
									{{ ucfirst($ada->pivot->status) }}</span>
								</span>
							</td>
							<td>
								<a href="{{ route('students.edit_ada', [$student->id, $ada->id]) }}" data-toggle="tooltip" title="Edit ADA" class="btn btn-link pull-right">
									{!! Icon::edit() !!}
								</a>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			@else
				No ADA's found.
			@endif
		</div>
	</div>

	<h2>Select New ADA</h2>
	<div class="well" id="new-adas">
		<div class="form-group">
			{!! Form::label('status', 'Select New ADA Status') !!}
			{!! Form::select('status', [
					'pending'  => 'Pending', 
					'accepted' => 'Accepted', 
					'denied'   => 'Denied'
				]) !!}
		</div>
		<hr>
		<div class="form-group">
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th></th>
						<th>Name</th>
						<th>Abbrev</th>
						<th>Extend Time</th>
					</tr>
				</thead>
				<tbody>
				@foreach($adas as $i => $ada)
				<tr data-clickable-row>
					<td>{!! Form::checkbox('adas[]', $ada->id, false) !!}</td>
					<td>{{ $ada->name }}</td>
					<td>{{ $ada->abbrev }}</td>
					<td>{{ $ada->extend_time }}</td>
				</tr>
				@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="col-md-3 sidebar">
	<button class="btn btn-success" type="submit">{!! Icon::plus_sign() !!} Add ADA</button>
</div>
{!! Form::close() !!}
@stop