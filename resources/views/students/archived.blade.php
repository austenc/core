@extends('core::layouts.default')

@section('content')
	{!! Form::model($student, ['route' => ['students.archived.update', $student->id]]) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>
					{{ ucwords($student->full_name) }} <small>{{ Lang::choice('core::terms.student', 1) }}</small>
				</h1>
			</div>
			{!! HTML::backlink('students.index') !!}
		</div>

		{{-- Warnings --}}
		@include('core::warnings.archived')

		{{-- Identification --}}
		@include('core::students.partials.arch_identification')

		{{-- Other --}}
		@include('core::students.partials.arch_other')

		{{-- Contact --}}
		@include('core::students.partials.arch_contact')

		{{-- Address --}}
		@include('core::students.partials.arch_address')

		{{-- Account Status --}}
		@include('core::students.partials.account_status', ['status' => $student->status, 'holds' => $holds, 'locks' => $locks])

		{{-- Certifications --}}
		@include('core::students.partials.certifications')

		{{-- Current Owner --}}
		@include('core::students.partials.current_owner')
	
		{{-- Trainings --}}
		@include('core::students.partials.trainings')

		{{-- Test History --}}
		@include('core::students.partials.arch_test_history')

		{{-- ADA --}}
		@include('core::students.partials.ada')

		{{-- Login Info --}}
		@include('core::students.partials.login_info')

		{{-- Timestamps --}}
		@include('core::partials.timestamps', ['record' => $student])

		{{-- Comments --}}
		@include('core::partials.comments', ['record' => $student])
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::students.sidebars.edit')
	</div>

	{{-- Hidden Form Fields --}}
	{!! Form::hidden('first', $student->first) !!}
	{!! Form::hidden('middle', $student->middle) !!}
	{!! Form::hidden('last', $student->last) !!}
	{!! Form::hidden('ssn', $student->plain_ssn) !!}
	{!! Form::hidden('birthdate', $student->birthdate) !!}
	{!! Form::hidden('phone', $student->phone) !!}
	{!! Form::hidden('alt_phone', $student->alt_phone) !!}
	{!! Form::hidden('address', $student->address) !!}
	{!! Form::hidden('city', $student->city) !!}
	{!! Form::hidden('state', $student->state) !!}
	{!! Form::hidden('zip', $student->zip) !!}
	{!! Form::hidden('gender', $student->gender) !!}
	{!! Form::hidden('email', $student->user->email) !!}
	{!! Form::hidden('is_unlisted', $student->is_unlisted) !!}

	{!! Form::close() !!}
@stop
@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/students/status.js') !!}
@stop