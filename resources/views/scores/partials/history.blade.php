@if($history && $title)
	{{-- Only show title on knowledge --}}
	@if($skill === true)
		<h5 class="text-muted">Skill Task {{ $count or '' }}</h5>
	@else
		<p class="lead">{{ $title }} Revision History</p>
	@endif
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>Time</th>
				<th>Action</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			@foreach($history as $h)
				
				{{-- Setup some variables for use below, yes it isn't pretty, but it works --}}
				<?php
                    $user     = $h->userResponsible();
                    $username = empty($user) ? 'Unknown User' : $user->username;
                ?>

				<tr>
					@if($skill === true)
						<td>{{ $h->updated_at->diffForHumans() }}</td>
						<td>{{ $username }} changed {{ $h->fieldName() }} for task {{ $h->revisionable->task->title }}</td>
						<td class="text-right">
							@if($h->fieldName() == 'response')
								<a href="{{ route('scores.revision', $h->id) }}" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#revision-modal">View Details</a>
							@endif
						</td>
					@else
						@if($h->fieldName() == 'answers')
							<td>{{ $h->updated_at->diffForHumans() }}</td>
							<td>{{ $username }} changed {{ $h->fieldName() }}</td>
							<td class="text-right">
								<a href="{{ route('scores.revision', $h->id) }}" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#revision-modal">View Details</a>
							</td>
						@else
							<td>{{ $h->updated_at->diffForHumans() }}</td>
							<td>
								{{ $username }} changed {{ $h->fieldName() }} from <span class="text-muted">{{ $h->oldValue() }}</span> to <span class="text-success">{{ $h->newValue() }}</span>
							</td>
							<td>&nbsp;</td>
						@endif
					@endif
				</tr>

			@endforeach
		</tbody>
	</table>
@endif