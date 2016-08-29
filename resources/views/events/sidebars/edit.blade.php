<div class="sidebar-contain" data-clampedwidth=".sidebar" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}">
	{{-- Update --}}
	@if(Auth::user()->can('events.change_datetime') && $event->locked == 0)
		{!! Button::success(Icon::refresh().' Update')->submit() !!}
	@endif

	{{-- Release Tests --}}
	@if(Auth::user()->can('events.release_tests') && ! $all_released && $event->test_date == date('m/d/Y') && $event->locked == 1 && ($event->knowledgeStudents()->count() > 0 || $event->skillStudents()->count() > 0))
		<a href="{{ route('events.release_tests', [$event->id]) }}" class="btn btn-info" data-confirm="Release all Tests?">{!! Icon::flag() !!} Release Tests</a>
	@endif
	
	{{-- Lock Event --}}
	@if($event->lockable)
		<a href="{{ route('events.lock', [$event->id]) }}" class="btn btn-danger" data-confirm="Lock this Event?" id="lock-evt-btn">{!! Icon::lock() !!} Lock</a>
	@endif

	{{-- Delete Event --}}
	@if(Auth::user()->can('events.delete'))
		<a href="{{ route('events.delete', $event->id) }}" data-confirm="Delete this Event?<br><br>Are you sure?" class="btn btn-danger delete-event">
			<span class="glyphicon glyphicon-exclamation-sign"></span> Delete
		</a>
	@endif

	{{-- Unlock Event --}}
	@if($event->locked && Auth::user()->can('events.lock') && ! Auth::user()->isRole('Observer'))
		<a href="{{ route('events.unlock', [$event->id]) }}" class="btn btn-danger" data-confirm="Unlock this Event?">{!! Icon::lock() !!} Unlock</a>
	@endif

	{{-- Change Test Team --}}
	@if(Auth::user()->can('events.change_team') && $event->locked == 0)
		<a href="{{ route('events.change_team', [$event->id]) }}" class="btn btn-warning">{!! Icon::flash() !!} Change Testing Team</a>
	@endif
		
	{{-- End Event --}}
	@if($event->canEnd)
		<a href="{{ route('events.end', $event->id) }}" class="btn btn-danger" data-confirm="<h4>Are you sure you want to end this event?</h4> No further tests will be allowed to start for this event after it is stopped. Test(s) will be sent for scoring!">
			{!! Icon::stop() !!} End Event
		</a>
	@endif

	@if(Auth::user()->can('events.print_packet'))
		
		{{-- Print knowledge scanforms --}}
		@if( ! $event->knowledgeStudents->isEmpty())
			<a href="{{ route('scantron.print_multiple', $event->id) }}" target="_blank" class="btn btn-default">
				<span class="glyphicon glyphicon-print"></span> Print Scanforms
			</a>
		@endif
	
		{{-- print paper verfication report --}}
		@if($event->is_paper)
			<a href="{{ route('events.print_verification', $event->id) }}" target="_blank" class="btn btn-default">
				{!! Icon::saved() !!} Print Verfication Report
			</a>
		@endif		
	@endif

	@if(Auth::user()->can('events.print_skill'))
		{{-- Print skill tests --}}
		@if( ! $event->skillStudents->isEmpty())
			<a href="{{ route('events.print_skill', $event->id) }}" target="_blank" class="btn btn-default">
				{!! Icon::list_alt() !!} Print Skills
			</a>
		@endif
	@endif

	@if(Auth::user()->can('events.print_1250') && $event->locked)
		<a href="{{ route('events.1250', $event->id) }}" target="_blank" class="btn btn-default">
			{!! Icon::th_list() !!} Print 1250
		</a>
	@endif

	{{-- Print Admin Report (for reviewing answers) --}}
	@if(Auth::user()->ability(['Admin', 'Staff'], []) && $event->isEnded)
		<a href="{{ route('events.admin_report', $event->id) }}" target="_blank" class="btn btn-default">{!! Icon::th() !!} Admin Report</a>
	@endif

	{{-- Print All Test Confirmation Letters --}}
	@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []) || Auth::user()->isRole('Facility'))
		@if( ! $event->knowledgeStudents->isEmpty() || ! $event->skillStudents->isEmpty())
			<a href="{{ route('events.print_confirmations', $event->id) }}" target="_blank" class="btn btn-default">
				{!! Icon::check() !!} Print Confirmation Letters
			</a>
		@endif
	@endif
</div>