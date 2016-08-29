@extends('core::layouts.default')

@section('content')
<div class="row">
	<div class="col-md-12">
		{{-- Generation errors? --}}
		@if($errors->has('generate'))
			<div class="alert alert-danger">
				<h4>Error Generating Test</h4>
				<ul>
					@foreach($errors->get('generate') as $error)
					<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		{{-- Each exam --}}
		@foreach($exams as $exam_count => $exam)

			@if($exam_count > 0)
				<br>
			@endif

			<p class="lead">
				{{ $exam->name }} 
				<a href="{{ route('testplans.create', $exam->id) }}" class="btn btn-success btn-sm pull-right">{!! Icon::plus_sign() !!} New Testplan</a>
			</p>
			<div class="well">
				<table class="table table-hover">
					<thead>
						<tr>
							<th>Name</th>
							<th class="hidden-xs">Time Limit</th>
							<th class="hidden-xs">Minimum Score</th>
							<th class="hidden-xs"></th>
						</tr>
					</thead>
					<tbody>
						@foreach($exam->testplans as $plan)
							@if($plan->status == 'active')
							<tr class="success">
							@else
							<tr class="danger">
							@endif
								<td>
									<a href="{{ route('testplans.edit', [$plan->id]) }}">
										{{ $plan->name }}
									</a><br>

									@if($plan->status == 'active')
									<span class="label label-success">
									@else
									<span class="label label-danger">
									@endif
										{{ ucfirst($plan->status) }}
									</span>
								</td>

								<td class="monospace hidden-xs">{{ $plan->timelimit }}</td>

								<td class="monospace hidden-xs">{{ $plan->minimum_score }}</td>

								<td>
									<div class="btn-group pull-right">
										@if(strpos($plan->name, 'LEGACY IMPORT') === FALSE)
											<a href="{{ route('testplans.generate', [$plan->id]) }}" class="btn btn-sm btn-info">{!! Icon::tasks() !!} Generate Form</a>
										@endif
									</div>
								</td>
							</tr>
						@endforeach
						
						@if($exam->testplans->isEmpty())
							<tr><td colspan="5" class="text-center">No Testplans</td></tr>
						@endif					
					</tbody>
				</table>
			</div>
		@endforeach
	</div>
</div>
@stop