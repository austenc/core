@extends('core::layouts.default')

@section('content')
	<h1>Edit Account - {{ $user->username }}</h1>
	{!! Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'PUT']) !!}
		<h3>Identification</h3>
		<div class="well">
			{{-- Name --}}
			<div class="form-group row">
				<div class="col-md-4">
					{!! Form::label('username', 'Username') !!}
					{!! Form::text('username', $user->username, ['disabled' => 'disabled']) !!}
					<span class="text-danger">{{ $errors->first('username') }}</span>
				</div>
				<div class="col-md-4">
					{!! Form::label('email', 'email') !!}
					{!! Form::text('email') !!}
					<span class="text-danger">{{ $errors->first('email') }}</span>
				</div>
			</div>

			{{-- Password Fields --}}
			<div class="form-group">
				{!! Form::label('password', 'New Password') !!}
				{!! Form::text('password', '', ['data-mask']) !!}
				<span class="text-danger">{{ $errors->first('password') }}</span>			
			</div>
			<div class="form-group">
				{!! Form::label('password_confirmation', 'Confirm Password') !!}
				{!! Form::text('password_confirmation', null, ['data-mask']) !!}
				<span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
			</div>
		</div>
		{!! Button::success(Icon::ok().' Submit')->submit() !!}		
	{!! Form::close() !!}
@stop