<div class="form-group">
	{!! Form::label('password', 'New Password') !!}
	{!! Form::text('password', null, ['data-mask']) !!}
	<span class="text-danger">{{ $errors->first('password') }}</span>			
</div>
<div class="form-group">
	{!! Form::label('password_confirmation', 'Confirm Password') !!}
	{!! Form::text('password_confirmation', null, ['data-mask']) !!}
	<span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
</div>