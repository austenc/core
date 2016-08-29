<h3>Choose Discipline & {{ Lang::choice('core::terms.facility_testing', 1) }}</h3>
<div class="row">
	<div class="col-md-12">
		<div class="well">
			{{-- Discipline --}}
			<div class="form-group">
				{!! Form::label('discipline_id', 'Event Discipline') !!} @include('core::partials.required')
				{!! Form::select('discipline_id', [0 => 'Select Discipline'] + $disciplines->lists('name', 'id')->all()) !!}
			</div>

			{{-- Test Site --}}
			@if(Auth::user()->isRole('Facility'))
				{!! Form::hidden('facility_id', Auth::user()->userable->id) !!}
			@else
				<div class="form-group">
					{!! Form::label('facility_id', Lang::choice('core::terms.facility_testing', 1)) !!} @include('core::partials.required')
					{!! Form::select('facility_id', [0 => 'Select '.Lang::choice('core::terms.facility_testing', 1)] + $testsites->lists('name', 'id')->all(), false, ['class' => 'sel-test-site']) !!}
				</div>
			@endif
		</div>
	</div>
</div>