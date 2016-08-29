@if(Auth::user()->isRole('Instructor'))
	{!! Form::hidden('instructor_id', Auth::user()->userable->id) !!}
@else
	<div class="form-group row">
		<div class="col-md-12">
			{!! Form::label('instructor_id', Lang::choice('core::terms.instructor', 1)) !!} @include('core::partials.required')
			{!! Form::select('instructor_id', [0 => 'Select '.Lang::choice('core::terms.instructor', 1)]) !!}
			<span class="text-danger">{{ $errors->first('instructor_id') }}</span>
		</div>
	</div>
@endif