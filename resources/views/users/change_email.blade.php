@extends('core::layouts.full')

@section('content')
	{!! Form::open(['route' => ['email.change']]) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Change Email</h1>
			</div>
		</div>

		<div class="alert alert-warning">
			{!! Icon::flag() !!} <strong>Email</strong> You must change your email address before you can continue.
		</div>

		<h3>Email Address</h3>
		<div class="well">
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('curr_email', 'Current Email') !!}
					{!! Form::text('curr_email', $user->email, ['disabled']) !!}
				</div>
			</div>

			<hr>
			
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('new_email', 'New Email') !!} @include('core::partials.required')
					{!! Form::text('new_email') !!}
					<span class="text-danger">{{ $errors->first('new_email') }}</span>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('confirm_email', 'Confirm Email') !!} @include('core::partials.required')
					{!! Form::text('confirm_email') !!}
					<span class="text-danger">{{ $errors->first('confirm_email') }}</span>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-3 sidebar">
		{!! Button::success(Icon::refresh().' Update')->submit() !!}
	</div>
	{!! Form::close() !!}
@stop