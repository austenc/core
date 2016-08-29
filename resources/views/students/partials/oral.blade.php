<div class="form-group">
	{!! Form::label('oral', 'Oral Tests?') !!}
	<div class="radio">
		<label>{!! Form::radio('is_oral', 1) !!} Yes</label>
	</div>
	<div class="radio">
		<label>{!! Form::radio('is_oral', 0) !!} No</label>
	</div>
	<span class="text-danger">{{ $errors->first('oral') }}</span>
</div>