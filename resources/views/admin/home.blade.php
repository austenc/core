@extends('core::layouts.default')

@section('content')

	<div class="row">
		
		<div class="col-md-3 col-md-push-9">
			
			<h3>Quick Links</h3>
			<div class="list-group">
				<a href="{{ route('notifications') }}" class="list-group-item">
					{!! Icon::inbox() !!} Inbox
				</a>
				<a href="{{ route('account') }}" class="list-group-item">
					{!! Icon::pencil() !!} Your Profile
				</a>
				<a href="{{ route('events.create') }}" class="list-group-item">
					{!! Icon::plus_sign() !!} Create Test Event
				</a>
				<a href="{{ route('events.calendar') }}" class="list-group-item">
					{!! Icon::calendar() !!} Event Calendar
				</a>
			</div>

			<hr>

			<h3>People</h3>
			<div class="list-group">
				<a href="{{{ route('students.index') }}}" class="list-group-item">
					{!! Icon::user() !!} Manage {{ Lang::choice('core::terms.student', 2) }} 
				</a>
				<a href="{{{ route('instructors.index') }}}" class="list-group-item">
					{!! Icon::education() !!} Manage {{ Lang::choice('core::terms.instructor', 2) }} 
				</a>
				<a href="{{{ route('observers.index') }}}" class="list-group-item">
					{!! Icon::bullhorn() !!} Manage {{ Lang::choice('core::terms.observer', 2) }} 
				</a>
				<a href="{{{ route('proctors.index') }}}" class="list-group-item">
					{!! Icon::edit() !!} Manage {{ Lang::choice('core::terms.proctor', 2) }} 
				</a>
				<a href="{{{ route('actors.index') }}}" class="list-group-item">
					{!! Icon::film() !!} Manage {{ Lang::choice('core::terms.actor', 2) }} 
				</a>
				<a href="{{{ route('facilities.index') }}}" class="list-group-item">
					{!! Icon::home() !!} Manage {{ Lang::choice('core::terms.facility', 2) }} 
				</a>
			</div>
		</div>
		<div class="col-md-9 col-md-pull-3">
			<h3>Test Events</h3>
			<!-- Today's Events -->
			@include('core::events.partials.panel_table', [
				'events' => $today,
				'title'  => 'Happening Today',
				'none'   => 'No test events happening today.',
				'class'  => 'panel-success'
			])
	
			<!-- Upcoming Events -->
			@include('core::events.partials.panel_table', [
				'events' => $upcoming,
				'title'  => 'Next 10 Upcoming'
			])

		</div> <!-- .col-sm-9 -->
	</div> <!-- .row -->

@stop