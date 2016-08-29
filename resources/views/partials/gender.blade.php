<div class="form-group">
	{!! Form::label('gender', 'Gender') !!} @include('core::partials.required')
	<div class="radio">
		<label>{!! Form::radio('gender', 'Male')!!} Male</label>
	</div>
	<div class="radio">
		<label>{!! Form::radio('gender', 'Female') !!} Female</label>
	</div>
	<span class="text-danger">{{ $errors->first('gender') }}</span>
</div>