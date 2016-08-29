@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'subjects.store']) !!}
	<div class="row">
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>New Subject</h1>
				</div>
				{!! HTML::backlink('subjects.index') !!}
			</div>

			{{-- Warnings --}}
			<div class="alert alert-warning">
				{!! Icon::flag() !!} <strong>Report As</strong> can be set after successful create
			</div>

			<h3>Basic Info</h3>
			<div class="well">
				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('exam_id', 'Exam') !!} @include('core::partials.required')
						{!! Form::select('exam_id', [0 => 'Select Exam'] + $exams, $selExam) !!}
						<span class="text-danger">{{ $errors->first('exam_id') }}</span>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('name', 'Name') !!} @include('core::partials.required')
						{!! Form::text('name') !!}
						<span class="text-danger">{{ $errors->first('name') }}</span>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('old_number', 'Old Subject #') !!}
						{!! Form::text('old_number') !!}
						<span class="text-danger">{{ $errors->first('old_number') }}</span>
					</div>
				</div>
			</div>
		</div>

		{{-- Sidebar  --}}
		<div class="col-md-3 sidebar">
			<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
				{!! Button::success(Icon::plus_sign().' Create')->submit() !!}
			</div>
		</div>
	</div>
	{!! Form::close() !!}
@stop