@extends('core::layouts.default')

@section('content')
	<div class="row">
		<div class="col-md-9">
			<h2>{{ $type }}</h2>
			<div class="well">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Name</th>
							<th>Username</th>
							<th>Email</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($people as $p)
							<tr>
								<td>
									<a href="{{ route($routeBase . '.edit', $p->id) }}">{{ $p->fullName }}</a>
								</td>
								<td class="monospace">{{ $p->user->username }}</td>
								<td>{{ $p->user->email }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
		@include('core::users.sidebar')
	</div>
@stop	
