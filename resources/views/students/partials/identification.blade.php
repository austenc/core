<h3 id="identification">Identification</h3>
<div class="well">
	{{-- Name --}}
	@include('core::partials.name')

	{{-- SSN --}}
	@if(isset($student))
		@include('core::students.partials.ssn')
	@else
		<div class="form-group">
			{!! Form::label('ssn', 'SSN') !!} @include('core::partials.required') 
			@if(Auth::user()->ability(['Admin', 'Agency', 'Staff'], []) || Auth::user()->isRole('Instructor'))
				&nbsp;&nbsp;
				{!! Form::checkbox('ckbGenFakeSsn', 1, Input::old('ckbGenFakeSsn'), ['id' => 'ckbGenFakeSsn']) !!}
				<strong><small>Use Fake SSN</small></strong>
			@endif 
			{!! Form::text('ssn', '', ['placeholder' => '111223333']) !!}
			<span class="text-danger">{{ $errors->first('ssn') }}</span>
		</div>
		<div class="form-group">
			{!! Form::label('rev_ssn', 'Reverse SSN') !!} @include('core::partials.required')
			{!! Form::text('rev_ssn', '', ['placeholder' => '333322111']) !!}
			<span class="text-danger">{{ $errors->first('rev_ssn') }}</span>
		</div>
	@endif

	{{-- DOB --}}
	@include('core::partials.birthdate')
</div>