@extends('core::layouts.default')

@section('content')
	{!! Form::model($facility, ['route' => ['facilities.update', $facility->id], 'method' => 'PUT', 'files' => true]) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>
					Edit {{ Lang::choice('core::terms.facility', 1) }}
					<small class="hidden-xs">
						{{-- Testing --}}
						@if(is_array($facility->actions) && in_array('Testing', $facility->actions))
							@if($facility->testingCert)
								{{ $facility->testingCert->abbrev }}
							@endif
							{{ Lang::choice('core::terms.facility_testing', 1) }}
						@endif

						{{-- Training --}}
						@if(is_array($facility->actions) && in_array('Training', $facility->actions))
							@if(is_array($facility->actions) && in_array('Testing', $facility->actions))
							/ 
							@endif
							{{ Lang::choice('core::terms.facility_training', 1) }}
						@endif
					</small>
				</h1>
			</div>
			{!! HTML::backlink('facilities.index') !!}
		</div>

		<!-- Tabs -->
		@include('core::facilities.tabs.edit')
		<div class="tab-content well">
			<!-- Facility Info -->
		    <div role="tabpanel" class="tab-pane active" id="facility-info">
		    	{{-- Warnings --}}
		    	@include('core::warnings.active_hold', ['hold' => in_array('hold', explode(",", $facility->status))])
				@include('core::warnings.active_lock', ['lock' => in_array('locked', explode(",", $facility->status))])
		    	@include('core::warnings.fake_email', ['user' => $facility->user])
		    	@include('core::facilities.warnings.actions')
		    	@include('core::facilities.warnings.no_disciplines')
		    	@include('core::facilities.warnings.agency_only')

		    	{{-- Identification --}}
		    	@include('core::facilities.partials.identification')

		    	{{-- Status --}}
				@include('core::facilities.partials.record_status', ['record' => $facility])

		    	{{-- Contact --}}
		    	@include('core::partials.contact', ['name' => 'facility', 'record' => $facility])
		    	
		    	{{-- Address --}}
		    	@include('core::facilities.partials.address')

		    	{{-- Other --}}
		    	@include('core::facilities.partials.other')		 

		    	{{-- Driving Directions --}}
		    	@include('core::facilities.partials.driving_directions')

		    	{{-- Login Info --}}
		    	@include('core::partials.login_info', ['record' => $facility, 'name' => 'facilities'])
		    		
		    	{{-- Timestamps --}}
		    	@include('core::partials.timestamps', ['record' => $facility])

		    	{{-- Comments --}}
		    	@include('core::partials.comments', ['record' => $facility])
		    </div>

		    {{-- Test Events --}}
		    @if( ! $facility->events->isEmpty())
			    <div role="tabpanel" class="tab-pane" id="facility-events">
			    	{{-- Test Events --}}
			    	@include('core::facilities.partials.testing_events', ['events' => $facility->events])
			    </div>
		    @endif

			{{-- Discipline Info --}}
			@if( ! $facility->allDisciplines->isEmpty())
				@foreach($facility->allDisciplines as $d)
					@if($d->pivot->active || Auth::user()->ability(['Admin', 'Staff'], []))

					    {{-- License/Parent Info + Instructors + Test Team --}}
						<div role="tabpanel" class="tab-pane" id="facility-discipline-{{{ strtolower($d->abbrev) }}}-info">
					    	@include('core::facilities.partials.discipline_info', ['discipline' => $d])
					    </div>

					    {{-- Instructors --}}
						<div role="tabpanel" class="tab-pane" id="facility-discipline-{{{ strtolower($d->abbrev) }}}-instructors">
					    	@include('core::facilities.partials.discipline_instructors', [
								'discipline'  => $d,
								'instructors' => $instructors[$d->id]
					    	])
					    </div>

					    {{-- Test Team --}}
						<div role="tabpanel" class="tab-pane" id="facility-discipline-{{{ strtolower($d->abbrev) }}}-testteam">
					    	@include('core::facilities.partials.discipline_testteam', [
								'discipline' => $d,
								'testteam'   => $testteam[$d->id]
					    	])
					    </div>

						{{-- Discipline Students --}}
					    <div role="tabpanel" class="tab-pane" id="facility-discipline-{{{ strtolower($d->abbrev) }}}-students">
							@include('core::facilities.partials.students', [
								'discipline' => $d,
								'students'   => $students[$d->id]
							])
						</div>
					@endif
				@endforeach
			@endif

		</div>
	</div>
	
	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::facilities.sidebars.edit')
	</div>
</div>

{!! Form::close() !!}
{!! HTML::modal('add-discipline') !!}
{!! HTML::modal('add-affiliate') !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/facilities/edit.js') !!}
@stop