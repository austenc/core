<div class="sidebar">
	{!! Button::success(Icon::plus_sign().' Create Event')->submit()->block() !!}
	<button type="submit" class="btn btn-warning btn-block" name="cancel" value="true">
		{!! Icon::warning_sign().' Cancel, Modify Event' !!}
	</button>
</div>

<hr>

<ul class="list-group">
	{{-- Discipline --}}
	<li class="list-group-item">
		<strong>{!! Icon::briefcase() !!} {{ $discipline->name }}</strong>
	</li>

	{{-- Test Site --}}
	<li class="list-group-item clearfix">
		<strong>
			{!! Icon::home() !!} {{ Lang::choice('core::terms.facility_testing', 1) }} 
		</strong>
		<span class="pull-right">
			@if(Auth::user()->ability(['Admin', 'Staff'], []))
				<a href="{{ route('facilities.edit', $test_site->id) }}">
					{{{ $test_site->name }}}
				</a>
			@else
				{!! Icon::home() !!} {{ Lang::choice('core::terms.facility_testing', 1) }}<br>
				{{{ $test_site->name }}}
			@endif
		</span>
	</li>

	{{-- DateTime --}}
	<li class="list-group-item clearfix">
		<strong>{!! Icon::calendar() !!} Test Date(s)</strong>
		<span class="pull-right">
			@foreach ($event['test_date'] as $i => $test_date)
				@if($test_date)
					{{ $test_date }} <br>
				@endif
			@endforeach
		</span>
	</li>


	{{-- Knowledge Exams --}}
	@if(isset($event['exam_names']))
	<li class="list-group-item clearfix">
		<strong>{!! Icon::book() !!} Knowledge</strong>
		<span class="pull-right">
			@foreach($event['exam_names'] as $id => $name)
				<small>
					{{ $name }} <span class="badge">{{ $event['exam_seats'][$id] }}</span> <br>
				</small>
			@endforeach
		</span>
	</li>
	@endif

	{{-- Skill Exams --}}
	@if(isset($event['skill_names']))
	<li class="list-group-item clearfix">
		<strong>{!! Icon::wrench() !!} Skills</strong>
		<span class="pull-right">
			@foreach($event['skill_names'] as $id => $name)
				<small>
					{{ $name }} <span class="badge">{{ $event['skill_seats'][$id] }}</span><br>
				</small>
			@endforeach
		</span>
	</li>
	@endif

	{{-- Options --}}
	<li class="list-group-item clearfix">
		<strong>{!! Icon::star() !!} Options</strong>
		<span class="pull-right">
			<small>
				{{-- Regional/Closed Event --}}
				@if(array_key_exists('is_regional', $event))
					{{ Lang::get('core::events.regional') }} Event <br>
				@else
					{{ Lang::get('core::events.closed') }} Event <br>
				@endif
				{{-- Paper/Web Event --}}
				@if(array_key_exists('is_paper', $event))
					Paper Event
				@else
					Web Event
				@endif
			</small>
		</span>
	</li>
</ul>