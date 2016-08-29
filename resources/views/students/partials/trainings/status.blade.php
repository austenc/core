<div class="form-group">
	{!! Form::label('status', 'Status') !!}
	{!! Form::select('status', $trStatusOpts) !!}
	<span class="text-danger">{{ $errors->first('status') }}</span>
</div>

<div class="form-group">
	{!! Form::label('reason', 'Reason') !!} @include('core::partials.required') 
	{!! Form::select('reason', $trFailReasons) !!}
	<span class="text-danger">{{ $errors->first('reason') }}</span>
</div>