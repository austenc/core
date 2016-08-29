@extends('core::layouts.default')

@section('content')
	{!! Form::model($event, ['route' => ['events.update_pending', $event->id], 'method' => 'PUT']) !!}
		<div class="row">
			<div class="col-md-9">
				<div class="row">
					<div class="col-xs-8">
						<h1>Edit Pending Event</h1>
					</div>
					{!! HTML::backlink('events.index') !!}
				</div>

				{{-- Warnings --}}
				@include('core::events.warnings.pending_test_team_select')

				{{-- DateTime --}}
				@include('core::events.partials.event_info')

				{{-- Test Site --}}
				@include('core::events.partials.test_site')

				{{-- Testing Team --}}
				@include('core::events.partials.pending_test_team')

				{{-- Knowledge/Skill Exams --}}
				@include('core::events.partials.pending_exams')

				{{-- Comments --}}
				@include('core::partials.comments', ['record' => $event])
			</div>

			{{-- Sidebar --}}
			<div class="col-md-3 sidebar">
				<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
					{!! Button::success(Icon::refresh().' Update ')->submit() !!}
					<button type="submit" name="publish_pending" value="true" class="btn btn-warning" data-confirm="Publish this pending Test Event?<br><br>Are you sure?">
						{!! Icon::book().' Publish' !!}
					</button>
				</div>
			</div>
		</div>
		{!! Form::hidden('pendingevent_id', $event->id) !!}
		{!! Form::hidden('pendingevent_testdate', $event->test_date, ['id' => 'test-date']) !!}
	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/bootstrap-3-timepicker/js/bootstrap-timepicker.min.js') !!}
	{!! HTML::script('vendor/hdmaster/core/js/events/pending.js') !!}
@stop