<h3 id="contact-info">Contact</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('email', 'Email') !!}
		<div>{{ $student->user->email }}</div>
	</div>

	<div class="form-group">
		{!! Form::label('phone', 'Main Phone') !!}
		<div>{{ $student->phone ?: 'N/A' }}</div>
	</div>

	@if($student->alt_phone)
	<div class="form-group">
		{!! Form::label('alt_phone', 'Alt Phone') !!}
		<div>{{ $student->alt_phone }}</div>
	</div>
	@endif

	<div class="form-group">
		{!! Form::label('is_unlisted', 'Phone Unlisted?') !!}
		<div>{{ $student->is_unlisted ? 'Yes' : 'No' }}</div>
	</div>
</div>