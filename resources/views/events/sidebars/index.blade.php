	@if(Auth::check() && Auth::user()->can('events.create'))
		<a href="/events/create" class="btn btn-block btn-success">{!! Icon::plus_sign() !!} New Event</a>
		
		<hr>
	@endif

	<p class="lead">Views</p>
	<div class="list-group">
		{{-- Current events --}}
		<a href="{{ route('events.index') }}" class="list-group-item {{ Request::is('events') && Input::get('past') == null ? 'active' : null }}">
			{!! Icon::dashboard() !!} Current Events
		</a>

		{{-- Events calendar --}}
		<a href="{{ route('events.calendar') }}" class="list-group-item {{ Request::is('events/calendar') ? 'active' : null }}">
			{!! Icon::calendar() !!} Calendar View
		</a>

		{{-- Pending events --}}
		@if(Auth::user()->ability(['Admin', 'Staff'], []))	
			<a href="{{ route('events.pending') }}" class="list-group-item {{ Request::is('events/pending') ? 'active' : null }}">
				{!! Icon::retweet() !!} Pending Events
			</a>
		@endif

		{{-- Past events --}}
		<a href="{{ route('events.index', ['past' => true]) }}" class="list-group-item {{ Request::is('events') && Input::get('past') != null ? 'active' : null }}">
			{!! Icon::book() !!} Past Events
		</a>
	
	</div>

	{{-- Calendar filters --}}
	@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
		@if(Request::is('events/calendar'))
			{!! Form::open(['route' => 'events.calendar', 'method' => 'get', 'id' => 'filter-form']) !!}
				<p class="lead">Filters</p>
				<div class="well">
					<div class="form-group">
						<small class="help-block">By City</small>
						{!! Form::select('city', $cities, Input::get('city'), ['id' => 'city', 'class' => 'calendar-filter']) !!}
					</div>
					
					<div class="form-group">
						<small class="help-block">By {{ Lang::choice('core::terms.facility_testing', 1) }}</small>
						{!! Form::select('facility', $facilityNames, Input::get('facility'), ['id' => 'facility', 'class' => 'calendar-filter']) !!}
					</div>
				</div>
			{!! Form::close() !!}

			<p class="lead">Legend</p>
			<div class="well">
				@include('core::events.calendar_legend', ['block' => true])
			</div>
		@endif
	@endif