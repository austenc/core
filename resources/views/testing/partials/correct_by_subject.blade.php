<table class="table table-striped table-hover @if(isset($condensed)) table-condensed @endif">
	@foreach($subjects as $s)
	<tr>
		<td class="col-md-6"><strong>{{ $s->name }}</strong></td>
		<td class="col-md-6">
			@if(array_key_exists($s->id, $attempt->correct_by_subject) && $attempt->correct_by_subject[$s->id] > 0)
				<div class="progress">
					<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" 
					style="width: {{ round(($attempt->correct_by_subject[$s->id] / $totals[$s->id]) * 100) }}%;">
						{{ round(($attempt->correct_by_subject[$s->id] / $totals[$s->id]) * 100) }}%
					</div>
				</div>
			@else
				<small class="text-danger">0%</small>
			@endif
		</td>
	</tr>
	@endforeach
</table>
