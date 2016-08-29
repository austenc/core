@if ($paginator->lastPage() > 1)
	<ul class="pagination-skill">  
		@for ($i = 1; $i <= $paginator->lastPage(); $i++)
			@if(Session::has('skills.errors.'.$i))
			<li class="{{ Session::has('skills.current') && Session::get('skills.current') == $i ? 'active' : '' }} has-error" data-toggle="popover" data-placement="bottom" title="Task {{{ $i }}} Errors" data-content="{{!! implode('<br>', array_flatten(Session::get('skills.errors.' . $i))) !!}}">
			@else
			<li class="{{ Session::has('skills.current') && Session::get('skills.current') == $i ? 'active' : '' }}">
			@endif
				<button name="directNav" value="{{{ $i }}}">{{ $i }}</button>
			</li>
		@endfor
	</ul>  
@endif