<h3 id="address-info">Address</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('address', 'Main Address') !!}
		<div>
			{{ $facility->address }}<br>
			{{ $facility->city }}, {{ $facility->state }} {{ $facility->zip }}
		</div>
	</div>

	@if( ! empty($facility->mail_address) || ! empty($facility->mail_city) || ! empty($facility->mail_state) || ! empty($facility->mail_zip))
	<div class="form-group">
		{!! Form::label('address', 'Mailing Address') !!}
		<div>
			{{ $facility->mail_address }}<br>
			{{ $facility->mail_city }}, {{ $facility->mail_state }} {{ $facility->mail_zip }}
		</div>
	</div>
	@endif
</div>