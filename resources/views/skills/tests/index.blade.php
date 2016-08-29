@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'skills.index', 'method' => 'get']) !!}
	<div class="row">
		<div class="col-md-9">
			<p class="lead">Skill Tests</p>
			<div class="form-group">
				{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
				<div class="input-group">
					{!! Form::text('search', Input::get('search'), 
					['placeholder' => 'Search for Skill Tests', 'autofocus' => 'autofocus']) !!}
					<span class="input-group-btn">
						<button class="btn btn-info" type="submit">{!! Icon::search() !!} Search</button>
      				</span>
      			</div>
      		</div>

			<div class="well table-responsive">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>{!! Sorter::link('skills.index', 'Header', ['sort' => 'header']) !!}</th>
							<th>Skillexam</th>
							<th class="hidden-xs">Minimum</th>
							<th>Tasks</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($skills as $skill)
							@if($skill->status == 'draft')
							<tr class="warning">
							@elseif($skill->status == 'active')
							<tr class="success">
							@else
							<tr class="danger">
							@endif
								<td class="lead text-muted">{{ $skill->id }}</td>
								
								<td>
									<a href="{{ route('skills.edit', $skill->id) }}">
										{{ $skill->header }}
									</a><br>

									@if($skill->status == 'draft')
									<span class="label label-warning">
									@elseif($skill->status == 'active')
									<span class="label label-success">
									@else
									<span class="label label-danger">
									@endif
										{{ ucfirst($skill->status) }}
									</span>
								</td>

								<td>{{ $skill->exam->name }}</td>

								<td class="hidden-xs monospace">{{ $skill->minimum }}</td>

								<td class="monospace">{{ $skill->tasks->count() }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			{!! $skills->appends(Input::except('page'))->render() !!}
		</div>

		{{-- Sidebar --}}
		@include('core::skills.tests.sidebars.index')
	</div>
	{!! Form::close() !!}
@stop