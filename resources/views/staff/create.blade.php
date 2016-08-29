@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => $routeBase . '.store']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Create {{ $type }}</h1>
			</div>
			{!! HTML::backlink($routeBase . '.index') !!}
		</div>

		<h3>Identification</h3>
		<div class="well">
			{{-- Name --}}
			<div class="form-group row">
				<div class="col-md-4">
					{!! Form::label('first', 'First') !!} @include('core::partials.required')
					{!! Form::text('first') !!}
					<span class="text-danger">{{ $errors->first('first') }}</span>
				</div>
				<div class="col-md-4">
					{!! Form::label('middle', 'Middle') !!}
					{!! Form::text('middle') !!}
					<span class="text-danger">{{ $errors->first('middle') }}</span>
				</div>
				<div class="col-md-4">
					{!! Form::label('last', 'Last') !!} @include('core::partials.required')
					{!! Form::text('last') !!}
					<span class="text-danger">{{ $errors->first('last') }}</span>
				</div>
			</div>
			{{-- Email --}}
			<div class="form-group">
				{!! Form::label('email', 'Email') !!} @include('core::partials.required')
				{!! Form::text('email') !!}
				<span class="text-danger">{{ $errors->first('email') }}</span>
			</div>
		</div>

		<h3>Login Password</h3>
		<div class="well">
			<div class="form-group">
			    <label for="password">{{{ Lang::get('confide::confide.password') }}}</label> @include('core::partials.required')
			    <input class="form-control" placeholder="{{{ Lang::get('confide::confide.password') }}}" type="text" data-mask name="password" id="password">
			</div>
			<div class="form-group">
			    <label for="password_confirmation">{{{ Lang::get('confide::confide.password_confirmation') }}}</label> @include('core::partials.required')
			    <input class="form-control" placeholder="{{{ Lang::get('confide::confide.password_confirmation') }}}" type="text" data-mask name="password_confirmation" id="password_confirmation">
			</div>
		</div>
	</div>

	<div class="col-md-3 sidebar">
        <div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
            {!! Button::success(Icon::plus_sign().' Create')->submit() !!}
        </div>
    </div>
	{!! Form::close() !!}
@stop
