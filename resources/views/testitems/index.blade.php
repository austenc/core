@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => 'testitems.index', 'method' => 'get']) !!}
<div class="row">
	<div class="col-md-9">
		<p class="lead">Testitems</p>

		{{-- Search By --}}
		<div class="form-group search-form">
			{!! Form::label('search', 'Search By') !!}
			<div class="input-group">
				<div class="btn-group input-group-btn" data-mimic="dropdown" data-mimic-target="#search-type">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						<span class="mimic-selected">
							{{ Input::get('search_type') ?: 'Stem' }}
						</span> <span class="caret"></span>
					</button>
					<ul class="dropdown-menu" role="menu">
						<li {{ Input::get('search_type') == 'Stem' ? 'class="active"' : '' }}><a href="#">Stem</a></li>
						<li {{ Input::get('search_type') == 'Subject' ? 'class="active"' : '' }}><a href="#">Subject</a></li>
					</ul>
				</div>
				{!! Form::text('search', Input::get('search'), ['placeholder' => 'Search for Testitems', 'autofocus' => 'autofocus']) !!}
				<span class="input-group-btn">
					<button class="btn btn-primary search-submit" type="submit">
						{!! Icon::search() !!} <span class="hidden-xs">Search</span>
					</button>
				</span>
			</div>
			{!! Form::hidden('search_type', Input::get('search_type') ?: 'Stem', ['id' => 'search-type']) !!}
		</div>
		
		{{-- Search Results --}}
		<div class="well">	
			<table class="table table-hover">
				<thead>
					<tr>
						<th>Stem</th>
						<th>Answer</th>
						<th>Subjects</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($items as $item)
						<tr class="bg-{{{ $item->statusClass }}}">
							<td>
								@if($item->status == 'active')
									<a href="{{ route('testitems.show', [$item->id]) }}">
										@if(Input::get('search_type') == 'Stem')
											{{ preg_replace('/'.Input::get("search").'/i', '<strong>$0</strong>', $item->excerpt) }}
										@else
											{{ $item->excerpt }}
										@endif
									</a>
								@else						
									<a href="{{ route('testitems.edit', [$item->id]) }}">
										@if(Input::get('search_type') == 'Stem')
											{{ preg_replace('/'.Input::get("search").'/i', '<strong>$0</strong>', $item->excerpt) }}
										@else
											{{ $item->excerpt }}
										@endif
									</a>
								@endif
								
								<br>

								@if($item->status == 'active')
								<span class="label label-success">
								@else
								<span class="label label-warning">
								@endif
									{{ ucfirst($item->status) }}
								</span>
							</td>

							<td>{{ $item->theAnswer->content }}</td>

							<td>
								@if(Input::get('search_type') == 'Subject')
									{!! implode('<br>', preg_replace('/'.Input::get("search").'/i', '<strong>$0</strong>', $item->subjects->lists('name')->all())) !!}
								@else
									{!! implode('<br>', $item->subjects->lists('name')->all()) !!}
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3">
		<a href="{{ route('testitems.create') }}" class="btn btn-block btn-success">{!! Icon::plus_sign() !!} New Testitem</a>

		<hr>

		<div class="list-group">
			<a class="list-group-item">
				{!! Icon::list_alt() !!} All 
				<span class="badge alert-info">{{ $all }}</span>
			</a>

			<a class="list-group-item">
				{!! Icon::star() !!} Active
				<span class="badge alert-success">{{ $active }}</span>
			</a>
			
			<a class="list-group-item">
				{!! Icon::flag() !!} Draft
				<span class="badge alert-danger">{{ $draft }}</span>
			</a>
		</div>
	</div>	
</div>

{!! $items->appends(Input::except('page'))->render() !!}
{!! Form::close() !!}
@stop

@section('scripts')
	<script type="text/javascript">
		$(function(){ // document.ready
			$("#loading").hide();
			var $mimic = $('[data-mimic="dropdown"]');
			var $hidden = $($mimic.data('mimic-target'));

			// when a list item is clicked
			$('.dropdown-menu li a', $mimic).click(function(){
				
				// mark the active item
				$('.dropdown-menu li', $mimic).removeClass('active');
				$(this).parent('li').addClass('active');

				// change the button text
				var selected = $(this).text();
				$('.mimic-selected', $mimic).html(selected);

				// update the hidden input
				$hidden.val(selected);
			});
		});
	</script>
@stop