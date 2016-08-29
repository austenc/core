@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'trainings.index', 'method' => 'get']) !!}
	<div class="row">
		<div class="col-md-9">
			<p class="lead">Trainings</p>
			<div class="form-group">
				{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
				<div class="input-group">
					{!! Form::text('search', Input::get('search'), 
					['placeholder' => 'Search for Trainings', 'autofocus' => 'autofocus']) !!}
					<span class="input-group-btn">
						<button class="btn btn-info" type="submit">
							{!! Icon::search() !!} <span class="sr-only">Search</span>
						</button>
      				</span>
				</div>
			</div>

			<div class="well">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>{!! Sorter::link('trainings.index', 'Name', ['sort' => 'name']) !!}</th>
							<th>Discipline</th>
							<th>Requirements</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($trainings as $training)
							<tr>
								<td>
									<a href="{{ route('trainings.edit', $training->id) }}">{{ $training->name_with_abbrev }}</a>
								</td>

								<td>{{ $training->discipline->name }}</td>

								<td>
									@if( ! $training->required_trainings->isEmpty())
										{!! implode('<br><small>Training</small><br>', $training->required_trainings->lists('name')->all()) !!}<br><small>Training</small>
									@endif
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			{!! $trainings->appends(Input::except('page'))->render() !!}
		</div>
		
		{{-- Sidebar --}}
		@include('core::trainings.sidebar')
	</div>
	{!! Form::close() !!}
@stop	
