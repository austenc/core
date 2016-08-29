@extends('core::layouts.default')

@section('content')
	<div class="row">
		{!! Form::model($facility, ['route' => ['facilities.archived.update', $facility->id], 'method' => 'POST']) !!}
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>View Archived {{ Lang::choice('core::terms.facility', 1) }}</h1>
				</div>
				{!! HTML::backlink('facilities.index') !!}
			</div>

			{{-- Warnings --}}
			@include('core::warnings.archived')
	
			{{-- Identification --}}
			@include('core::facilities.partials.arch_identification')
	
			{{-- Contact --}}
			@include('core::facilities.partials.arch_contact')
	
			{{-- Address --}}
			@include('core::facilities.partials.arch_address')

			{{-- Other --}}
			@include('core::facilities.partials.arch_other')
	
			{{-- Trained Students --}}
			@include('core::facilities.partials.arch_trained_students')

			{{-- Instructors --}}
			@include('core::facilities.partials.arch_people', ['people' => $facility->allInstructors, 'route' => 'instructors.edit'])

			{{-- People --}}
			@if(Auth::user()->can('facilities.view_people') && is_array($facility->actions) && in_array('Testing', $facility->actions))
				{{-- Observers --}}
				<h3 id="facility-observers">{{ Lang::choice('core::terms.observer', 2) }}</h3>
				@include('core::facilities.partials.arch_people', ['people' => $facility->allObservers, 'route' => 'observers.edit'])

				{{-- Proctors --}}
				<h3 id="facility-proctors">{{ Lang::choice('core::terms.proctor', 2) }}</h3>
				@include('core::facilities.partials.arch_people', ['people' => $facility->allProctors, 'route' => 'proctors.edit'])

				{{-- Actors --}}
				<h3 id="facility-actors">{{ Lang::choice('core::terms.actor', 2) }}</h3>
				@include('core::facilities.partials.arch_people', ['people' => $facility->allActors, 'route' => 'actors.edit'])
			@endif

			{{-- Test Events --}}
			@if(Auth::user()->can('facilities.view_events') && is_array($facility->actions) && in_array('Testing', $facility->actions))
				<h3 id="facility-events">Test Events</h3>
				@include('core::person.partials.arch_test_events', ['events' => $facility->events])
			@endif
			
			{{-- Affiliated Facilities --}}
			@include('core::facilities.partials.arch_affiliated')

			{{-- Timestamps --}}
			@include('core::partials.timestamps', ['record' => $facility])

			{{-- Comments --}}
			@include('core::partials.comments', ['record' => $facility])
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			@include('core::facilities.sidebars.archived')
		</div>
	{!! Form::close() !!}
	</div>
@stop

@section('scripts')
	<script type="text/javascript">
		$('input[type=checkbox]:checked').parents('tr').addClass('success');
	</script>
@stop