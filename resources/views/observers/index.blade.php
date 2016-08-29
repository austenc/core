@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'observers.index', 'method' => 'get']) !!}
	<div class="row">
		<div class="col-md-9">
			{{-- Search Box --}}
			<p class="lead">{{ Lang::choice('core::terms.observer', 2) }}</p>
			<div class="form-group">
				{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
				<div class="input-group">
					{!! Form::text('search', Input::get('search'), 
					['placeholder' => 'Search for '.Lang::choice('core::terms.observer', $observers->count()), 'autofocus' => 'autofocus']) !!}
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
							<th>{!! Sorter::link('observers.index', 'Name', ['sort' => 'last']) !!}</th>
							<th>{!! Sorter::link('observers.index', 'Location', ['sort' => 'city']) !!}</th>
							<th>Disciplines</th>
						</tr>
					</thead>
					<tbody>
					@foreach ($observers as $observer)
						<tr>
							<td>
								<a href="{{ route('observers.edit', $observer->id) }}">
									@if(strpos($observer->status, 'archive') !== false)
										{!! Icon::flag() !!} 
									@endif
									{{ $observer->last }}, {{ $observer->first }}
								</a><br>
								<small>{{ $observer->username }}</small>
							</td>

							<td>{{ $observer->city }}, {{ strtoupper($observer->state) }}</td>

							<td>{!! isset($observer->disc) ? implode('<br>', array_unique(explode(',', $observer->disc))) : '' !!}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
			{!! $observers->appends(Input::except('page'))->render() !!}
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3">
			@include('core::observers.sidebars.index')
		</div>
	</div>
	{!! Form::close() !!}
@stop