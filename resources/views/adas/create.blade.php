@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => 'adas.store']) !!}
<div class="col-md-9">
	<h2>Create ADA Type</h2>

	<div class="well">
		<div class="form-group">
			{!! Form::label('name', 'Name') !!}
			{!! Form::text('name') !!}
			<span class="text-danger">{{ $errors->first('name') }}</span>
		</div>

		<div class="form-group">
			{!! Form::label('abbrev', 'Abbreviation') !!}
			{!! Form::text('abbrev') !!}
			<span class="text-danger">{{ $errors->first('abbrev') }}</span>
		</div>

		<div class="form-group">
			{!! Form::label('test_type', 'Test Type Affected') !!}
			{!! Form::select('test_type', $testTypes) !!}
		</div>

		<div class="form-group">
			{!! Form::label('extend_time', 'Extend Time By') !!} <small>(minutes)</small>
			{!! Form::text('extend_time') !!}
			<span class="text-danger">{{ $errors->first('extend_time') }}</span>
		</div>

		<div class="checkbox">
			<label>
				{!! Form::checkbox('paper_only', 'true', FALSE) !!} Paper Only?
			</label>
		</div>
	</div>
</div>
<div class="col-md-3 sidebar">
	<button type="submit" class="btn btn-success">{!! Icon::plus_sign() !!} Save ADA</button>
</div>
{!! Form::close() !!}
@stop