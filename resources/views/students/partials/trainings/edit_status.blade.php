<div class="form-group">
	{!! Form::label('status', 'Status') !!} @include('core::partials.required')
	
	@if($training->archived)
		{!! Form::select('status', $training->student->training_status, $training->status, ['disabled']) !!}
	@else
		{!! Form::select('status', $training->student->training_status, $training->status) !!}
		<span class="text-danger">{{ $errors->first('status') }}</span>
	@endif
</div>

{{-- Failed Reason --}}
<div class="form-group">
	{!! Form::label('reason', 'Reason') !!} @include('core::partials.required') 
	
	@if($training->archived)
		{!! Form::select('reason', $failReasons, $training->reason, ['disabled']) !!}
	@else
		{!! Form::select('reason', $failReasons, $training->reason) !!}
		<span class="text-danger">{{ $errors->first('reason') }}</span>
	@endif
</div>