<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	{!! Button::success(Icon::plus_sign().' Save Testitem')->submit() !!}

	<hr>

	<ul class="nav list-group">
		<li class="list-group-item active">
			<a href="#info">{!! Icon::info_sign() !!} Information</a>
		</li>

		<li class="list-group-item">
			<a href="#subjects">{!! Icon::tasks() !!} Exam Subjects</a>
		</li>

		<li class="list-group-item">
			<a href="#other">{!! Icon::comment() !!} Other</a>
		</li>
	</ul>
</div>