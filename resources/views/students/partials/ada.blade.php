@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
	<h3 id="ada-info">ADA</h3>
	<div class="well">
		@if($student->allAdas->isEmpty())
			No ADAs
		@else
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Type</th>
						<th>Status</th>
						<th>Updated</th>
						<th>Created</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@foreach($student->allAdas as $ada)
						<tr>
							<td>	
								@if($ada->pivot->deleted_at)
									<a data-toggle="tooltip" title="Archived {{ date('m/d/Y', strtotime($ada->pivot->deleted_at)) }}">{!! Icon::exclamation_sign() !!}</a> 
								@endif
								{{ $ada->name }}
							</td>
							
							<td>
								@if($ada->pivot->deleted_at)
									<span class="label label-default">
								@elseif($ada->pivot->status == 'pending')
									<span class="label label-warning">
								@elseif($ada->pivot->status == 'accepted')
									<span class="label label-success">
								@else
									<span class="label label-danger">
								@endif
									{{ ucfirst($ada->pivot->status) }}</span>
								</span>
							</td>

							<td>{{ $ada->pivot->updated_at ? date('m/d/Y', strtotime($ada->pivot->updated_at)) : '' }}</td>
							<td>{{ $ada->pivot->created_at ? date('m/d/Y', strtotime($ada->pivot->created_at)) : '' }}</td>

							<td>
								@if(is_null($ada->pivot->deleted_at))
									<a href="{{ route('students.edit_ada', [$student->id, $ada->id]) }}" class="btn btn-link pull-right">
										{!! Icon::edit() !!}
									</a>
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@endif
	</div>
@endif