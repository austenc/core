<h3 id="login-info">Login Information</h3>
<div class="well">
	{{-- Username --}}
	<div class="form-group">
		{!! Form::label('username', 'Username') !!}
		@if(Auth::user()->ability(['Admin', 'Staff'], []))
			{!! Form::text('username', $record->user->username) !!}	
		@else
			{!! Form::text('username', $record->user->username, ['disabled']) !!}	
			{!! Form::hidden('username', $record->user->username) !!}	
		@endif
		<span class="text-danger">{{ $errors->first('username') }}</span>			
	</div>

	{{-- Change Password --}}
	@if(Auth::user()->can($name.'.update_password'))
		{{-- Password Fields --}}
		@include('core::partials.change_password')
	@endif	
</div>