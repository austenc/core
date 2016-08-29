@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'steps.index', 'method' => 'get']) !!}
	<div class="row">
		<div class="col-md-9">
			<p class="lead">Skill Steps</p>
			<div class="form-group">
				{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
				<div class="input-group">
					{!! Form::text('search', Input::get('search'), 
					['placeholder' => 'Search for Skill Step by Outcome', 'autofocus' => 'autofocus']) !!}
					<span class="input-group-btn">
						<button class="btn btn-info" type="submit">{!! Icon::search() !!} Search</button>
      				</span>
      			</div>
      		</div>

			<div class="well">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Ordinal</th>
							<th>Outcome</th>
							<th>Task</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
					@foreach ($steps as $i => $step)
						@if($step->task->status == 'active')
						<tr class="success">
						@elseif($step->task->status == 'draft')
						<tr class="warning">
						@else
						<tr class="danger">
						@endif
							<td>
								<span class="lead text-muted">#{{ $step->ordinal }}</span>
							</td>

							<td>
								<a href="{{ route('steps.edit', $step->id) }}">
									{{ str_limit($step->expected_outcome, 40) }}
								</a>
							</td>

							<td>
								<a href="{{ route('tasks.edit', $step->task->id) }}">
									{{ $step->task->title }}
								</a><br>

								@if($step->task->status == 'active')
								<span class="label label-success">
								@elseif($step->task->status == 'draft')
								<span class="label label-warning">
								@else
								<span class="label label-danger">
								@endif
									{{ ucfirst($step->task->status) }}
								</span>
							</td>

							<td>
								<div class="btn-group pull-right">
									{{-- Needs Review? --}}
									@if($step->vinput_review)
										<a class="btn btn-link" title="Needs Review" data-toggle="tooltip">{!! Icon::flag() !!}</a>
									@endif

									{{-- Has Input --}}
									@if( ! $step->inputs->isEmpty())
										<a class="btn btn-link" title="Has Input" data-toggle="tooltip">{!! Icon::tags() !!}</a>
									@endif

									{{-- Key Step --}}
									@if($step->is_key == 1)
										<a class="btn btn-link" title="Key Step" data-toggle="tooltip">{!! Icon::star() !!}</a>
									@endif
								</div>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3">
			@include('core::skills.steps.sidebars.index')
		</div>
	</div>
	@if(Input::get('review'))
		{!! Form::hidden('review', 1) !!}
	@endif

	@if(Input::get('inputs'))
		{!! Form::hidden('inputs', 1) !!}
	@endif
	
	{!! Form::close() !!}
@stop