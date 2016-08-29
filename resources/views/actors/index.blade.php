@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'actors.index', 'method' => 'get']) !!}
	<div class="row">
		<div class="col-md-9">
			{{-- Search Box --}}
			<p class="lead">{{ Lang::choice('core::terms.actor', 2) }}</p>
			<div class="form-group">
				{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
				<div class="input-group">
					{!! Form::text('search', Input::get('search'), 
					['placeholder' => 'Search for '.Lang::choice('core::terms.actor', $actors->count()), 'autofocus' => 'autofocus']) !!}
					<span class="input-group-btn">
						<button class="btn btn-info" type="submit">
							{!! Icon::search() !!} <span class="sr-only">Search</span>
						</button>
      				</span>
      			</div>
      		</div>

			{{-- Showing results for... --}}
			@if(Input::has('search'))
				<h4 class="text-muted">Showing results for "{{{ Input::get('search') }}}"</h4>
			@endif

			{{-- Search results table --}}
			<div class="well table-responsive">
				<table class="table table-striped table-hover results-table">
					<thead>
						<tr>
							<th>{!! Sorter::link('actors.index', 'Name', ['sort' => 'last']) !!}</th>
							<th>{!! Sorter::link('actors.index', 'Location', ['sort' => 'city']) !!}</th>
							<th class="hidden-xs">Disciplines</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($actors as $actor)
							<tr>
								<td>
									<a href="{{ route('actors.edit', $actor->id) }}">
										@if(strpos($actor->status, 'archive') !== false)
											{!! Icon::flag() !!} 
										@endif
										{{ $actor->last }}, {{ $actor->first }}
									</a><br>
									<small>{{ $actor->username }}</small>
								</td>

								<td>{{ $actor->city }}, {{ strtoupper($actor->state) }}</td>

								<td>{!! isset($actor->disc) ? implode('<br>', array_unique(explode(',', $actor->disc))) : '' !!}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>	
			{!! $actors->appends(Input::except('page'))->render() !!}
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3">
			@include('core::actors.sidebars.index')
		</div>
	</div>
	{!! Form::close() !!}
@stop