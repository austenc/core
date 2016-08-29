@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'proctors.index', 'method' => 'get']) !!}
	<div class="row">
		<div class="col-md-9">
			{{-- Search Box --}}
			<p class="lead">{{ Lang::choice('core::terms.proctor', 2) }}</p>
			<div class="form-group">
				{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
				<div class="input-group">
					{!! Form::text('search', Input::get('search'), 
					['placeholder' => 'Search for '.Lang::choice('core::terms.proctor', $proctors->count()), 'autofocus' => 'autofocus']) !!}
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
							<th>{!! Sorter::link('proctors.index', 'Name', ['sort' => 'last']) !!}</th>
							<th>{!! Sorter::link('proctors.index', 'Location', ['sort' => 'city']) !!}</th>
							<th>Disciplines</th>
						</tr>
					</thead>
					<tbody>
					@foreach ($proctors as $proctor)
						<tr>
							<td>
								<a href="{{ route('proctors.edit', $proctor->id) }}">
									@if(strpos($proctor->status, 'archive') !== false)
										{!! Icon::flag() !!} 
									@endif
									{{ $proctor->last }}, {{ $proctor->first }}
								</a><br>
								<small>{{ $proctor->username }}</small>
							</td>

							<td>{{ $proctor->city }}, {{ strtoupper($proctor->state) }}</td>

							<td>{{ isset($proctor->disc) ? implode('<br>', array_unique(explode(',', $proctor->disc))) : '' }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
			{!! $proctors->appends(Input::except('page'))->render() !!}
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3">
			@include('core::proctors.sidebars.index')
		</div>
	</div>
	{!! Form::close() !!}
@stop