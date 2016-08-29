@extends('core::layouts.default')

@section('content')
	<div class="row">
		{!! Form::model($student, ['route' => ['account.update', 'student', $student->id], 'method' => 'PUT']) !!}
		<div class="col-md-9">
			<h1>Your Account</h1>
	
			<h3>Login Information</h3>
			<div class="well">
				<div class="form-group">
					{!! Form::label('username', 'Username') !!}
					{!! Form::text('username', $student->user->username) !!}
					<span class="text-danger">{{ $errors->first('username') }}</span>
				</div>
				
				{{-- Password Fields --}}
				@include('core::partials.change_password')
			</div>

			<h3>Identification</h3>
			<div class="well">
				<div class="form-group row">
					<div class="col-md-4">
						{!! Form::label('first_readonly', 'First') !!}
						{!! Form::text('first_readonly', $student->first, ['disabled' => 'disabled']) !!}
						<span class="text-danger">{{ $errors->first('first_readonly') }}</span>
					</div>
					<div class="col-md-4">
						{!! Form::label('middle_readonly', 'Middle') !!}
						{!! Form::text('middle_readonly', $student->middle, ['disabled' => 'disabled']) !!}
						<span class="text-danger">{{ $errors->first('middle_readonly') }}</span>
					</div>
					<div class="col-md-4">
						{!! Form::label('last_readonly', 'Last') !!}
						{!! Form::text('last_readonly', $student->last, ['disabled' => 'disabled']) !!}
						<span class="text-danger">{{ $errors->first('last_readonly') }}</span>
					</div>
				</div>
				<div class="form-group">
					{!! Form::label('birthdate', 'Birthdate') !!} @include('core::partials.required')
					{!! Form::text('birthdate', null, ['data-provide' => 'datepicker', 'data-date-end-date' => '-16y']) !!}
					<span class="text-danger">{{ $errors->first('birthdate') }}</span>
				</div>
			</div>

			<h3>Contact</h3>
			<div class="well">
				<div class="form-group">
					{!! Form::label('email', 'Email') !!} @include('core::partials.required')
					{!! Form::text('email', $student->user->email) !!}
					<span class="text-danger">{{ $errors->first('email') }}</span>
				</div>
				<div class="form-group">
					{!! Form::label('phone', 'Phone') !!} @include('core::partials.required')
					{!! Form::text('phone') !!}
					<span class="text-danger">{{ $errors->first('phone') }}</span>
				</div>
				<div class="form-group">
					<div class="checkbox">
						<label>{!! Form::checkbox('is_unlisted', true, $student->is_unlisted) !!} Phone Unlisted?</label>
					</div>
				</div>
				<div class="form-group">
					{!! Form::label('alt_phone', 'Alternate Phone') !!}
					{!! Form::text('alt_phone') !!}
					<span class="text-danger">{{ $errors->first('alt_phone') }}</span>
				</div>

				<hr>

				<div class="form-group">
					{!! Form::label('address', 'Address') !!} @include('core::partials.required')
					{!! Form::text('address') !!}
					<span class="text-danger">{{ $errors->first('address') }}</span>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						{!! Form::label('zip', 'Zipcode') !!} @include('core::partials.required') <small class="text-muted">Tab for City/State complete</small>
						{!! Form::text('zip') !!}
						<span class="text-danger">{{ $errors->first('zip') }}</span>
					</div>
					<div class="col-md-6">
						{!! Form::label('city', 'City') !!} @include('core::partials.required')
						{!! Form::text('city') !!}
						<span class="text-danger">{{ $errors->first('city') }}</span>
					</div>
					<div class="col-md-2">
						{!! Form::label('state', 'State') !!} @include('core::partials.required') <small class="text-muted">Like '{{ Config::get('core.client.abbrev') }}'</small>
						{!! Form::text('state') !!}
						<span class="text-danger">{{ $errors->first('state') }}</span>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-3 sidebar">
			<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
				{!! Button::success(Icon::refresh().' Update')->submit() !!}
			</div>
		</div>

		{!! Form::hidden('first', $student->first) !!}		
		{!! Form::hidden('middle', $student->middle) !!}
		{!! Form::hidden('last', $student->last) !!}
		{!! Form::hidden('gender', $student->gender) !!}
	{!! Form::close() !!}
	</div>
@stop