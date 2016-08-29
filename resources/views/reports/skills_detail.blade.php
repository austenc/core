@extends('core::layouts.default')

@section('content')

	<h2 class="center-block text-center">Skills Detail</h2>
	<div class="text-center center-block">
		<a href="javascript:void(0);" class="btn btn-primary hidden-print" data-clipboard-target=".clipboard-target">
			{!! Icon::copy() !!} Copy to Clipboard
		</a>
	</div>

	<div class="clipboard-target">

		@include('core::reports.partials.info', $info)

		{{-- All Tasks Counts --}}
		<span class="lead text-muted">
			Skills Summary
		</span>
		<div class="well">
			<table class="table table-striped table-hover monospace">
				<thead>
					<tr>
						<th>Skill Summary</th>
						<th># Tested</th>
						<th>% Passing</th>
						<th>Variance</th>
					</tr>
				</thead>
				<tbody>
					@foreach($tasks as $task)
						<tr>
							{{-- Task Title --}}
							<td>
								{{ $task->parent_id }} <a href="#task-steps-{{ $task->id }}">{{ $task->title }}</a>
							</td>

							{{-- # Tested --}}
							<td>
								{{ $requestTotals[$task->id]['total'] }} 
								
								@if(Auth::user()->isRole('Admin'))
									<small class="text-muted">({{ $stateTotals[$task->id]['total'] }})</small>
								@endif
							</td>

							{{-- % Passing --}}
							<td>
								@if($requestTotals[$task->id]['total'])
									{{ $requestTotals[$task->id]['passedPercent'] ?: 0 }}% 
									
									@if(Auth::user()->isRole('Admin'))
										<small class="text-muted">({{ $stateTotals[$task->id]['passedPercent'] }}%)</small>
									@endif
								@endif
							</td>

							{{-- Variance --}}
							<td>
								@if($requestTotals[$task->id]['total'])
									{{ round($requestTotals[$task->id]['passedPercent'] - $stateTotals[$task->id]['passedPercent'], 1) }}%
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		{{-- Each Individual Task w/ Step Counts --}}
		@foreach($tasks as $task)
			<span class="lead text-muted">{{ $task->title }}</span>
			<div id="task-steps-{{ $task->id }}" class="well">
				<table class="table table-striped monospace">
					<thead>
						<tr>
							<th>#</th>
							<th>Step</th>
							<th>% Passing</th>
						</tr>
					</thead>
					<tbody>
						@foreach($task->steps as $step)
						<tr>
							<td>#{{ $step->ordinal }}</td>
							<td>{{ $step->expected_outcome }}</td>
							<td>
								@if(array_key_exists($step->id, $stepTotals))
									{{ $stepTotals[$step->id]['passedPercent'] }}% 

									@if(Auth::user()->isRole('Admin'))
										<small class="text-muted">({{ $stepTotals[$step->id]['total'] }})</small>
									@endif
								@endif
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@endforeach
	</div>

@stop

{{-- Scripts --}}
@section('scripts')
	{!! HTML::script('vendor/clipboard/dist/clipboard.min.js') !!}
	<script type="text/javascript">
		$(document).ready(function() {
			new Clipboard('.btn');
		});
	</script>
@stop