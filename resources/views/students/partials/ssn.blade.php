@if(Auth::user()->ability(['Admin'], []))
	<div class="form-group">
		{!! Form::label('ssn', 'SSN') !!} @include('core::partials.required')
		{!! Form::text('ssn', $student->plain_ssn) !!}
		<span class="text-danger">{{ $errors->first('ssn') }}</span>
	</div>
@else
	<div class="form-group">
		{!! Form::label('ssn', 'SSN') !!}
		{!! Form::text('ssn', $student->plain_ssn, ['disabled']) !!}
	</div>
@endif