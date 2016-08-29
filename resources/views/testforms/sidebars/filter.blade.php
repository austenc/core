<p class="lead">Views</p>
<div class="list-group">
	{{-- Current events --}}
	<a href="{{ route('testforms.index') }}" class="list-group-item {{ Request::is('testforms') && Input::get('s') == null ? 'active' : null }}">
		{!! Icon::dashboard() !!} All <span class="badge">{{ $count['all'] }}</span>
	</a>

	<a href="{{ route('testforms.index', ['s' => 'active']) }}" class="list-group-item {{ Request::is('testforms') && Input::get('s') == 'active' ? 'active' : null }}">
		{!! Icon::star() !!} Active <span class="badge">{{ $count['active'] }}</span>
	</a>

	<a href="{{ route('testforms.index', ['s' => 'draft']) }}" class="list-group-item {{ Request::is('testforms') && Input::get('s') == 'draft' ? 'active' : null }}">
		{!! Icon::duplicate() !!} Draft <span class="badge">{{ $count['draft'] }}</span>
	</a>

	<a href="{{ route('testforms.index', ['s' => 'archived']) }}" class="list-group-item {{ Request::is('testforms') && Input::get('s') == 'archived' ? 'active' : null }}">
		{!! Icon::flag() !!} Archived <span class="badge">{{ $count['archived'] }}</span>
	</a>
</div>