@extends('core::layouts.default')

@section('content')
{!! Form::model($ada, ['route' => ['adas.update', $ada->id], 'method' => 'PUT']) !!}
	<div class="col-md-9">
		<h2>Edit ADA Type</h2>
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
					{!! Form::checkbox('paper_only', 'true') !!} Paper Only?
				</label>
			</div>
		</div>
	</div>

	<div class="col-md-3 sidebar">
		<button type="submit" class="btn btn-success">{!! Icon::refresh() !!} Update</button>
	</div>
{!! Form::close() !!}
@stop