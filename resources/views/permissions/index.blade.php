@extends('core::layouts.default')

@section('content')
	<div class="row">
		<div class="col-md-9">
			<p class="lead">User Roles</p>

			<div class="well">	
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th>Role</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@foreach($roles as $r)
							<tr>
								<td>{{ $r->name }}</td>
								<td class="text-right">
									<a href="{{ route('permissions.edit_role', $r->id) }}" class="btn btn-primary btn-sm">Permissons</a>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
@stop