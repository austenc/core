<table class="table table-condensed">
	<tbody>
		<tr>
			<td><strong>Discipline: </strong></td>
			<td><span>{{ $info['discipline'] }}</span></td>
		</tr>
		<tr>
			<td><strong>Date Range: </strong></td>
			<td>
				@if($info['to'] === null && $info['from'] === null)
					for all time
				@else
					from {{ $info['from'] or 'the beginning of time' }} - {{ $info['to'] or 'today' }}
				@endif
			</td>
		</tr>
		@if($info['type'] == 'instructor')
			<tr>
				<td>
					<strong>{{ Lang::choice('core::terms.instructor', 1) }}: </strong>
				</td>
				<td>
					@if(array_key_exists('instructor_license', $info)) {{ $info['instructor_license'] }}, @endif 
					@if(array_key_exists('instructor_name', $info)) {{ $info['instructor_name'] }} @endif
				</td>
			</tr>
		@endif
		<tr>
			<td><strong>{{ Lang::choice('core::terms.facility_training', 1) }}:</strong> </td>
			<td>
				@if(array_key_exists('program_license', $info)) {{ $info['program_license'] }}, @endif 
				@if(array_key_exists('program_name', $info)) {{ $info['program_name'] }} @endif
			</td>
		</tr>
	</tbody>	
</table>



