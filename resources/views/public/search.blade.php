@extends('core::layouts.default')

@section('content')
	<div class="row">@include('core::public.search_form')</div>
	<hr>
	<div class="row">
		<p class="lead">Search Results</p>
		<table class="table table-striped">
			@if($results)
				<thead>
					<tr>
						<th>Name</th>
						@if(Config::get('core.certification.show'))
							<th>Certification(s)</th>
						@endif
					</tr>
				</thead>
				<tbody>
					@foreach($results as $person)
					<tr>
						<td>{{ $person->fullName }}</td>
						@if(Config::get('core.certification.show'))
							<td>
								{{ $person->certifications() ? implode(', ', $person->certifications()->get()->lists('name')->all()) : '' }}
							</td>
						@endif
					</tr>
					@endforeach
				</tbody>
			@else
				<tr>
					<td>No matches found.</td>
				</tr>
			@endif
		</table>
	</div>
@stop