<div class="row">
	<div class="col-xs-8">
		<h3 id="students">
			{{ Lang::choice('core::terms.student', 2) }}
		</h3>
	</div>
</div>
<div class="well table-responsive">
	@if($instructor->studentTrainings->isEmpty())
		No {{ Lang::choice('core::terms.student', 2) }}
	@else
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Name</th>
					<th>Training</th>
					<th>{{ Lang::choice('core::terms.facility_training', 1) }}</th>
					<th>Start</th>
					<th>Completed</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach($instructor->studentTrainings as $str)
					<tr>
						<td>
							@if($str->student->isArchived)
							<a title="Archived {{{ Lang::choice('core::terms.student', 1) }}}" data-toggle="tooltip">
								{!! Icon::exclamation_sign() !!}
							</a>
							@endif
							<a href="{{ route('students.edit', $str->student_id) }}">
								{{ $str->student->commaName }}
							</a>
							<br>

							@if($str->archived)
							<span class="label label-default">
							@elseif($str->status == 'failed')
							<span class="label label-danger">
							@elseif($str->status == 'passed')
							<span class="label label-success">
							@elseif($str->status == 'attending')
							<span class="label label-warning">
							@else
							<span class="label label-default">
							@endif
								{{ ucfirst($str->status) }}
							</span>
						</td>

						<td>
							{{ $str->training->name }}<br>
							<small>{{ $str->discipline->name }}</small>
						</td>

						<td>
							<a href="{{ route('facilities.edit', $str->facility->id) }}">{{ $str->facility->name }}</a>
						</td>

						<td><small>{{ $str->started }}</small></td>
						<td><small>{{ $str->ended }}</small></td>

						<td>
							<div class="btn btn-group">
								@if($str->archived)
									<a title="Archived Training" data-toggle="tooltip">{!! Icon::exclamation_sign() !!}</a>
								@endif
							</div>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	@endif
</div>