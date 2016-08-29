@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => ['students.update_ada', $student->id, $ada->id]]) !!}
<div class="col-md-9">
	<div class="row">
		<div class="col-xs-8">
			<h2>ADA Status - {{ $student->fullname }}</h2>
		</div>
		<div class="col-xs-4 back-link">
			<a href="{{ route('students.edit', $student->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to {{ $student->fullname }}</a>
		</div>
	</div>

	<div class="well">
		<div class="form-group">
			{!! Form::label('ada', 'ADA') !!}
			{!! Form::text('ada_name', $ada->name, ['disabled']) !!}
		</div>
		<div class="form-group">
			{!! Form::label('status', 'Status') !!}
			{!! Form::select('status', [
				'pending'  => 'Pending', 
				'accepted' => 'Accepted', 
				'denied'   => 'Denied'
			], $ada->pivot->status) !!}
		</div>
		<div class="form-group">
			{!! Form::label('notes', 'Notes') !!}
			{!! Form::textarea('notes', $ada->pivot->notes) !!}
		</div>
	</div>
</div>
<div class="col-md-3 sidebar">
	<button type="submit" class="btn btn-success">{!! Icon::refresh() !!} Update</button>
</div>
{!! Form::close() !!}
@stop