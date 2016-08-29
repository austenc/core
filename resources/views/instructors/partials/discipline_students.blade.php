<div class="row">
	<div class="col-xs-8">
		<h3>{{ $discipline->name }} - Trained {{ Lang::choice('core::terms.student', 2) }}</h3>
	</div>

	<div class="col-sm-4">
		<a class="btn btn-sm btn-info pull-right" target="_blank" href="{{ route('instructors.discipline.students.archived', [$instructor->id, $discipline->id]) }}">{!! Icon::bullhorn() !!} Get Archived</a>
	</div>
</div>
<div class="well">
	@if($students->isEmpty())
		No {{ Lang::choice('core::terms.student', 2) }}
	@else
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Name</th>
					<th>{{ Lang::choice('core::terms.facility_training', 1) }}</th>
					<th>Status</th>
					<th>Start</th>
					<th>Completed</th>
					<th class="hidden-xs">Expires</th>
				</tr>
			</thead>
			<tbody>
				@foreach($students as $st)
					@if($st->status == 'failed')
					<tr class="danger">
					@elseif($st->status == 'passed')
					<tr class="success">
					@elseif($st->status == 'attending')
					<tr class="warning">
					@else
					<tr>
					@endif
						<td>
							@if($st->student->isArchived)
							<a title="Archived {{{ Lang::choice('core::terms.student', 1) }}}" data-toggle="tooltip">
								{!! Icon::exclamation_sign() !!}
							</a>
							@endif
							<a href="{{ route('students.edit', $st->student_id) }}">
								{{ $st->student->commaName }}
							</a>
						</td>

						<td>
							<a href="{{ route('facilities.edit', $st->facility->id) }}">{{ $st->facility->name }}</a>
						</td>

						<td>
							@if($st->archived)
							<span class="label label-default">
							@elseif($st->status == 'failed')
							<span class="label label-danger">
							@elseif($st->status == 'passed')
							<span class="label label-success">
							@elseif($st->status == 'attending')
							<span class="label label-warning">
							@else
							<span class="label label-default">
							@endif
								{{ ucfirst($st->status) }}
							</span>
						</td>

						<td><small>{{ $st->started }}</small></td>
						<td><small>{{ $st->ended }}</small></td>
						<td class="hidden-xs"><small>{{ $st->expires }}</small></td>
					</tr>
				@endforeach
			</tbody>
		</table>
	@endif
</div>