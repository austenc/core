@extends('core::layouts.default')

@section('content')
	<div class="row">
		<div class="col-md-9">
			<p class="lead">Pending Events</p>
			{!! Form::open(['route' => 'events.pending', 'method' => 'get']) !!}
			<div class="form-group">
				{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
				<div class="input-group">
					{!! Form::text('search', Input::get('search'), ['placeholder' => 'Search for Pending Events by date', 'autofocus' => 'autofocus']) !!}
					<span class="input-group-btn">
						<button class="btn btn-info" type="submit">
							{!! Icon::search() !!} <span class="sr-only">Search</span>
						</button>
      				</span>
      			</div>
      		</div>
      		{!! Form::close() !!}

      		<div class="well table-responsive">
      			<table class="table table-striped table-condensed">
					<thead>
						<tr>
							<th>#</th>
							<th>{!! Sorter::link('events.index', 'Test Date', ['sort' => 'test_date']) !!}</th>
							
							@if( ! Auth::user()->isRole('Facility'))
								<th>{{ Lang::choice('core::terms.facility_testing', 1) }}</th>
							@endif
							
							<th>Discipline Exams</th>
							<th class="hidden-xs">Options</th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($events as $event)
							<tr class="{{{ $event->statusClass }}}">
								<td>
									<span class="lead text-muted">
										{{{ $event->id }}}
									</span>
								</td>

								{{-- DateTime --}}
								<td class="hidden-xs">
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
									</a><br>
									<small>{{ $event->facility->city }}, {{ $event->facility->state }}</small>
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
									@if($event->is_regional)
										<a title="{{{ Lang::get('core::events.regional') }}} Event" data-toggle="tooltip">{!! Icon::globe() !!}</a>
									@else
										<a title="{{{ Lang::get('core::events.closed') }}} Event" data-toggle="tooltip">{!! Icon::lock() !!}</a>
									@endif

									@if($event->is_paper)
										<a title="Paper Event" data-toggle="tooltip">{!! Icon::file() !!}</a>
									@endif
								</td>

								<td>
									<div class="btn-group pull-right">
										<a href="{{ route('events.edit_pending', $event->id) }}" class="btn btn-sm btn-primary">Edit</a>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
      		</div>

		</div>
		<div class="col-md-3">
			@include('core::events.sidebars.index')
		</div>
	</div>
@stop