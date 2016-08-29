@if(is_array($facility->actions) && in_array('Training', $facility->actions))
	<h3 id="trained-students">
		Trained {{ Lang::choice('core::terms.student', 2) }}
		<small>All {{ Lang::choice('core::terms.student', 2) }} trainings; active and archived</small>
	</h3>
	<div class="well">
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Name</th>
					<th>Training</th>
					<th>Status</th>
					<th class="hidden-xs">Start</th>
					<th class="hidden-xs">End</th>
					<th class="hidden-xs">Expires</th>
				</tr>
			</thead>
			<tbody>
				@foreach($facility->allStudents->unique() as $st)
					@foreach($st->trainings as $i => $tr)
						@if($tr->pivot->archived)
						<tr>
						@elseif($tr->pivot->status == 'passed')
						<tr class="success">
						@elseif($tr->pivot->status == 'failed')
						<tr class="danger">
						@elseif($tr->pivot->status == 'attending')
						<tr class="warning">
						@else
						<tr>
						@endif
							<td>
								<a href="{{ route('students.edit', $st->id) }}">
									{{ $st->commaName }}
								</a>
							</td>
							<td>
								{{ $tr->name }}<br>
							</td>
							<td>
								{{ ucfirst($tr->pivot->status) }}	
							</td>
							<td class="hidden-xs">
								{{ empty($tr->pivot->started) ? '' : date('m/d/Y', strtotime($tr->pivot->started)) }}				
							</td>
							<td class="hidden-xs">
								{{ empty($tr->pivot->ended) ? '' : date('m/d/Y', strtotime($tr->pivot->ended)) }}				
							</td>
							<td class="hidden-xs">
								{{ empty($tr->pivot->expires) ? '' : date('m/d/Y', strtotime($tr->pivot->expires)) }}				
							</td>
						</tr>
					@endforeach
				@endforeach
			</tbody>
		</table>
	</div>
@endif