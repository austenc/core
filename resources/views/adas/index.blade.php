@extends('core::layouts.default')

@section('content')
	<div class="row">
		<div class="col-md-9">
			<h2>Manage ADA</h2>
			<div class="well">
			@if($adas->isEmpty())
				<p>No ADA types found.</p>
			@else
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th>Name</th>
							<th>Test Type</th>
							<th>Extend Time By <small>(min)</small></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@foreach($adas as $ada)
							<tr>
								<td>{{{ $ada->name }}}</td>
								<td><em>{{{ $ada->test_type }}}</em></td>
								<td>{{ $ada->extend_time }}</td>
								<td>
									<a href="{{ route('adas.edit', [$ada->id]) }}" class="btn btn-primary btn-sm pull-right">Edit</a>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
			</div>
		</div>

		<div class="col-md-3 sidebar">
			<a href="{{ route('adas.create') }}" class="valign-heading pull-right btn btn-success">
				{!! Icon::plus_sign() !!} Create New
			</a>
		</div>
	</div>
@stop