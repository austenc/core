<div class="form-group">
	{!! Form::label('birthdate', 'Birthdate') !!} 
	@if( ! isset($optional)) 
		@include('core::partials.required')
	@endif
	{!! Form::text('birthdate', null, ['data-provide' => 'datepicker', 'data-date-end-date' => '-14y']) !!}
	<span class="text-danger">{{ $errors->first('birthdate') }}</span>
</div>