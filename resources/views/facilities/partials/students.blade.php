<div class="row">
	<div class="col-xs-8">
		<h3>{{ $discipline->name }} - Trained {{ Lang::choice('core::terms.student', 2) }}</h3>
	</div>

	{{-- Search Archived Students --}}
	<div class="col-xs-4">
		<a class="btn btn-sm btn-info pull-right" href="{{ route('facilities.discipline.students.archived', [$facility->id, $discipline->id]) }}">{!! Icon::bullhorn() !!} Get Archived</a>
	</div>
</div>
<div class="well">
@if( ! $students->isEmpty())
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Name</th>
				<th>{{ Lang::choice('core::terms.instructor', 1) }}</th>
				<th>Status</th>
				<th class="hidden-xs">Start</th>
				<th>Completed</th>
				<th class="hidden-xs">Expires</th>
			</tr>
		</thead>
		<tbody>
			@foreach($students as $tr)
				@if($tr->status == 'failed')
				<tr class="danger">
				@elseif($tr->status == 'passed')
				<tr class="success">
				@elseif($tr->status == 'attending')
				<tr class="warning">
				@else
				<tr>
				@endif
					<td>
						<a href="{{ route('students.edit', $tr->student_id) }}" target="_blank">{{ $tr->student->commaName }}</a>
					</td>

					<td>
						@if(Auth::user()->ability(['Staff', 'Admin'], []))
							<a href="{{ route('instructors.edit', $tr->instructor_id) }}" target="_blank">{{ $tr->instructor->fullname }}</a>
						@else
							{{ $tr->instructor->fullname }}
						@endif
					</td>

					<td>
						@if($tr->status == 'passed')
						<span class="label label-success">
						@elseif($tr->status == 'failed')
						<span class="label label-danger">
						@else
						<span class="label label-warning">
						@endif
							{{ Lang::get('core::training.status_'.$tr->status) }}
						</span>
					</td>

					<td class="hidden-xs">
						@if( ! empty($tr->started))
							<small>{{ date('m/d/Y', strtotime($tr->started)) }}</small>				
						@endif
					</td>

					<td>
						@if( ! empty($tr->ended))
							<small>{{ date('m/d/Y', strtotime($tr->ended)) }}</small>				
						@endif
					</td>

					<td class="hidden-xs">
						@if( ! empty($tr->expires))
							<small>{{ date('m/d/Y', strtotime($tr->expires)) }}</small>				
						@endif
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endif
</div>