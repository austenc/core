{{-- Started --}}
<div class="form-group">
	{!! Form::label('started', 'Started') !!} 
	@if( ! $training->archived)
		@include('core::partials.required')
	@endif

	@if(isset($disabled))
		{!! Form::text('started', null, ['disabled']) !!}
	@else
		{!! Form::text('started', null, ['data-provide' => 'datepicker', 'data-date-end-date' => '0d']) !!}
		<span class="text-danger">{{ $errors->first('started') }}</span>
	@endif
</div>

{{-- Ended --}}
<div class="form-group">
	{!! Form::label('ended', 'Ended') !!} @include('core::partials.required') 

	@if(isset($disabled))
		{!! Form::text('ended', null, ['disabled']) !!}
	@else
		{!! Form::text('ended', null, ['data-provide' => 'datepicker', 'data-date-end-date' => '0d']) !!} 
		<span class="text-danger">{{ $errors->first('ended') }}</span>
	@endif
</div>


{{-- Expires --}}
@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<div class="form-group">
		{!! Form::label('expires', 'Expires') !!}

		@if(isset($disabled))
			{!! Form::text('expires', null, ['disabled']) !!}
		@else
			{!! Form::text('expires', null) !!}
		@endif
	</div>
@elseif(Auth::user()->isRole('Agency') || Auth::user()->isRole('Instructor'))
	<div class="form-group">
		{!! Form::label('expires', 'Expires') !!}
		{!! Form::text('expires', null, ['disabled']) !!}
	</div>
@endif
