@extends('core::layouts.default')

@section('content')
	<h1>Your Account</h1>

	{!! Form::model($agency, ['route' => ['account.update', 'agency', $agency->id], 'method' => 'PUT']) !!}
		<h3>Login Information</h3>
		<div class="well">
			<div class="form-group">
				{!! Form::label('username', 'Username') !!}
				{!! Form::text('username', $agency->user->username, ['disabled' => 'disabled']) !!}
				<span class="text-danger">{{ $errors->first('username') }}</span>
			</div>

			{{-- Password Fields --}}
			@include('core::partials.change_password')
		</div>

		<h3>Identification</h3>
		<div class="well">
			{{-- Name --}}
			<div class="form-group row">
				<div class="col-md-6">
					{!! Form::label('first_readonly', 'First') !!}
					{!! Form::text('first_readonly', $agency->first, ['disabled' => 'disabled']) !!}
					<span class="text-danger">{{ $errors->first('first_readonly') }}</span>
				</div>
				<div class="col-md-6">
					{!! Form::label('last_readonly', 'Last') !!}
					{!! Form::text('last_readonly', $agency->last, ['disabled' => 'disabled']) !!}
					<span class="text-danger">{{ $errors->first('last_readonly') }}</span>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('email', 'Email') !!}
					{!! Form::text('email', $agency->user->email) !!}
					<span class="text-danger">{{ $errors->first('email') }}</span>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('phone', 'Phone') !!}
					{!! Form::text('phone') !!}
					<span class="text-danger">{{ $errors->first('phone') }}</span>
				</div>
			</div>
		</div>

		{!! Button::success(Icon::refresh().' Update')->submit() !!}
	{!! Form::close() !!}
@stop