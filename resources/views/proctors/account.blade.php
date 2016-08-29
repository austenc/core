@extends('core::layouts.default')

@section('content')
	<div class="row">
	{!! Form::model($proctor, ['route' => ['account.update', 'proctor', $proctor->id], 'method' => 'PUT']) !!}
		<div class="col-md-9">
			<h1>Your Account</h1>

			<h3>Login Information</h3>
			<div class="well">
				<div class="form-group">
					{!! Form::label('username', 'Username') !!}
					{!! Form::text('username', $proctor->user->username) !!}
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
						{!! Form::text('first_readonly', $proctor->first, ['disabled' => 'disabled']) !!}
						<span class="text-danger">{{ $errors->first('first_readonly') }}</span>
					</div>
					<div class="col-md-4">
						{!! Form::label('middle_readonly', 'Middle') !!}
						{!! Form::text('middle_readonly', $proctor->middle, ['disabled' => 'disabled']) !!}
						<span class="text-danger">{{ $errors->first('middle_readonly') }}</span>
					</div>
					<div class="col-md-4">
						{!! Form::label('last_readonly', 'Last') !!}
						{!! Form::text('last_readonly', $proctor->last, ['disabled' => 'disabled']) !!}
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
					{!! Form::text('email', $proctor->user->email) !!}
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
		</div>

		<div class="col-md-3 sidebar">
			<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
				{!! Button::success(Icon::refresh().' Update')->submit() !!}
			</div>
		</div>
		
		{!! Form::hidden('first', $proctor->first) !!}		
		{!! Form::hidden('middle', $proctor->middle) !!}
		{!! Form::hidden('last', $proctor->last) !!}
	{!! Form::close() !!}
	</div>
@stop