<h3 id="identification">Identification</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('name', 'Name') !!}
		<div>{{ $student->full_name }}</div>
	</div>

	@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<div class="form-group">
		{!! Form::label('ssn', 'SSN') !!}
		<div>{{ $student->plain_ssn }}</div>
	</div>
	@endif

	<div class="form-group">
		{!! Form::label('username', 'Username') !!}
		<div>{{ $student->user->username }}</div>
	</div>

	<div class="form-group">
		{!! Form::label('birthdate', 'Birthdate') !!}
		<div>{{ $student->birthdate }}</div>
	</div>
</div>