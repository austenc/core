@extends('core::layouts.default')

@section('content')
	{!! Form::model($instructor, ['route' => ['account.update', 'instructor', $instructor->id], 'method' => 'PUT']) !!}

	<div class="col-md-9">
		<h1>Your Account</h1>
		<h3>Login</h3>
		<div class="well">
			<div class="form-group">
				{!! Form::label('username', 'Username') !!}
				{!! Form::text('username', $instructor->user->username) !!}
				<span class="text-danger">{{ $errors->first('username') }}</span>
			</div>

			{{-- Password Fields --}}
			@include('core::partials.change_password')
		</div>
	
		<h3>Current {{ Lang::choice('core::terms.facility_training', 1) }}</h3>
		<div class="well">

			@if(Session::has('discipline.program.training_approved') && Session::get('discipline.program.training_approved') !== true)
				<div class="alert alert-danger">
					<strong>{!! Icon::exclamation_sign() !!} {{ Lang::choice('core::terms.facility_training', 1) }} not approved for Training</strong>
				</div>
			@endif

			<div class="form-group">
				{!! Form::label('login_discipline', 'Discipline') !!}
				{!! Form::text('login_discipline', Session::get('discipline.name'), ['disabled']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('login_license', 'License') !!}
				{!! Form::text('login_license', Session::get('discipline.program.license'), ['disabled']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('login_program', Lang::choice('core::terms.facility_training', 1)) !!}
				{!! Form::text('login_program', Session::get('discipline.program.name'), ['disabled']) !!}
			</div>
		</div>

		<h3>Identification</h3>
		{{-- Name --}}
		<div class="well">
			<div class="form-group row">
				<div class="col-md-4">
					{!! Form::label('first_readonly', 'First') !!}
					{!! Form::text('first_readonly', $instructor->first, ['disabled']) !!}
					<span class="text-danger">{{ $errors->first('first_readonly') }}</span>
				</div>
				<div class="col-md-4">
					{!! Form::label('middle_readonly', 'Middle') !!}
					{!! Form::text('middle_readonly', $instructor->middle, ['disabled']) !!}
					<span class="text-danger">{{ $errors->first('middle_readonly') }}</span>
				</div>
				<div class="col-md-4">
					{!! Form::label('last_readonly', 'Last') !!}
					{!! Form::text('last_readonly', $instructor->last, ['disabled']) !!}
					<span class="text-danger">{{ $errors->first('last_readonly') }}</span>
				</div>
			</div>
			<div class="form-group">
				{!! Form::label('birthdate', 'Birthdate') !!}
				{!! Form::text('birthdate', null, ['data-provide' => 'datepicker', 'data-date-end-date' => '-16y']) !!}
				<span class="text-danger">{{ $errors->first('birthdate') }}</span>
			</div>
		</div>

		<h3>Contact</h3>
		<div class="well">
			<div class="form-group">
				{!! Form::label('email', 'Email') !!}
				{!! Form::text('email', $instructor->user->email) !!}
				<span class="text-danger">{{ $errors->first('email') }}</span>
			</div>

			<hr>

			<div class="form-group">
				{!! Form::label('address', 'Address') !!}
				{!! Form::text('address') !!}
				<span class="text-danger">{{ $errors->first('address') }}</span>
			</div>
			<div class="form-group row">
				<div class="col-md-4">
					{!! Form::label('zip', 'Zipcode') !!} <small class="text-muted">Tab for City/State complete</small>
					{!! Form::text('zip') !!}
					<span class="text-danger">{{ $errors->first('zip') }}</span>
				</div>
				<div class="col-md-6">
					{!! Form::label('city', 'City') !!}
					{!! Form::text('city') !!}
					<span class="text-danger">{{ $errors->first('city') }}</span>
				</div>
				<div class="col-md-2">
					{!! Form::label('state', 'State') !!} <small class="text-muted">Like '{{ Config::get('core.client.abbrev') }}'</small>
					{!! Form::text('state') !!}
					<span class="text-danger">{{ $errors->first('state') }}</span>
				</div>
			</div>
		</div>

		{!! Form::hidden('first', $instructor->first) !!}		
		{!! Form::hidden('middle', $instructor->middle) !!}
		{!! Form::hidden('last', $instructor->last) !!}
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::instructors.sidebars.account')
	</div>
	{!! Form::close() !!}
@stop