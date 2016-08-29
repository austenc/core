<div class="form-group">
	{!! Form::label('max_seats', 'Max Test Seats') !!}
	{!! Form::text('max_seats') !!}
	<span class="text-danger">{{ $errors->first('max_seats') }}</span>
</div>