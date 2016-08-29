<h3>Contact</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('email', 'Email') !!}
		<div>{{ $record->user->email }}</div>
	</div>
	<div class="form-group">
		{!! Form::label('phone', 'Main Phone') !!}
		<div>{{ $record->phone ?: 'N/A' }}</div>
	</div>
	@if($record->alt_phone)
	<div class="form-group">
		{!! Form::label('alt_phone', 'Alt Phone') !!}
		<div>{{ $record->alt_phone }}</div>
	</div>
	@endif
	<div class="form-group">
		{!! Form::label('address', 'Address') !!}
		<div>
			{{ $record->address }}<br>
			{{ $record->city }}, {{ $record->state }} {{ $record->zip }}
		</div>
	</div>
</div>