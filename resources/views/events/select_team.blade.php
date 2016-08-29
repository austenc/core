@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'events.store']) !!}
		<div class="row">
			<div class="col-md-9">
				<h2>Select Testing Team</h2>

				<h3>{{ Lang::choice('core::terms.observer', 1) }} <small>@include('core::partials.required')</small></h3>

				{{-- Observer --}}
				<div class="well">
					{{-- Observer mentoring? --}}
					<div class="alert alert-info">
						<div class="checkbox">
							<label>{!! Form::checkbox('is_mentor', true) !!} {{ Lang::choice('core::terms.observer', 1) }} is <strong>mentoring</strong> this event.</label>
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
								@if( ! empty($observer->potentialConflicts))
								<tr class="danger">
								@else
								<tr>
								@endif
									<td>
										{!! Form::checkbox('observer_id[]', 
														$observer->id, 
													    false, 
													    ( ! empty($observer->potentialConflicts) ? array('data-confirm' => Lang::choice('core::terms.observer', 1).' <strong>'.$observer->full_name.'</strong> has potentially conflicting Test Events at '.$test_site->name.'.<br><br>'.implode("<br>",$observer->potentialConflicts).'<br><br>Are you sure?') : array())) 
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

				{{-- Proctor --}}
				<h3>{{ Lang::choice('core::terms.proctor', 1) }} <small>optional -- leave blank to default to {{ Lang::choice('core::terms.observer', 1) }}</small></h3>
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
								@if(Input::old('proctor_id'))
									@if(Input::old('proctor_id.0') == $proctor->user_id)
									<tr class="{{ empty($proctor->potentialConflicts) ? 'success' : 'danger' }}">
									@else
									<tr class="{{ empty($proctor->potentialConflicts) ? 'success' : 'danger' }}" style="display:none;">
									@endif
								@else
									@if( ! empty($proctor->potentialConflicts))
									<tr class="danger">
									@else
									<tr>
									@endif
								@endif

									<td>
										{!! Form::checkbox('proctor_id[]', 
														$proctor->user_id, 
													    false, 
													    ( ! empty($proctor->potentialConflicts) ? array('data-confirm' => Lang::choice('core::terms.proctor', 1).' <strong>'.$proctor->full_name.'</strong> has potentially conflicting Test Events at '.$test_site->name.'.<br><br>'.implode("<br>",$proctor->potentialConflicts).'<br><br>Are you sure?') : array())) 
										!!}
										{!! Form::hidden('proctor_type['.$proctor->user_id.']', $proctor->getMorphClass()) !!}
									</td>

									<td>
										@if( ! empty($proctor->potentialConflicts))
											<a data-toggle="tooltip" title="Potential Conflicts">{!! Icon::flag() !!}</a>
										@endif
										{{ $proctor->commaName }}
									</td>

									<td><small class="text-muted">{{ Lang::choice('core::terms.proctor', 1) }}</small></td>
								</tr>
							@endforeach

							{{-- Observers as Proctors --}}
							@foreach ($observers as $observer)
								@if(Input::old('proctor_id'))
									@if(Input::old('proctor_id.0') == $observer->user_id)
									<tr class="{{ empty($observer->potentialConflicts) ? 'success' : 'danger' }}">
									@else
									<tr class="{{ empty($observer->potentialConflicts) ? 'success' : 'danger' }}" style="display:none;">
									@endif
								@else
									@if( ! empty($observer->potentialConflicts))
									<tr class="danger">
									@else
									<tr>
									@endif
								@endif

									<td>
										{!! Form::checkbox('proctor_id[]', 
														$observer->user_id, 
													    false,
													    ( ! empty($observer->potentialConflicts) ? array('data-confirm' => Lang::choice('core::terms.observer', 1).' <strong>'.$observer->full_name.'</strong> has potentially conflicting Test Events at '.$test_site->name.'<br><br>'.implode("<br>",$observer->potentialConflicts).'<br><br>Are you sure?') : array())) 
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

				{{-- Actor --}}
				<h3>{{ Lang::choice('core::terms.actor', 1) }} <small>optional -- leave blank to default to {{ Lang::choice('core::terms.observer', 1) }}</small></h3>
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
							@foreach ($actors as $actor)
								@if(Input::old('actor_id'))
									@if(Input::old('actor_id.0') == $actor->user_id)
									<tr class="{{ empty($actor->potentialConflicts) ? 'success' : 'danger' }}">
									@else
									<tr class="{{ empty($actor->potentialConflicts) ? 'success' : 'danger' }}" style="display:none;">
									@endif
								@else
									@if( ! empty($actor->potentialConflicts))
									<tr class="danger">
									@else
									<tr>
									@endif
								@endif

									<td>
										{!! Form::checkbox('actor_id[]', 
														$actor->user_id, 
													    false, 
													    ( ! empty($actor->potentialConflicts) ? array('data-confirm' => Lang::choice('core::terms.actor', 1).' <strong>'.$actor->full_name.'</strong> has potentially conflicting Test Events at '.$test_site->name.'.<br><br>'.implode("<br>",$actor->potentialConflicts).'<br><br>Are you sure?') : array())) 
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
								@if(Input::old('actor_id'))
									@if(Input::old('actor_id.0') == $observer->user_id)
									<tr class="{{ empty($observer->potentialConflicts) ? 'success' : 'danger' }}">
									@else
									<tr class="{{ empty($observer->potentialConflicts) ? 'success' : 'danger' }}" style="display:none;">
									@endif
								@else
									@if( ! empty($observer->potentialConflicts))
									<tr class="danger">
									@else
									<tr>
									@endif
								@endif

									<td>
										{!! Form::checkbox('actor_id[]', 
														$observer->user_id, 
													    false, 
													    ( ! empty($observer->potentialConflicts) ? array('data-confirm' => Lang::choice('core::terms.observer', 1).' <strong>'.$observer->full_name.'</strong> has potentially conflicting Test Events at '.$test_site->name.'.<br><br>'.implode("<br>",$observer->potentialConflicts).'<br><br>Are you sure?') : array())) 
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
			
			{{-- Sidebar --}}
			<div class="col-md-3">
				@include('core::events.sidebars.select_team')
			</div>
		</div>

		{{-- Discipline --}}
		{!! Form::hidden('discipline_id', $event['discipline_id']) !!}
		{{-- TestSite  --}}
		{!! Form::hidden('facility_id', $event['facility_id']) !!}

		{{-- Options --}}
		@if(array_key_exists('is_regional', $event))
			{!! Form::hidden('is_regional', $event['is_regional']) !!}
		@else
			{!! Form::hidden('is_regional', false) !!}
		@endif
		@if(array_key_exists('is_paper', $event))
			{!! Form::hidden('is_paper', $event['is_paper']) !!}
		@else
			{!! Form::hidden('is_paper', false) !!}
		@endif

		{{-- Event DateTime --}}
		@foreach($event['test_date'] as $i => $test_date)
			{!! Form::hidden('test_date['.$i.']', $test_date) !!}
		@endforeach
		@foreach($event['start_time'] as $i => $start_time)
			{!! Form::hidden('start_time['.$i.']', $start_time) !!}
		@endforeach

		{{-- Exam/Skill Names --}}
		@if(Input::old('skill_names'))
			@foreach(Input::old('skill_names') as $i => $name)
				{!! Form::hidden('skill_names['.$i.']', $name) !!}
			@endforeach
		@endif
		@if(Input::old('exam_names'))
			@foreach(Input::old('exam_names') as $i => $name)
				{!! Form::hidden('exam_names['.$i.']', $name) !!}
			@endforeach
		@endif

		{{-- Seats --}}
		@if(Input::old('skill_seats'))
			@foreach(Input::old('skill_seats') as $i => $seats)
				{!! Form::hidden('skill_seats['.$i.']', $seats) !!}
			@endforeach
		@endif
		@if(Input::old('exam_seats'))
			@foreach(Input::old('exam_seats') as $i => $seats)
				{!! Form::hidden('exam_seats['.$i.']', $seats) !!}
			@endforeach
		@endif

		{!! Form::hidden('comments', $event['comments']) !!}
	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/events/select_team.js') !!}
@stop