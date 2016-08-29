@extends('core::layouts.default')

@section('content')

<h2 class="center-block text-center">{{ Lang::choice('core::terms.facility_testing', 1) }} Summary</h2>

@include('core::reports.partials.info', $info)

<div class="well">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>{{ Lang::choice('core::terms.facility_testing', 1) }}</th>
				<th>Test Date</th>

				@if($info['show_knowledge'])
				<th>
					Knowledge <br>
					<table class="table-inner-header">
						<tbody>
							<tr>
								<td><small>Pass</small></td>
								<td><small>Fail</small></td>
								<td><small>No Show</small></td>
							</tr>
						</tbody>
					</table>
				</th>
				@endif

				@if($info['show_skills'])
				<th>
					Skills <br>
					<table class="table-inner-header">
						<tbody>
							<tr>
								<td><small>Pass</small></td>
								<td><small>Fail</small></td>
								<td><small>No Show</small></td>
							</tr>
						</tbody>
					</table>
				</th>
				@endif
			</tr>
		</thead>
		<tbody>
			@foreach($data['facilities'] as $id => $facility)
				<tr>
					<td>
						{{-- Site Name --}}
						@if(array_key_exists($id, $facilities))
							<a href="{{ route('facilities.edit', $id) }}">{{ $facilities[$id] }}</a>
						@endif

						{{-- License --}}
						@if(array_key_exists($id, $licenses))
							<br>
							<small class="monospace">#{{ $licenses[$id] }}</small>
						@endif
					</td>

					{{-- Test Dates --}}
					<td>
						<table class="table-inner monospace">
							<tbody>
								@foreach($facility['dates'] as $date => $dateInfo)
									<tr>
										<td>
											{{ $date }}
										</td>
									</tr>
								@endforeach
								<tr class="subtotal-row">
									<td>Sub Totals</td>
								</tr>
							</tbody>
						</table>
					</td>

					{{-- Knowledge --}}
					@if($info['show_knowledge'])
					<td>
						<table class="table-inner monospace table-equal-3">
							<tbody>
								@foreach($facility['dates'] as $date => $dateInfo)
									<tr>
										<td>{{ Formatter::nonZero($dateInfo['knowledge']['passed']) }}</td>
										<td>{{ Formatter::nonZero($dateInfo['knowledge']['failed']) }}</td>
										<td>{{ Formatter::nonZero($dateInfo['knowledge']['noshow']) }}</td>
									</tr>
								@endforeach
								<tr class="subtotal-row">
									<td>{{ Formatter::nonZero($facility['subtotals']['knowledge']['passed']) }}</td>
									<td>{{ Formatter::nonZero($facility['subtotals']['knowledge']['failed']) }}</td>
									<td>{{ Formatter::nonZero($facility['subtotals']['knowledge']['noshow']) }}</td>
								</tr>
							</tbody>
						</table>
					</td>
					@endif

					{{-- Skills --}}
					@if($info['show_skills'])
					<td>
						<table class="table-inner monospace table-equal-3">
							<tbody>
								@foreach($facility['dates'] as $date => $dateInfo)
									<tr>
										<td>{{ Formatter::nonZero($dateInfo['skill']['passed']) }}</td>
										<td>{{ Formatter::nonZero($dateInfo['skill']['failed']) }}</td>
										<td>{{ Formatter::nonZero($dateInfo['skill']['noshow']) }}</td>
									</tr>
								@endforeach
								<tr class="subtotal-row">
									<td>{{ Formatter::nonZero($facility['subtotals']['skill']['passed']) }}</td>
									<td>{{ Formatter::nonZero($facility['subtotals']['skill']['failed']) }}</td>
									<td>{{ Formatter::nonZero($facility['subtotals']['skill']['noshow']) }}</td>
								</tr>
							</tbody>
						</table>
					</td>
					@endif

				</tr>
			@endforeach
			
			<tr class="monospace strong">
				<td>&nbsp;</td>
				<td>Totals</td>

				@if($info['show_knowledge'])
				<td>
					<table class="table-inner table-equal-3">
						<tbody>
							<tr>
								<td>{{ $data['totals']['knowledge']['passed'] }}</td>
								<td>{{ $data['totals']['knowledge']['failed'] }}</td>
								<td>{{ $data['totals']['knowledge']['noshow'] }}</td>
							</tr>
						</tbody>
					</table>
				</td>
				@endif

				@if($info['show_skills'])
				<td>
					<table class="table-inner table-equal-3">
						<tbody>
							<tr>
								<td>{{ $data['totals']['skill']['passed'] }}</td>
								<td>{{ $data['totals']['skill']['failed'] }}</td>
								<td>{{ $data['totals']['skill']['noshow'] }}</td>
							</tr>
						</tbody>
					</table>
				</td>
				@endif
			</tr>
			
		</tbody>
	</table>
</div>
@stop