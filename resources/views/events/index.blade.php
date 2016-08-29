@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'events.index', 'method' => 'GET']) !!}
	<div class="row">
		<div class="col-md-9">
			<p class="lead">Events</p>
			<div class="form-group search-form">
	            <div class="input-group">
	                <!-- Single button -->
	                <div class="btn-group input-group-btn" data-mimic="dropdown" data-mimic-target="#search-type">
	                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
	                        <span class="mimic-selected">{{ $searchType }}</span> <span class="caret"></span>
	                    </button>
	                    <ul class="dropdown-menu" role="menu">
	                        {{-- Event # --}}
	                        <li {{ $searchType == 'Event #' ? 'class="active"' : '' }}>
	                        	<a href="#">Event #</a>
	                        </li>

	                        <li class="divider"></li>
	         				
	         				{{-- Test Date --}}
	                        <li {{ $searchType == 'Test Date' ? 'class="active"' : '' }}>
	                        	<a href="#">Test Date</a>
	                        </li>

	                        <li class="divider"></li>

							{{-- Test Site License # --}}
	                        @if(Auth::user()->ability(['Admin', 'Staff'], []))
		                        <li {{ $searchType == 'Test Site License' ? 'class="active"' : '' }}>
		                        	<a href="#">{{ Lang::choice('core::terms.facility_testing', 1) }} License</a>
		                        </li>
	                        @endif

							{{-- Test Site Name --}}
	                        <li {{ $searchType == 'Test Site Name' ? 'class="active"' : '' }}>
	                        	<a href="#">{{ Lang::choice('core::terms.facility_testing', 1) }} Name</a>
	                       	</li>

	                       	<li class="divider"></li>

	                       	{{-- Exam Name --}}
	                        <li {{ $searchType == 'Exam Name' ? 'class="active"' : '' }}>
	                        	<a href="#">Exam Name</a>
	                       	</li>
	                       	{{-- Skill Name --}}
	                        <li {{ $searchType == 'Skill Name' ? 'class="active"' : '' }}>
	                        	<a href="#">Skill Name</a>
	                       	</li>
	                    </ul>
	                </div>

	                {!! Form::text('search', Input::get('search'), ['placeholder' => 'Enter search term(s)', 'autofocus' => 'autofocus']) !!}
	                <span class="input-group-btn">
	                    <button class="btn btn-primary search-submit" type="submit">
	                        {!! Icon::search() !!} Search<span class="sr-only">Search</span>
	                    </button>
	                </span>
	            </div>
	            {!! Form::hidden('type', $searchType, ['id' => 'search-type']) !!}
      		</div>

			@if(Input::get('past') !== null)
				<p class="lead">Viewing <strong class="text-muted">Past</strong> test events</p>
			@endif

      		<div class="well table-responsive">
      			<table class="table table-striped table-condensed">
					<thead>
						<tr>
							<th>{!! Sorter::link('events.index', 'Test Date', ['sort' => 'test_date']) !!}</th>
							
							@if( ! Auth::user()->isRole('Facility'))
								<th>{{ Lang::choice('core::terms.facility_testing', 1) }}</th>
							@endif
							
							<th>Discipline Exams</th>
							<th class="hidden-xs">Options</th>
							
							@if(Auth::user()->can('events.edit'))
								<th>&nbsp;</th>
							@endif
						</tr>
					</thead>
					<tbody>
						@foreach ($events as $i => $event)
							<tr class="{{{ $event->statusClass }}}">
								{{-- DateTime --}}
								<td>
									<small>
									{{ $event->test_date }} <br>
									@if($event->ended)
										<span class="label label-danger">Ended</span>
									@else
										{{ $event->start_time }}
									@endif
									</small>
								</td>

								{{-- Testsite --}}
								@if( ! Auth::user()->isRole('Facility'))
								<td>
									<a href="{{ route('facilities.edit', $event->facility->id) }}">
										{{ $event->facility->name }}
									</a>

									@if($event->facility->city && $event->facility->state)
									<br>
									<small>{{ $event->facility->city }}, {{ strtoupper($event->facility->state) }}
									@endif
								</td>
								@endif

								{{-- Discipline/Exams/Skills --}}
								<td>
									<strong>{{ $event->discipline->name }}</strong><br>

									@if( ! $event->exams->isEmpty())
										{!! implode('<br>', $event->exams->lists('pretty_name')->all()) !!}<br>
									@endif
									
									@if( ! $event->skills->isEmpty())
										{!! implode('<br>', $event->skills->lists('pretty_name')->all()) !!}
									@endif
								</td>
								
								{{-- Options --}}
								<td class="hidden-xs">
									<div class="btn-group">
										{{-- Regional/Closed --}}
										@if($event->is_regional)
											<a title="{{{ Lang::get('core::events.regional') }}} Access" data-toggle="tooltip" class="btn btn-link">{!! Icon::globe() !!}</a>
										@else
											<a title="{{{ Lang::get('core::events.closed') }}} Access" data-toggle="tooltip" class="btn btn-link">{!! Icon::flag() !!}</a>
										@endif

										{{-- Paper/Web --}}
										@if($event->is_paper)
											<a title="Paper Tests" data-toggle="tooltip" class="btn btn-link">{!! Icon::file() !!}</a>
										@endif

										{{-- Locked/Unlocked --}}
										@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []) && $event->locked)
											<a title="Locked Event" data-toggle="tooltip" class="btn btn-link">{!! Icon::lock() !!}</a>
										@endif

										{{-- Oral Students? --}}
										@if($event->hasOralStudents)
											<a title="Oral {{ Lang::choice('core::terms.student', $event->hasOral) }}" data-toggle="tooltip" class="btn btn-link">{!! Icon::volume_up() !!}</a>
										@endif

										{{-- Empty Event? (No Scheduled) --}}
										@if( ! $event->testattempts->isEmpty() || ! $event->skillattempts->isEmpty())
											<a title="Scheduled {{ Lang::choice('core::terms.student', 2) }}" data-toggle="tooltip" class="btn btn-link">
												{!! Icon::user() !!}
											</a>
										@endif
									</div>
								</td>

								{{-- Actions --}}
								@if(Auth::user()->can('events.edit'))
								<td>
									<div class="btn-group pull-right">
										<a href="{{ route('events.edit', $event->id) }}" class="btn btn-sm btn-primary">Edit</a>
	
										@if(Auth::user()->can('events.release_tests') && ! $event->all_released() && $event->test_date == date('m/d/Y') && $event->locked == 1 && ( ! $event->testattempts->isEmpty() || ! $event->skillattempts->isEmpty()))
											{{-- Release Tests --}}
											<a title="Release Tests" data-toggle="tooltip" href="{{ route('events.release_tests', $event->id) }}" class="btn btn-sm btn-info" data-confirm="Release all tests for this event?">
												{!! Icon::flag() !!} Release
											</a>
										@endif

										{{-- End Event --}}	
										@if($event->canEnd)
											<a title="End Event" data-toggle="tooltip" href="{{ route('events.end', $event->id) }}" class="btn btn-sm btn-danger end-event" data-confirm="<h4>Are you sure you want to end this event?</h4> No further tests will be allowed to start for this event after it is stopped.">
												{!! Icon::stop() !!} End
											</a>
										@endif
									</div>
								</td>
								@endif
							</tr>
						@endforeach
					</tbody>
				</table>
      		</div> {{-- .well --}}

			{{-- Pagination --}}
			@include('core::pagination.links', ['results' => $events])
		</div>
		<div class="col-md-3">
			@include('core::events.sidebars.index')
		</div>
	</div>
	{!! Form::close() !!}
@stop

@section('scripts')
	<script type="text/javascript">
		$(function(){ // document.ready

			var $mimic = $('[data-mimic="dropdown"]');
			var $hidden = $($mimic.data('mimic-target'));

			// Disable double submit in modals
			$('body').on('click', '#dataConfirmOK', function() {
			    $('#main-ajax-load').show();
			    $('button, input[type="button"], input[type="submit"]').prop('disabled', true);
			});
			$('body').on('click', '#dataConfirmCancel', function() {
			   $('button, input[type="button"], input[type="submit"]').prop('disabled', false);
			});

			// when a list item is clicked
			$('.dropdown-menu li a', $mimic).click(function(){
				
				// mark the active item
				$('.dropdown-menu li', $mimic).removeClass('active');
				$(this).parent('li').addClass('active');

				// change the button text
				var selected = $(this).text();
				$('.mimic-selected', $mimic).html(selected);

				// update the hidden input
				$hidden.val(selected);

			});
		});
    </script>
@stop