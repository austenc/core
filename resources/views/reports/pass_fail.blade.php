@extends('core::layouts.default')

@section('content')
	<h2 class="center-block text-center">Pass / Fail Report</h2>

	@include('core::reports.partials.info', $info)

	<div class="well">
		@if($students->isEmpty())
			No records found.
		@else
		<table class="table table-striped table-hover monospace">
			<thead>
				<tr>
					<th>Name</th>
					<th>Training Completed</th>
					<th>Test Date</th>
					<th>Status</th>
					<th>Exam</th>				
					</th>
				</tr>
			</thead>
			<tbody>
				@foreach($students as $s)
					<?php 
                        $prevName   = '';
                        $prevTrDate = '';
                    ?>
					@foreach($s->testHistory as $testDate => $attempts)
						@foreach($attempts as $attempt)
							<tr>
								<td>
									@if($prevName != $s->commaName)
										@if(Auth::user()->ability(['Staff', 'Admin'], []))
											<a href="{{ route('students.edit', $s->id) }}">{{ $s->commaName }}</a>
										@else
											{{ $s->commaName }}
										@endif
									@endif
								</td>

								<td>
									@if($prevTrDate != $attempt->training_date)
										{{ $attempt->training_date }}
									@endif
								</td>

								<td>{{ $attempt->tested_date ? date('m/d/Y', strtotime($attempt->tested_date)) : '' }}</td>

								<td>
									@if($attempt->status == 'passed')
										{{ ucfirst($attempt->status) }}
									@else
										-{{ ucfirst($attempt->status) }}-
									@endif
								</td>

								<td>{{ $attempt->title }}</td>
							</tr>
							
							<?php 
                                $prevName   = $s->commaName;
                                $prevTrDate = $attempt->training_date;
                            ?>
						@endforeach
					@endforeach
				@endforeach
			</tbody>
		</table>
		@endif
	</div>

	<div class="well">
		<table class="table table-striped monospace">
			<thead>
				<tr>
					<th>Totals</th>
					<th>ID</th>

					<th># Written</th>
					<th>Pass %</th>
					<th>Variance</th>

					<th># Skill</th>	
					<th>Pass %</th>
					<th>Variance</th>

					<th># Total</th>	
					<th>Pass %</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>{{ $info['type'] == 'instructor' ? $info['instructor_name'] : $info['program_name'] }} </td>
					<td>{{ $info['type'] == 'instructor' ? $info['instructor_license'] : $info['program_license'] }} </td>

					{{-- Knowledge --}}
					<td>{{ $totals['match']['knowledge']['total'] }}</td>
					<td>
						@if( ! empty($totals['match']['knowledge']['passed_percent']))
							{{ $totals['match']['knowledge']['passed_percent'] }}%
						@endif
					</td>
					<td>
						@if( ! empty($totals['match']['knowledge']['variance']))
							{{ $totals['match']['knowledge']['variance'] }}%
						@endif
					</td>

					{{-- Skill --}}
					<td>{{ $totals['match']['skill']['total'] }}</td>
					<td>
						@if( ! empty($totals['match']['skill']['passed_percent']))
							{{ $totals['match']['skill']['passed_percent'] }}%
						@endif
					</td>
					<td>
						@if( ! empty($totals['match']['skill']['variance']))
							{{ $totals['match']['skill']['variance'] }}%
						@endif
					</td>

					{{-- Totals --}}
					<td>{{ $totals['all']['total']['total'] }}</td>
					<td>
						@if( ! empty($totals['all']['total']['passed_percent']))
							{{ $totals['all']['total']['passed_percent'] }}%
						@endif
					</td>
				</tr>
			</tbody>
		</table>
	</div>
@stop