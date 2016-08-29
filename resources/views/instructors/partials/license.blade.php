<div class="form-group">
	{!! Form::label('license', 'License') !!}
	{!! Form::text('license') !!}
	<span class="text-danger">{{ $errors->first('birthdate') }}</span>
</div>

@if(isset($instructor))
	@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<div class="form-group">
		{!! Form::label('expires', 'Expires') !!}
		{!! Form::text('expires', $instructor->expires, ['data-provide' => 'datepicker', 'data-date-start-date' => '+1day']) !!}
		<span class="text-danger">{{ $errors->first('expires') }}</span>
	</div>
	@else
		{!! Form::hidden('expires', $instructor->expires) !!}	
	@endif
@endif