<h3>Trainings</h3>
<div class="well">
	<table class="table table-striped table-hover" id="training-info">
		<thead>
			<tr>
				<th>Name</th>
				@if(Auth::user()->can('instructors.manage_trainings'))
				<th class="xs-col-1"></th>
				@endif
			</tr>
		</thead>
		<tbody>
			@foreach($trainings as $training)
				<tr>
					<td>
						{{ $training->name }}<br>
						@if(in_array($training->id, $instructor->teaching_trainings->lists('id')->all()))
						<span class="label label-success">Active</span>
						@else
						<span class="label label-warning">Inactive</span>
						@endif
					</td>
	
					{{-- Activate/Inactivate Staff/Admin Only --}}
					@if(Auth::user()->can('instructors.manage_trainings'))
					<td>
						<div class="btn-group pull-right">
							@if(in_array($training->id, $instructor->teaching_trainings->lists('id')->all()))
								{{-- Deactivate Training --}}
								<a href="{{ route('instructors.training.deactivate', [$instructor->id, $training->id]) }}" class="btn btn-link" data-toggle="tooltip" title="Deactivate Training" data-confirm="Deactivate Training {{{ $training->name }}}?<br><br>Are you sure?">
									{!! Icon::thumbs_down() !!}
								</a>
							@else
								{{-- Activate Training --}}
								<a href="{{ route('instructors.training.activate', [$instructor->id, $training->id]) }}" class="btn btn-link" data-toggle="tooltip" title="Activate Training" data-confirm="Activate Training {{{ $training->name }}}?<br><br>Are you sure?">
									{!! Icon::thumbs_up() !!}
								</a>
							@endif
						</div>
					</td>
					@endif
				</tr>
			@endforeach
		</tbody>
	</table>
</div>