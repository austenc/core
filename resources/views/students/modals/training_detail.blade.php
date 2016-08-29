<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title">Training Detail</h4>
</div>

<div class="modal-body">
	<table class="table table-striped">
	    <tr>
	        <th>Training</th>
	        <td>{{ $training->training->name }}</td>
	    </tr>
	    <tr>
	        <th>Status</th>
	        <td>
				@if($training->archived)
				<span class="label label-default">
				@elseif($training->status == 'passed')
				<span class="label label-success">
				@elseif($training->status == 'failed')
				<span class="label label-danger">
				@else
				<span class="label label-warning">
				@endif
					{{ Lang::get('core::training.status_'.$training->status) }}
				</span>
	        </td>
	    </tr>
	    @if(isset($training->facility_id))
	    <tr>
	        <th>{{ Lang::choice('core::terms.facility_training', 1) }}</th>
	        <td>{{ $training->facility->name }}</td>
	    </tr>
	    @endif
	    <tr>
	        <th>{{ Lang::choice('core::terms.instructor', 1) }}</th>
	        <td>{{ $training->instructor->full_name }}</td>
	    </tr>
	    <tr>
	        <th>Started</th>
	        <td>{{ $training->started }}</td>
	    </tr>
	    <tr>
	        <th>Ended</th>
	        <td>{{ $training->ended }}</td>
	    </tr>
	    <tr>
	        <th>Classroom Hours</th>
	        <td>{{{ $training->classroom_hours or 0 }}}</td>
	    </tr>
	    <tr>
	        <th>Distance Hours</th>
	        <td>{{{ $training->distance_hours or 0 }}}</td>
	    </tr>
	    <tr>
	        <th>Lab Hours</th>
	        <td>{{{ $training->lab_hours or 0 }}}</td>
	    </tr>
	    <tr>
	        <th>Traineeship Hours</th>
	        <td>{{{ $training->traineeship_hours or 0 }}}</td>
	    </tr>
	    <tr>
	        <th>Clinical Hours</th>
	        <td>{{{ $training->clinical_hours or 0 }}}</td>
	    </tr>
	</table>
</div>

<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>