@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'students.store']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>{{ Lang::get('core::titles.student_create') }}</h1>
			</div>
			{!! HTML::backlink('students.index') !!}
		</div>

		@if($errors->has('existing_ssn_student'))
		<div class="alert alert-warning">
			<strong>Warning!</strong> {{ $errors->first('existing_ssn_student') }}
		</div>
		@endif

		{{-- Identification --}}
		@include('core::students.partials.identification')
		
		{{-- Other --}}
		@include('core::students.partials.other')

		{{-- Set Password --}}
		@include('core::partials.set_password')

		{{-- Contact --}}
		@include('core::partials.contact', ['name' => 'student'])

		{{-- Address --}}
		@include('core::partials.address', ['required' => true])

		{{-- Initial Training --}}
		@include('core::students.partials.initial_training')

		{{-- Comments --}}
		@include('core::partials.comments')
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::students.sidebars.create')
	</div>
	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/students/add_training.js') !!}
	{!! HTML::script('vendor/hdmaster/core/js/students/create.js') !!}
@stop