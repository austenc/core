@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'events.creating']) !!}
	<div class="row">
		<div class="col-xs-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>Create Event</h1>
				</div>
				{!! HTML::backlink('events.index') !!}
			</div>

			{{-- Discipline --}}
			@include('core::events.partials.discipline')

			{{-- Event DateTime --}}
			@include('core::events.partials.datetime')

			{{-- Exam Seats --}}
			@include('core::events.partials.choose_exam_seats')

			{{-- Comments --}}
			@include('core::partials.comments')
		</div>

		<!-- Sidebar -->
		<div class="col-md-3 sidebar">
			@include('core::events.sidebars.create')
		</div>
	</div>
	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/bootstrap-3-timepicker/js/bootstrap-timepicker.min.js') !!}
	{!! HTML::script('vendor/hdmaster/core/js/events/create.js') !!}
@stop