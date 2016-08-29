@extends('core::layouts.default')

@section('content')
	<div class="row">
		<div class="col-md-9">
			<h2>Agency</h2>

			<div class="well">
				@if($agencies->isEmpty())
					No State Agency users
				@else
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Name</th>
							<th>Username</th>
							<th>Email</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($agencies as $agency)
							<tr>
								<td>
									<a href="{{ route('agencies.edit', $agency->id) }}">{{ $agency->full_name }}</a>
								</td>
								<td class="monospace">{{ $agency->user->username }}</td>
								<td>{{ $agency->user->email }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
				@endif
			</div>
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3">
			@include('core::agency.sidebars.index')
		</div>
	</div>
@stop	
