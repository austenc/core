<div class="form-group">
	{!! Form::label('actions', 'Actions') !!}
	<span class="text-danger">{{ $errors->first('actions') }}</span>

	@foreach($avActions as $a)
		<div class="checkbox">
			<label>{!! Form::checkbox('actions[]', $a) !!} {{ $a }}</label>
		</div>
	@endforeach
</div>