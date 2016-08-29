{!! Button::success(Icon::refresh().' Update')->submit()->block() !!}
	
<hr>

<ul class="list-group">
	{{-- Discipline --}}
	@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<li class="list-group-item">
		<strong>{!! Icon::briefcase() !!} {{ $event->discipline->name }}</strong>
	</li>
	@endif

	{{-- Test Site --}}
	<li class="list-group-item clearfix">
		<strong>{!! Icon::home() !!} {{ Lang::choice('core::terms.facility_testing', 1) }}</strong>
		<span class="pull-right">{{ $event->facility->name }}</span>
	</li>

	{{-- DateTime --}}
	<li class="list-group-item clearfix">
		<strong>
			{!! Icon::calendar() !!} Test Date
		</strong> 
		<span class="pull-right">
			<small>{{ $event->test_date }} at {{ date('g:i A T', strtotime($event->start_time)) }}</small>
		</span>
	</li>

	{{-- Knowledge Exams --}}
	@if( ! $event->exams->isEmpty())
	<li class="list-group-item clearfix">
		<strong>{!! Icon::book() !!} Knowledge</strong>
		<span class="pull-right">
			@foreach($event->exams as $exam)
				<small>
				 {{ $exam->name }} <span class="badge">{{ $event->knowledgeStudents()->where('exam_id', $exam->id)->count() }}</span><br>
				</small>
			@endforeach
		</span>
	</li>
	@endif

	{{-- Skill Exams --}}
	@if( ! $event->skills->isEmpty())
	<li class="list-group-item clearfix">
		<strong>{!! Icon::wrench() !!} Skills</strong>
		<span class="pull-right">
			@foreach($event->skills as $skill)
				<small>
				 {{ $skill->name }} <span class="badge">{{ $event->skillStudents()->where('skillexam_id', $skill->id)->count() }}</span><br>
				</small>
			@endforeach
		</span>
	</li>
	@endif

	<li class="list-group-item clearfix">
		<strong>{!! Icon::star() !!} Options</strong>
		<span class="pull-right">
			<small>
				{{-- Regional Event? --}}
				@if($event->is_regional)
				{!! Icon::globe() !!} {{ Lang::get('core::events.regional') }} Event
				@else
				{!! Icon::eye_close() !!} {{ Lang::get('core::events.closed') }} Event
				@endif
				<br>
				{{--  Paper Event? --}}
				@if($event->is_paper)
					{!! Icon::file() !!} Paper Event
				@else
					{!! Icon::hdd() !!} Web Event
				@endif
			</small>
		</span>
	</li>
</ul>