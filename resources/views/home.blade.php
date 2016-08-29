@extends('core::layouts.full')

@section('title', 'Welcome')

@section('content')

	<div class="row">
		<div class="col-md-4">
			@if( ! Auth::check())
				<div class="well">
					@include('core::partials.login')
				</div>
				<hr>
			@endif
		
			<div class="well">
				<p class="lead">Search People</p>
				@include('core::public.search_form')
			</div>
		
		</div>
		<div class="col-md-8">
			<!-- <h2>Test Events</h2> -->
			<div class="well">@include('core::events.calendar_simple')</div>
		</div>
	</div>

@stop
