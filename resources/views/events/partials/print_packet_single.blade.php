@if(Auth::user()->can('events.print_packet'))
	
	{{-- Print Scanform --}}
	<li>
		<a href="{{ route('scantron.print_single', [$student->id, $event->id]) }}" target="_blank" class="print-packet-single">
			Print Scanform
		</a>
	</li>

	{{-- Print Skilltest --}}
	@if(isset($includeSkill))
		<li>
			<a href="{{ route('events.print_skill', [$event->id, $student->id]) }}" target="_blank">
				Print Skills
			</a>
		</li>
	@endif
@endif