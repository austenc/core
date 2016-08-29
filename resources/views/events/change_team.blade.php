@extends('core::layouts.default')

@section('content')
	{!! Form::open(array('route' => array('events.change_team', $event->id))) !!}
		<div class="row">
			<div class="col-md-9">
				<div class="row">
					<div class="col-xs-8">
						<h2>Change Testing Team</h2>
					</div>
					<div class="col-xs-4 back-link">
						<a href="{{ route('events.edit', $event->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to Event</a>
					</div>
				</div>

				{{-- Select Observer --}}
				@if( ! Auth::user()->isRole('Observer'))
					<h3>{{ Lang::choice('core::terms.observer', 1) }} <small>@include('core::partials.required')</small></h3>
					<div class="well">

						{{-- Observer mentoring? --}}
						<div class="alert alert-info">
							<div class="checkbox">
								<label>{!! Form::checkbox('is_mentor', true, $event->is_mentor) !!} {{ Lang::choice('core::terms.observer', 1) }} is <strong>mentoring</strong> this event.</label>
							</div>
						</div>

						<table class="table table-striped" id="obs-table">
							<thead>
								<tr>
									<th></th>
									<th>Name</th>
									<th>Type</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($observers as $observer)
									@if($observer->id == $event->observer_id)
									<tr class="success">
									@elseif( ! empty($observer->potentialConflicts))
									<tr class="danger">
									@else
									<tr>
									@endif
										<td>
											{!! 
												// observer id must be array for codeception
												// values come thru incorrect when not array 
												Form::checkbox('observer_id[]', 
															$observer->id, 
														    ($observer->id == $event->observer_id), 
														    ( ! empty($observer->potentialConflicts) ? array('data-confirm' => Lang::choice('core::terms.observer', 1).' <strong>'.$observer->full_name.'</strong> has potentially conflicting Test Events at '.$event->facility->name.'.<br><br>'.implode("<br>",$observer->potentialConflicts).'<br><br>Are you sure?') : array())) 
											!!}
										</td>

										<td>
											@if( ! empty($observer->potentialConflicts))
												<a data-toggle="tooltip" title="Potential Conflicts">{!! Icon::flag() !!}</a>
											@endif
											{{ $observer->commaName }}
										</td>

										<td><small class="text-muted">{{ Lang::choice('core::terms.observer', 1) }}</small></td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				@endif

				{{-- Select Proctor --}}
				<h3>{{ Lang::choice('core::terms.proctor', 1) }} 
				@if( ! Auth::user()->isRole('Observer'))
					<small>optional -- leave blank to default to {{ Lang::choice('core::terms.observer', 1) }}</small>
				@endif
				</h3>

				<div class="well">
					<table class="table table-striped" id="proc-table">
						<thead>
							<tr>
								<th></th>
								<th>Name</th>
								<th>Type</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($proctors as $proctor)
								@if($proctor->id == $event->proctor_id && $event->proctor_type == 'Proctor')
								<tr class="success">
								@elseif( ! empty($proctor->potentialConflicts))
								<tr class="danger">
								@else
								<tr>
								@endif
									<td>
										{!! Form::checkbox('proctor_id[]', 
														$proctor->user_id, 
													    ($proctor->id == $event->proctor_id && $event->proctor_type == 'Proctor'), 
													    ( ! empty($proctor->potentialConflicts) ? array('data-confirm' => Lang::choice('core::terms.proctor', 1).' <strong>'.$proctor->full_name.'</strong> has potentially conflicting Test Events at '.$event->facility->name.'.<br><br>'.implode("<br>",$proctor->potentialConflicts).'<br><br>Are you sure?') : array())) 
										!!}
										{!! Form::hidden('proctor_type['.$proctor->user_id.']', $proctor->getMorphClass()) !!}
									</td>

									<td>
										@if( ! empty($proctor->potentialConflicts))
											<a data-toggle="tooltip" title="Potential Conflicts">{!! Icon::flag() !!}</a>
										@endif
										{{ $proctor->commaName}}
									</td>

									<td><small class="text-muted">{{ Lang::choice('core::terms.proctor', 1) }}</small></td>
								</tr>
							@endforeach

							{{-- Observers as proctors --}}
							@foreach ($observers as $observer)
								@if($observer->id == $event->proctor_id && $event->proctor_type == 'Observer')
								<tr class="success">
								@elseif( ! empty($observer->potentialConflicts))
								<tr class="danger">
								@else
								<tr>
								@endif
									<td>
										{!! Form::checkbox('proctor_id[]', 
														$observer->user_id, 
													    ($observer->id == $event->proctor_id && $event->proctor_type == 'Observer'), 
													    ( ! empty($observer->potentialConflicts) ? array('data-confirm' => Lang::choice('core::terms.observer', 1).' <strong>'.$observer->full_name.'</strong> has potentially conflicting Test Events at '.$event->facility->name.'.<br><br>'.implode("<br>",$observer->potentialConflicts).'<br><br>Are you sure?') : array())) 
										!!}
										{!! Form::hidden('proctor_type['.$observer->user_id.']', $observer->getMorphClass()) !!}
									</td>

									<td>
										@if( ! empty($observer->potentialConflicts))
											<a data-toggle="tooltip" title="Potential Conflicts">{!! Icon::flag() !!}</a>
										@endif
										{{ $observer->commaName }}
									</td>

									<td><small class="text-muted">{{ Lang::choice('core::terms.observer', 1) }} filling in</small></td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>

				{{-- Select Actor --}}
				<h3>
					{{ Lang::choice('core::terms.actor', 1) }} 
					<small>
						optional -- leave blank to default to {{ Lang::choice('core::terms.observer', 1) }}
					</small>
				</h3>
				<div class="well">
					<table class="table table-striped" id="act-table">
						<thead>
							<tr>
								<th></th>
								<th>Name</th>
								<th>Type</th>
							</tr>
						</thead>
						<tbody>
							{{-- Actors as Actors --}}
							@foreach ($actors as $actor)
								@if($actor->id == $event->actor_id && $event->actor_type == 'Actor')
								<tr class="success">
								@elseif( ! empty($actor->potentialConflicts))
								<tr class="danger">
								@else
								<tr>
								@endif
									<td>
										{!! Form::checkbox('actor_id[]', 
														$actor->user_id, 
													    ($actor->id == $event->actor_id && $event->actor_type == 'Actor'), 
													    ( ! empty($actor->potentialConflicts) ? array('data-confirm' => Lang::choice('core::terms.actor', 1).' <strong>'.$actor->full_name.'</strong> has potentially conflicting Test Events at '.$event->facility->name.'<br><br>'.implode("<br>",$actor->potentialConflicts).'<br><br>Are you sure?') : array())) 
										!!}
										{!! Form::hidden('actor_type['.$actor->user_id.']', $actor->getMorphClass()) !!}
									</td>

									<td>
										@if( ! empty($actor->potentialConflicts))
											<a data-toggle="tooltip" title="Potential Conflicts">{!! Icon::flag() !!}</a>
										@endif
										{{ $actor->commaName }}
									</td>

									<td><small class="text-muted">{{ Lang::choice('core::terms.actor', 1) }}</small></td>
								</tr>
							@endforeach

							{{-- Observers as Actors --}}
							@foreach ($observers as $observer)
								@if($observer->id == $event->actor_id && $event->actor_type == 'Observer')
								<tr class="success">
								@elseif( ! empty($observer->potentialConflicts))
								<tr class="danger">
								@else
								<tr>
								@endif
									<td>
										{!! Form::checkbox('actor_id[]', 
														$observer->user_id, 
													    ($observer->id == $event->actor_id && $event->actor_type == 'Observer'), 
													    ( ! empty($observer->potentialConflicts) ? array('data-confirm' => Lang::choice('core::terms.observer', 1).' <strong>'.$observer->full_name.'</strong> has potentially conflicting Test Events at '.$event->facility->name.'.<br><br>'.implode("<br>",$observer->potentialConflicts).'<br><br>Are you sure?') : array())) 
										!!}
										{!! Form::hidden('actor_type['.$observer->user_id.']', $observer->getMorphClass()) !!}
									</td>

									<td>
										@if( ! empty($observer->potentialConflicts))
											<a data-toggle="tooltip" title="Potential Conflicts">{!! Icon::flag() !!}</a>
										@endif
										{{ $observer->commaName }}
									</td>

									<td><small class="text-muted">{{ Lang::choice('core::terms.observer', 1) }} filling in</small></td>
								</tr>
							@endforeach
						</tbody>
					</table>

				</div>
			</div>
			
			{{-- Change Team --}}
			<div class="col-md-3 sidebar">
				@include('core::events.sidebars.change_team')
			</div>
		</div>

		{{-- If current user is Observer, include observer_id in form --}}
		@if(Auth::user()->isRole('Observer'))
			{!! Form::hidden('observer_id[]', $event->observer_id) !!}
		@endif
	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/events/select_team.js') !!}
@stop