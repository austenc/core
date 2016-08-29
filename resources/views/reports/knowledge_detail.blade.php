@extends('core::layouts.default')

@section('content')

	<h2 class="center-block text-center">Knowledge Detail</h2>

	@include('core::reports.partials.info', $info)

	<div class="well">
		<h3>Subjects</h3>
		<table class="table table-striped monospace">
			<thead>
				<th>Subject</th>
				<th>% Passing</th>
			</thead>
			<tbody>
				@foreach($subjects as $subject)
				<tr>
					<td>
						@if(Auth::user()->isRole('Admin'))
							<a href="route('subjects.edit', $subject->id)">{{ $subject->name }}</a>
						@else
							{{ $subject->name }}
						@endif
					</td>
					<td>{{ $totals[$subject->id]['percentPass'] }}%</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<div class="well">
		<h3>Missed Vocab</h3>
		<table class="table table-striped monospace">
			<thead>
				<th>% Missed</th>
				<th>Vocab</th>
			</thead>
			<tbody>
				@foreach($vocab as $percentMissed => $v)
				<tr>
					<td>{{ $percentMissed }}%</td>
					<td>{{ isset($v['names']) ? implode(', ', $v['names']) : '' }}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>

@stop