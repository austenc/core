<h3 id="address-info">Address</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('address', 'Main Address') !!}
		<div>
			{{ $student->address }}<br>
			{{ $student->city }}, {{ $student->state }} {{ $student->zip }}
		</div>
	</div>
</div>