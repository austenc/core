<div class="btn-group pull-right">
	@if($attempt->exam)
		<a href="{{ route('testing.show', $attempt->id) }}" class="btn btn-sm btn-default">Details</a>
  	@else
		<a href="{{ route('skills.testing.show', $attempt->id) }}" class="btn btn-sm btn-default">Details</a>
  	@endif

  	<button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    	<span class="caret"></span>
    	<span class="sr-only">Toggle Dropdown</span>
  	</button>

  	<ul class="dropdown-menu">

		{{-- Test Event --}}
  		@if(Auth::user()->can('events.edit'))
  			<li>
				<a href="{{ route('events.edit', $attempt->testevent_id) }}">Event</a>
			</li>
  		@endif

    	{{-- Knowledge --}}
		@if($attempt->exam)

			{{-- Reschedule --}}
			@if(Auth::user()->can('students.unschedule') && $attempt->status == 'assigned')
				<li>
					<a href="{{ route('students.unschedule_knowledge', [$student->id, $attempt->testevent_id, $attempt->exam_id]) }}" data-confirm="Remove {{{ Lang::choice('core::terms.student', 1) }}} <strong>{{{ $student->full_name }}}</strong> from Knowledge Test <strong>{{{ $attempt->exam->name }}}</strong>?" class="reschedule-btn">
						Reschedule
					</a>
				</li>
			@endif

			{{-- Attach --}}
			@if(Auth::user()->ability(['Admin', 'Staff'], []))
				<li>
					<a href="{{ route('students.attach_attempt_image', [$attempt->id, 'knowledge']) }}" data-toggle="modal" data-target="#attach-media">
						Attach
					</a>
				</li>

				@if($attempt->image->originalFilename())
					<li>
						<a href="{{ $attempt->image->url() }}" target="_blank">
							See Attachment
						</a>
					</li>
				@endif
			@endif

			{{-- Archive/Hold --}}
			@if(Auth::user()->can('students.attempt.modify'))
				{{-- Archive --}}
				<li>
					<a href="{{ route('students.attempt.toggle', [$student->id, $attempt->id, 'knowledge', 'archive']) }}" data-confirm="{{ $attempt->archived ? 'Restore' : 'Archive' }} test attempt?<br><br>Are you sure?" class="toggle-know-archive-btn">
						@if($attempt->archived)
							Restore
						@else
							Archive
						@endif
					</a>
				</li>

				{{-- Hold --}}
				<li>
					<a href="{{ route('students.attempt.toggle', [$student->id, $attempt->id, 'knowledge', 'hold']) }}" data-confirm="{{ $attempt->hold ? 'Remove hold from' : 'Add hold to' }} test attempt?<br><br>Are you sure?" class="toggle-know-hold-btn">
						@if($attempt->hold)
							Remove Hold
						@else
							Add Hold
						@endif
					</a>
				</li>
			@endif

			{{-- extra buttons on each attempt row --}}
			@if(View::exists('testattempts.buttons.extra'))
				@include('testattempts.buttons.extra', ['attempt' => $attempt])
			@endif

		{{-- Skill --}}
		@else

			{{-- Reschedule --}}
			@if(Auth::user()->can('students.unschedule') && $attempt->status == 'assigned')
				<li>
					<a href="{{ route('students.unschedule_skill', [$student->id, $attempt->testevent_id, $attempt->skillexam_id]) }}" data-confirm="Remove {{{ Lang::choice('core::terms.student', 1) }}} <strong>{{{ $student->fullName }}}</strong> from Skill Exam <strong>{{{ $attempt->skillexam->name }}}</strong>?" class="reschedule-skill-btn">
						Reschedule
					</a>
				</li>
			@endif
			
			{{-- Attach media --}}
			@if(Auth::user()->ability(['Admin', 'Staff'], []))
				<li>
					<a href="{{ route('students.attach_attempt_image', [$attempt->id, 'skill']) }}" data-toggle="modal" data-target="#attach-media">
						Attach
					</a>
				</li>

				@if($attempt->image->originalFilename())
					<li>
						<a href="{{ $attempt->image->url() }}" target="_blank">
							See Attachment
						</a>
					</li>
				@endif
			@endif

			{{-- Archive/Hold --}}
			@if(Auth::user()->can('students.attempt.modify'))
				{{-- Archive --}}
				<li>
					<a href="{{ route('students.attempt.toggle', [$student->id, $attempt->id, 'skill', 'archive']) }}" data-confirm="{{ $attempt->archived ? 'Restore' : 'Archive' }} skill attempt?<br><br>Are you sure?" class="toggle-skill-archive-btn">
						@if($attempt->archived)
							Restore
						@else
							Archive
						@endif
					</a>
				</li>

				{{-- Hold --}}
				<li>
					<a href="{{ route('students.attempt.toggle', [$student->id, $attempt->id, 'skill', 'hold']) }}" data-confirm="{{ $attempt->hold ? 'Remove hold from' : 'Add hold to' }} skill attempt?<br><br>Are you sure?" class="toggle-skill-hold-btn">
						@if($attempt->hold)
							Remove Hold
						@else
							Add Hold
						@endif
					</a>
				</li>
			@endif

			{{-- extra buttons on each attempt row --}}
			@if(View::exists('skillattempts.buttons.extra'))
				@include('skillattempts.buttons.extra', ['attempt' => $attempt])
			@endif

		@endif {{-- End 'skillattempts' --}}
	</ul>
</div>



