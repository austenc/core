<h3>Login Password</h3>
<div class="well">
	<div class="form-group">
		<label for="password" class="control-label">
			@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
				<a data-href="{{{ route('generate.password') }}}" id="gen-pwd" title="Generate Password" data-toggle="tooltip">Password</a>
			@else
				Password
			@endif
		</label> @include('core::partials.required') 
		{!! Form::text('password', null, ['data-mask']) !!}
		<span class="text-danger">{{ $errors->first('password') }}</span>			
	</div>

	<div class="form-group">
		{!! Form::label('password_confirmation', 'Confirm Password') !!} @include('core::partials.required')
		{!! Form::text('password_confirmation', null, ['data-mask']) !!}
		<span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
	</div>
</div>