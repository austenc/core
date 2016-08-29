@extends('core::layouts.default')

@section('content')

	<h2 class="center-block text-center">Retake Summary</h2>

	@include('core::reports.partials.info', $info)

	<div class="well">
		@if(empty($items))
			No records found.
		@else
		<table class="table table-striped monospace">
			<thead>
				<tr>
					<th colspan="2" style="border-style:none;"></th>
					@if($info['show_knowledge'] && $info['show_skills'])
						<th colspan="6">Knowledge</th>
						<th colspan="6">Skill</th>
					@elseif($info['show_knowledge'])
						<th colspan="12">Knowledge</th>
					@elseif($info['show_skills'])
						<th colspan="12">Skill</th>
					@endif
				</tr>

				<tr>
					<th style="border-top:none;">
						@if($info['type'] == 'all_facilities')
							{{ Lang::choice('core::terms.facility_training', 1) }}
						@else
							{{ Lang::choice('core::terms.instructor', 1) }}
						@endif
					</th>

					<th style="border-top:none;">#ID#</th>

					@if($info['show_knowledge'])
						<th>Att1</th>
						<th>% Pass</th>
						<th>Att2</th>
						<th>% Pass</th>
						<th>Att3</th>
						<th>% Pass</th>
					@endif

					@if($info['show_skills'])
						<th>Att1</th>
						<th>% Pass</th>
						<th>Att2</th>
						<th>% Pass</th>
						<th>Att3</th>
						<th>% Pass</th>
					@endif
				</tr>
			</thead>
			<tbody>

				{{-- Instructors or Programs --}}
				@foreach($items as $name => $data)
					<tr>
						{{--  Name --}}
						<td>
							@if(Auth::user()->ability(['Staff', 'Admin'], []))
								@if($data['type'] == 'instrutor')
									<a target="_blank" href="{{ route('instructors.edit', $data['id']) }}">{{ $name }}</a>
								@else
									<a target="_blank" href="{{ route('facilities.edit', $data['id']) }}">{{ $name }}</a>
								@endif
							@else
								{{ $name }}
							@endif
						</td>

						{{-- License # --}}
						<td class="monospace">
							{{ $data['license'] }}
						</td>

						{{-- Knowledge --}}
						@if($info['show_knowledge'] && isset($data['counts']['knowledge']))
							{{-- 1st attempt --}}
							<td>
								{{ $data['counts']['knowledge']['total']['first'] ?: '' }}
							</td>
							<td>
								@if($data['counts']['knowledge']['total']['first'])
									{{ round(($data['counts']['knowledge']['passed']['first'] / $data['counts']['knowledge']['total']['first']) * 100, 2) }}%
								@endif
							</td>

							{{-- 2nd attempt --}}
							<td>
								{{ $data['counts']['knowledge']['total']['second'] ?: '' }}
							</td>
							<td>
								@if($data['counts']['knowledge']['total']['second'])
									{{ round(($data['counts']['knowledge']['passed']['second'] / $data['counts']['knowledge']['total']['second']) * 100, 2) }}%
								@endif
							</td>

							{{-- 3rd attempt --}}
							<td>
								{{ $data['counts']['knowledge']['total']['third'] ?: '' }}
							</td>
							<td>
								@if($data['counts']['knowledge']['total']['third'])
									{{ round(($data['counts']['knowledge']['passed']['third'] / $data['counts']['knowledge']['total']['third']) * 100, 2) }}%
								@endif
							</td>
						@endif
						
						{{-- Skills --}}
						@if($info['show_skills'])
							{{-- 1st Attempt --}}
							<td>
								{{ $data['counts']['skill']['total']['first'] ?: '' }}
							</td>
							<td>
								@if($data['counts']['skill']['total']['first'])
									{{ round(($data['counts']['skill']['passed']['first'] / $data['counts']['skill']['total']['first']) * 100, 2) }}%
								@endif
							</td>

							{{-- 2nd Attempt --}}
							<td>
								{{ $data['counts']['skill']['total']['second'] ?: '' }}
							</td>
							<td>
								@if($data['counts']['skill']['total']['second'])
									{{ round(($data['counts']['skill']['passed']['second'] / $data['counts']['skill']['total']['second']) * 100, 2) }}%
								@endif
							</td>

							{{-- 3rd Attempt --}}
							<td>
								{{ $data['counts']['skill']['total']['third'] ?: '' }}
							</td>
							<td>
								@if($data['counts']['skill']['total']['third'])
									{{ round(($data['counts']['skill']['passed']['third'] / $data['counts']['skill']['total']['third']) * 100, 2) }}%
								@endif
							</td>
						@endif

					</tr>
				@endforeach

				{{-- Totals --}}
				<tr class="monospace strong">
					<td>Totals</td>

					<td></td>

					@if($info['show_knowledge'])
						<td>
							{{ $totals['knowledge']['total']['first'] ?: '' }}
						</td>
						<td>
							@if($totals['knowledge']['total']['first'])
								{{ round(($totals['knowledge']['passed']['first'] / $totals['knowledge']['total']['first']) * 100, 2) }}%
							@endif
						</td>

						<td>
							{{ $totals['knowledge']['total']['second'] ?: '' }}
						</td>
						<td>
							@if($totals['knowledge']['total']['second'])
								{{ round(($totals['knowledge']['passed']['second'] / $totals['knowledge']['total']['second']) * 100, 2) }}%
							@endif
						</td>

						<td>
							{{ $totals['knowledge']['total']['third'] ?: '' }}
						</td>
						<td>
							@if($totals['knowledge']['total']['third'])
								{{ round(($totals['knowledge']['passed']['third'] / $totals['knowledge']['total']['third']) * 100, 2) }}%
							@endif
						</td>
					@endif

					@if($info['show_skills'])
						<td>
							{{ $totals['skill']['total']['first'] ?: '' }}
						</td>
						<td>
							@if($totals['skill']['total']['first'])
								{{ round(($totals['skill']['passed']['first'] / $totals['skill']['total']['first']) * 100, 2) }}%
							@endif
						</td>

						<td>
							{{ $totals['skill']['total']['second'] ?: '' }}
						</td>
						<td>
							@if($totals['skill']['total']['second'])
								{{ round(($totals['skill']['passed']['second'] / $totals['skill']['total']['second']) * 100, 2) }}%
							@endif
						</td>

						<td>
							{{ $totals['skill']['total']['third'] ?: '' }}
						</td>
						<td>
							@if($totals['skill']['total']['third'])
								{{ round(($totals['skill']['passed']['third'] / $totals['skill']['total']['third']) * 100, 2) }}%
							@endif
						</td>
					@endif

				</tr>
			</tbody>
		</table>
		@endif
	</div>
@stop