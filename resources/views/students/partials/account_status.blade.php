@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<div class="row">
		<div class="col-xs-8">
			<h3 id="status-info">Account Status</h3>
		</div>

		{{-- Change Status --}}
		<div class="col-sm-4" style="margin-top: 20px; text-align: right;">
			<a href="{{ route('students.status.edit', $student->id) }}" class="btn btn-sm btn-info">
				{!! Icon::edit() !!} Change Status
			</a>
		</div>
	</div>
	<div class="well">
		{{-- Holds --}}
		<h3 style="margin-top:0px">
			<small>Hold History</small>
		</h3>
		<div class="well">
			@if(empty($holds))
				No Holds
			@else
			<table id="holdTable" class="table table-striped table-hover">
				<tr>
					<th>Status</th>
					<th>Updated</th>
					<th>Created</th>
					<th>&nbsp;</th>
				</tr>
				@foreach($holds as $hold)
					@if($hold->hold_status == 'active')
					<tr class="danger">
					@else
					<tr>
					@endif
						<td>
							@if($hold->hold_status == 'active')
							<span class="label label-danger">
							@else
							<span class="label label-default">
							@endif
								{{ ucfirst($hold->hold_status) }}
							</span>
						</td>
						<td><small>{{ date("m/d/Y g:i a", strtotime($hold->updated_at)) }}</small></td>
						<td><small>{{ date("m/d/Y g:i a", strtotime($hold->created_at)) }}</small></td>
						<td>
							<div class="btn-group pull-right">
								<i class="fa fa-chevron-down btn" style="cursor: pointer;" onclick="openHoldDetails($(this))"></i>
								<i class="fa fa-chevron-up btn" style="cursor: pointer; display: none;" onclick="hideThis($(this), 'hold')"></i>
							</div>
						</td>
					</tr>
					<tr class="holdDetails" style="display: none;">
						<td colspan="4">
							<table class="table">
								<tr>
									<td style="width: 10%; font-weight: bold;">Instructions</td>
									<td>{{ nl2br($hold->instructions) }}</td>
								</tr>
								<tr>
									<td style="width: 10%; font-weight: bold;">Comments</td>
									<td>{{ nl2br($hold->comments) }}</td>
								</tr>
							</table>
						</td>
					</tr>
				@endforeach
			</table>
			@endif
		</div>

		{{-- Locks --}}
		<h3>
			<small>Lock History</small>
		</h3>
		<div class="well">
			@if(empty($locks))
				No Locks
			@else
			<table id="lockTable" class="table table-striped table-hover">
				<tr>
					<th>Status</th>
					<th>Updated</th>
					<th>Created</th>
					<th>&nbsp;</th>
				</tr>
				@foreach($locks as $lock)
					@if($lock->lock_status == 'active')
					<tr class="danger">
					@else
					<tr>
					@endif
						<td>
							@if($lock->lock_status == 'active')
							<span class="label label-danger">
							@else
							<span class="label label-default">
							@endif
								{{ ucfirst($lock->lock_status) }}
							</span>
						</td>
						<td><small>{{ date("m/d/Y g:i a", strtotime($lock->updated_at)) }}</small></td>
						<td><small>{{ date("m/d/Y g:i a", strtotime($lock->created_at)) }}</small></td>
						<td>
							<div class="btn-group pull-right">
								<i class="fa fa-chevron-down btn" style="cursor: pointer;" onclick="openLockDetails($(this))"></i>
								<i class="fa fa-chevron-up btn" style="cursor: pointer; display: none;" onclick="hideThis($(this), 'lock')"></i>
							</div>
						</td>
					</tr>
					<tr class="lockDetails" style="display: none;">
						<td colspan="4">
							<table class="table">
								<tr>
									<td style="width: 10%; font-weight: bold;">Instructions</td>
									<td>{{ nl2br($lock->instructions) }}</td>
								</tr>
								<tr>
									<td style="width: 10%; font-weight: bold;">Comments</td>
									<td>{{ nl2br($lock->comments) }}</td>
								</tr>
							</table>
						</td>
					</tr>
				@endforeach
			</table>
			@endif
		</div>
	</div>
@endif