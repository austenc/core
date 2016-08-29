{!! Form::open(['route' => 'public.search', 'method' => 'get']) !!}

	<div class="input-group">
		{!! Form::text('q', Input::get('q'), ['placeholder' => 'Search by name', 'autofocus' => 'autofocus']) !!}
		<span class="input-group-btn">
			<button class="btn btn-primary" type="submit">{!! Icon::search() !!} <span class="sr-only">Search</span></button>
		</span>
	</div>

{!! Form::close() !!}