<h3>Identification</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('name', 'Name') !!}
		<div>{{ $record->full_name }}</div>
	</div>

	<div class="form-group">
		{!! Form::label('username', 'Username') !!}
		<div>{{ $record->user->username }}</div>
	</div>

	@if(isset($record->license) && ! empty($record->license))
	<div class="form-group">
		{!! Form::label('license', 'License') !!}
		<div>{{ $record->license }}</div>
	</div>
	@endif

	@if(isset($record->birthdate) && ! empty($record->birthdate))
	<div class="form-group">
		{!! Form::label('birthdate', 'Birthdate') !!}
		<div>{{ $record->birthdate }}</div>
	</div>
	@endif

	@if(isset($record->gender) && ! empty($record->gender))
	<div class="form-group">
		{!! Form::label('gender', 'Gender') !!}
		<div>{{ $record->gender }}</div>
	</div>
	@endif
</div>