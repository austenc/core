@extends('core::layouts.default')

@section('content')
	<div class="row">
		{!! Form::model($event, ['route' => ['events.update', $event->id], 'method' => 'PUT', 'files' => true, 'id' => 'frmEventEdit']) !!}
		{!! Form::hidden('eventID', $event->id, ['id' => 'eventID']) !!}
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>Edit Event <small>{{ $event->is_regional ? Lang::get('core::events.regional') : Lang::get('core::events.closed') }}</small></h1>
				</div>
				{!! HTML::backlink('events.index') !!}
			</div>

			{{-- Start Code --}}
			@if($event->ended)
				@include('core::events.warnings.ended')
			@else
				@include('core::events.warnings.start_code')
			@endif

			{{-- Tabs --}}
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active">
					<a href="#event-info" aria-controls="event info" role="tab" data-toggle="tab">
						{!! Icon::info_sign() !!} Event Info
					</a>
				</li>

				@if($event->exams->count() > 0)
					<li role="presentation">
						<a href="#event-knowledge-info" aria-controls="test events" role="tab" data-toggle="tab">
							{!! Icon::book() !!} Knowledge Tests
						</a>
					</li>
				@endif

				@if($event->skills->count() > 0)
					<li role="presentation">
						<a href="#event-skill-info" aria-controls="test events" role="tab" data-toggle="tab">
							{!! Icon::wrench() !!} Skill Tests
						</a>
					</li>
				@endif
			</ul>
			<div class="tab-content well">
			    <div role="tabpanel" class="tab-pane active" id="event-info">
			    	{{-- Show Warnings if Event hasn't ended --}}
					@if( ! $event->ended)
						@include('core::events.warnings.release_tests')
						@include('core::events.warnings.locked')
						@include('core::events.warnings.closed')
						@include('core::events.warnings.paper')
						@include('core::events.warnings.pending_ada')
					@endif

					{{-- DateTime --}}
					@include('core::events.partials.event_info')

					{{-- Test Site --}}
					@include('core::events.partials.test_site')

					{{-- Test Team --}}
					@include('core::events.partials.test_team')

					{{-- Attached Files --}}
					@include('core::events.partials.event_file')

					{{-- Comments --}}
					@include('core::partials.comments', ['record' => $event])
				</div>

			    <div role="tabpanel" class="tab-pane" id="event-knowledge-info">
					{{-- Knowledge Tests --}}
			    	@include('core::events.warnings.null_testform')
					@include('core::events.warnings.duplicate_testform')
					@include('core::events.partials.exams_knowledge')
				</div>

			    <div role="tabpanel" class="tab-pane" id="event-skill-info">
					{{-- Skill Tests --}}
			    	@include('core::events.warnings.null_skilltest')
					@include('core::events.warnings.duplicate_skilltest')
					@include('core::events.partials.exams_skill')
				</div>				
			</div>

			<div id="loading-contain" class="hide">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<br>
				</div>
			</div>
		</div>

		{!! Form::hidden('facility_id', $event->facility_id) !!}
		{!! Form::hidden('observer_id', $event->observer_id) !!}
		{!! Form::hidden('event_id', $event->id, ['id' => 'event-id']) !!}
		@foreach($event->observer->conflict_dates as $i => $conflict)
			{!! Form::hidden('conflict_dates['.$i.']', $conflict, ['class' => 'conflict-date']) !!}
		@endforeach

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			@include('core::events.sidebars.edit', $event)
		</div>
	</div>

	{!! Form::close() !!}

	{!! HTML::modal('change-knowledge-seats') !!}
	{!! HTML::modal('change-skill-seats') !!}
	{!! HTML::modal('change-password') !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/bootstrap-3-timepicker/js/bootstrap-timepicker.min.js') !!}
	{!! HTML::script('vendor/hdmaster/core/js/events/edit.js') !!}
@stop