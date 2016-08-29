<div class="form-group row">
	<div class="col-md-4">
		{!! Form::label('first', 'First') !!} @include('core::partials.required')
		{!! Form::text('first') !!}
		<span class="text-danger">{{ $errors->first('first') }}</span>
	</div>
	<div class="col-md-4">
		{!! Form::label('middle', 'Middle') !!}
		{!! Form::text('middle') !!}
		<span class="text-danger">{{ $errors->first('middle') }}</span>
	</div>
	<div class="col-md-4">
		{!! Form::label('last', 'Last') !!} @include('core::partials.required')
		{!! Form::text('last') !!}
		<span class="text-danger">{{ $errors->first('last') }}</span>
	</div>
</div>