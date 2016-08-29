<h3>Identification</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('name', 'Name') !!} @include('core::partials.required')
		{!! Form::text('name') !!}
		<span class="text-danger">{{ $errors->first('name') }}</span>
	</div>

	<div class="form-group">
		{!! Form::label('license', 'State License') !!}
		{!! Form::text('license') !!}
		<span class="text-danger">{{ $errors->first('license') }}</span>
	</div>

	<div class="form-group">
		{!! Form::label('don', 'Director of Nursing') !!}
		{!! Form::text('don') !!}
		<span class="text-danger">{{ $errors->first('don') }}</span>
	</div>
	
	<div class="form-group">
		{!! Form::label('administrator', 'Administrator') !!}
		{!! Form::text('administrator') !!}
		<span class="text-danger">{{ $errors->first('administrator') }}</span>
	</div>
</div>