@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => ['students.merge', $student->id], 'class' => 'form-horizontal']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Existing {{ Lang::choice('core::terms.student', 1) }} Found</h1>
			</div>
		</div>

		{{-- Warnings --}}
		<div class="alert alert-warning">
			{!! Icon::flag() !!} <strong>Duplicate</strong> Matching {{ Lang::choice('core::terms.student', 1) }} record with SSN already exists
		</div>

		<h3>
			New {{ Lang::choice('core::terms.student', 1) }}
			<small>Previously entered info</small>
		</h3>
		<div class="well">
			<div class="form-group">
				{{-- Last --}}
				<label for="name" class="col-xs-2 control-label">Last Name</label>
				<div class="col-xs-2">
			  		<p class="form-control-static">{{ Input::old('last') }}</p>
				</div>
			
				{{-- First --}}
				<label for="name" class="col-xs-2 control-label">First Name</label>
				<div class="col-xs-2">
			  		<p class="form-control-static">{{ Input::old('first') }}</p>
				</div>
			
				{{-- Middle --}}
				<label for="name" class="col-xs-2 control-label">Middle Name</label>
				<div class="col-xs-2">
			  		<p class="form-control-static">{{ Input::old('middle') }}</p>
				</div>
			</div>
			<div class="form-group">
				@if(Auth::user()->ability(['Admin', 'Staff'], []))
					<label class="control-label col-xs-2">SSN</label>
					<div class="col-xs-2">
				  		<p class="form-control-static">{{ Formatter::format_ssn(Input::old('ssn')) }}</p>
					</div>
				@endif
				
				<label class="control-label col-xs-2">Date of Birth</label>
				<div class="col-xs-2">
			  		<p class="form-control-static">{{ Input::old('birthdate') }}</p>
				</div>
				
				<label class="control-label col-xs-2">Gender</label>
				<div class="col-xs-2">
			  		<p class="form-control-static">{{ ucfirst(Input::old('gender')) }}</p>
				</div>
			</div>
			<div class="form-group">
				{{-- Phone and Alt. Phone --}}
				<label class="control-label col-xs-2">Phone</label>
				<div class="col-xs-2">
					<p class="form-control-static">{{ Input::old('phone') }}</p>
				</div>
				@if(Input::old('alt_phone'))
					<label for="alt_phone" class="control-label col-xs-2">Alt Phone</label>
					<div class="col-xs-2">
						<p class="form-control-static">{{ Input::old('alt_phone') }}</p>
					</div>
				@endif					
			</div>
			<div class="form-group">
				{{-- Address --}}
				<label class="control-label col-xs-2">Address</label>
				<div class="col-xs-8">
					<p class="form-control-static">
						{{ Input::old('address') }} <br>
						{{ Input::old('city') }}, {{ Input::old('state') }} {{ Input::old('zip') }}
					</p>
				</div>
			</div>
			<div class="form-group">
				{{-- Training Type --}}
				<label for="training" class="col-xs-2 control-label">Training</label>
				<div class="col-xs-4">
			  		<p class="form-control-static">{{ $training->name }}</p>
				</div>

				{{-- Training Program --}}
				<label for="status" class="col-xs-2 control-label">Status</label>
				<div class="col-xs-2">
			  		<p class="form-control-static">{{ ucfirst(Input::old('status')) }}</p>
				</div>
			</div>
			<div class="form-group">
				{{-- Training Program --}}
				<label for="program" class="col-xs-2 control-label">{{ Lang::choice('core::terms.facility_training', 1) }}</label>
				<div class="col-xs-4">
			  		<p class="form-control-static">{{ $program->name }}</p>
				</div>

				{{-- Training Program --}}
				<label for="started" class="col-xs-2 control-label">Started</label>
				<div class="col-xs-2">
			  		<p class="form-control-static">{{ Input::old('started') }}</p>
				</div>
			</div>
		</div>

		<h3>
			Archived {{ Lang::choice('core::terms.student', 1) }}
			<small>Record matching SSN {{ $student->plain_ssn }}</small>
		</h3>
		<div class="well">
			<div class="form-group">
				{{-- Last --}}
				<label for="name" class="col-xs-2 control-label">Last Name</label>
				<div class="col-xs-2">
			  		<p class="form-control-static">{{ $student->last }}</p>
				</div>
			
				{{-- First --}}
				<label for="name" class="col-xs-2 control-label">First Name</label>
				<div class="col-xs-2">
			  		<p class="form-control-static">{{ $student->first }}</p>
				</div>
			
				{{-- Middle --}}
				<label for="name" class="col-xs-2 control-label">Middle Name</label>
				<div class="col-xs-2">
			  		<p class="form-control-static">{{ $student->middle }}</p>
				</div>
			</div>
			<div class="form-group">
				@if(Auth::user()->ability(['Admin', 'Staff'], []))
					<label class="control-label col-xs-2">SSN</label>
					<div class="col-xs-2">
				  		<p class="form-control-static">{{ $student->plain_ssn }}</p>
					</div>
				@endif
				
				<label class="control-label col-xs-2">Date of Birth</label>
				<div class="col-xs-2">
			  		<p class="form-control-static">{{ $student->birthdate }}</p>
				</div>
				
				<label class="control-label col-xs-2">Gender</label>
				<div class="col-xs-2">
			  		<p class="form-control-static">{{ $student->gender }}</p>
				</div>
			</div>
			<div class="form-group">
				{{-- Phone and Alt. Phone --}}
				<label class="control-label col-xs-2">Phone</label>
				<div class="col-xs-2">
					<p class="form-control-static">{{ $student->phone }}</p>
				</div>
				@if($student->alt_phone)
					<label for="alt_phone" class="control-label col-xs-2">Alt Phone</label>
					<div class="col-xs-2">
						<p class="form-control-static">{{ $student->alt_phone }}</p>
					</div>
				@endif					
			</div>
			<div class="form-group">
				{{-- Address --}}
				<label class="control-label col-xs-2">Address</label>
				<div class="col-xs-8">
					<p class="form-control-static">
						{{ $student->address }} <br>
						{{ $student->city }}, {{ $student->state }} {{ $student->zip }}
					</p>
				</div>
			</div>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			{!! Button::success(Icon::random().' Merge')->submit() !!}
			
			<a href="{{ URL::previous() }}" class="btn btn-default">{!! Icon::arrow_left() !!} Cancel</a>
		</div>
	</div>

	@if(Input::old())
		{!! Form::hidden('first', Input::old('first')) !!}
		{!! Form::hidden('middle', Input::old('middle')) !!}
		{!! Form::hidden('last', Input::old('last')) !!}
		{!! Form::hidden('ssn', Input::old('ssn')) !!}
		{!! Form::hidden('birthdate', Input::old('birthdate')) !!}
		{!! Form::hidden('gender', Input::old('gender')) !!}
		{!! Form::hidden('phone', Input::old('phone')) !!}
		{!! Form::hidden('alt_phone', Input::old('alt_phone')) !!}
		{!! Form::hidden('address', Input::old('address')) !!}
		{!! Form::hidden('city', Input::old('city')) !!}
		{!! Form::hidden('state', Input::old('state')) !!}
		{!! Form::hidden('zip', Input::old('zip')) !!}
		{!! Form::hidden('comments', Input::old('comments')) !!}

		{!! Form::hidden('password', Input::old('password')) !!}
		{!! Form::hidden('password_confirmation', Input::old('password_confirmation')) !!}
		{!! Form::hidden('email', Input::old('email')) !!}

		{{-- Initial Training Info --}}
		{!! Form::hidden('status', Input::old('status')) !!}
		{!! Form::hidden('reason', Input::old('reason')) !!}
		{!! Form::hidden('discipline_id', Input::old('discipline_id')) !!}
		{!! Form::hidden('facility_id', Input::old('facility_id')) !!}
		{!! Form::hidden('training_id', Input::old('training_id')) !!}
		{!! Form::hidden('instructor_id', Input::old('instructor_id')) !!}
		{!! Form::hidden('started', Input::old('started')) !!}
		{!! Form::hidden('ended', Input::old('ended')) !!}
		{!! Form::hidden('expires', Input::old('expires')) !!}
		{!! Form::hidden('classroom_hours', Input::old('classroom_hours')) !!}
		{!! Form::hidden('distance_hours', Input::old('distance_hours')) !!}
		{!! Form::hidden('lab_hours', Input::old('lab_hours')) !!}
		{!! Form::hidden('traineeship_hours', Input::old('traineeship_hours')) !!}
		{!! Form::hidden('clinical_hours', Input::old('clinical_hours')) !!}
	@endif

</form>
@stop