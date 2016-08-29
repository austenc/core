<h3 id="login-info">Login Information</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('username', 'Username') !!}
		{!! Form::text('username', $student->user->username, ['disabled' => 'disabled']) !!}
		<span class="text-danger">{{ $errors->first('username') }}</span>			
	</div>
	@if(Auth::user()->can('students.update_password'))
		{{-- Password Fields --}}
		@include('core::partials.change_password')
	@endif	
</div>