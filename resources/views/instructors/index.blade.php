@extends('core::layouts.default')

@section('content') 
	
	<div class="row">
		<div class="col-md-9">
			<p class="lead">{{ Lang::choice('core::terms.instructor', 2) }}</p>

			{!! Form::open(['route' => 'instructors.search', 'method' => 'POST']) !!}
				<div class="form-group search-form">
					{!! Form::label('search', 'Search By') !!}
					<div class="input-group">
						<!-- Single Button -->
						<div class="btn-group input-group-btn" data-mimic="dropdown" data-mimic-target="#search-type">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								<span class="mimic-selected">Name</span> <span class="caret"></span>
							</button>
							<ul class="search-for dropdown-menu" role="menu">
								<li class="active"><a href="#">Name</a></li>
								<li><a href="#">Birth Date</a></li>
								<li><a href="#">City</a></li>
								<li><a href="#">Email</a></li>
								<li class="divider"></li>
								<li><a href="#">License</a></li>
								<li><a href="#">TM License</a></li>
							</ul>
						</div>
						<div id="divDiscipline" class="btn-group input-group-btn" data-discipline="discipline" data-discipline-target="#search-discipline">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								<span class="discipline-selected">All</span> <span class="caret"></span>
							</button>
							<ul class="discipline dropdown-menu">
								<li class="active"><a href="#">All</a></li>
								@foreach($disciplines as $discipline)
									<li><a href="#">{{ $discipline->abbrev }}</a></li>
								@endforeach
							</ul>
						</div>
						{!! Form::text('search', null, ['placeholder' => 'Enter Search Term ', 'autoofcus' => 'autofocus']) !!}
						<span class="input-group-btn">
							<button class="btn btn-primary search-submit" type="submit">
								{!! Icon::search() !!}  <span class="hidden-xs">Search</span>
							</button>
							<button class="btn btn-primary search-submit" name="add_search" value="true" type="submit" data-toggle="tooltip" title="Add to Previous Search">
								{!! Icon::plus() !!} 
							</button>
	      					</span>
	      				</div>
	      			{!! Form::hidden('search_type', 'Name', ['id' => 'search-type']) !!}
	      			{!! Form::hidden('search-discipline', 'All', ['id' => 'search-discipline']) !!}
	      		</div>

	  			@if( ! empty($searchTypes))
					@include('core::partials.search_terms', [
						'searchTypes' => $searchTypes,
						'controller'  => 'instructors'
					])
				@endif
	      	{!! Form::close() !!} {{-- End search form --}}

      		{{-- Search Results --}}
      		<div class="well table-responsive">
      			<table class="table table-striped table-hover results-table">
					<thead>
						<tr>
							<th>{!! Sorter::link('instructors.index', 'Name', ['sort' => 'last']) !!}</th>
							<th>{!! Sorter::link('instructors.index', 'Location', ['sort' => 'city']) !!}</th>
							<th>Disciplines</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($instructors as $instructor)
							<tr>
								<td>
									{{-- Archived? --}}
									@if(strpos($instructor->status, 'archive') !== false)
										<a data-toggle="tooltip" title="Archived">{!! Icon::flag() !!}</a>
									@endif

									<a href="{{ route('instructors.edit', $instructor->id) }}">
										{{ $instructor->last }}, {{ $instructor->first }}
									</a><br>
									<small class="monospace">{{ $instructor->username }}</small>
								</td>

								<td>{{ $instructor->city }}, {{ strtoupper($instructor->state) }}</td>

								<td>{!! $instructor->disc ? implode('<br>', array_unique(explode(',', $instructor->disc))) : '' !!}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
      		</div>
			{!! $instructors->appends(Input::except('page'))->render() !!}
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3">
			@include('core::instructors.sidebars.index')
		</div>
	</div>
@stop

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			var $mimic = $('[data-mimic="dropdown"]');
			var $hidden = $($mimic.data('mimic-target'));

			var $mimic2 = $('[data-discipline="discipline"]');
			var $hidden2 = $($mimic2.data('discipline-target'));

			getDiscipline();

			// when a list item is clicked
			$('.search-for li a').click(function(){

				// mark the active item
				$('.search-for li', $mimic).removeClass('active');
				$(this).parent('li').addClass('active');

				// change the button text
				var selected = $(this).text();
				$('.mimic-selected', $mimic).html(selected);

				// update the hidden input
				$hidden.val(selected);

				// Set visibility of discipline dropdown, selected item, hidden search value
				// based on if License or TM License is selected in the search by field
				if($(this).text() == "License" || $(this).text() == "TM License")
				{
					$('#divDiscipline').hide();
					$('#search_discipline').val("All");
					$('.discipline-selected', $mimic2).html("All");
					discSelect = $('.discipline').find('li');
					$.each(discSelect, function(idx, select)
					{
						if($(select).hasClass('active'))
						{
							$(select).removeClass('active');
						}
						if(idx == 0) { $(select).addClass('active') }
					});
					$hidden2.val("All");
					setDiscipline("All");
				}
				else
				{
					$('#divDiscipline').show();
				}
			})
			$('.discipline li a').click(function(){
				$('.discipline li', $mimic2).removeClass('active');
				$(this).parent('li').addClass('active');

				$('.discipline-selected', $mimic2).html($(this).text());

				$hidden2.val($(this).text());
				setDiscipline($(this).text());
			})
		})
		function setDiscipline(discipline)
		{
			if(typeof(Storage) !== "undefined")
			{
				sessionStorage.setItem("discipline", discipline);
			}
			else
			{
				document.cookie("discipline=" + discipline + "; path=/");
			}
		}
		function getDiscipline()
		{
			if(typeof(Storage) !== "undefined")
			{
				var disc = sessionStorage.getItem("discipline");
			}
			else
			{
				disc = getCookie("discipline");
			}
			// Check when page first loads and no cookie or sessionStorage data is available
			// Set to the default All
			if(disc === null || disc == "")
			{
				disc = "All";
			}
			setSelectedDiscipline(disc);
		}
		function getCookie(cname)
		{
			var name = cname + "=";
			var ca = document.cookie.split(";");
			for(var i = 0; i < ca.length; i++)
			{
				var c = ca[i];
				while (c.charAt(0) == " ") c = c.substring(1);
				if(c.indexOf(name) == 0) return c.substring(name.length, c.length);
			}
			return "";
		}
		function setSelectedDiscipline(disc)
		{
			var $mimic2 = $('[data-discipline="discipline"]');
			var $hidden2 = $($mimic2.data('discipline-target'));

			discSelect = $('.discipline').find('li');
			for(var x = 0; x < discSelect.length; x++)
			{
				if($(discSelect[x]).hasClass('active'))
				{
					$(discSelect[x]).removeClass('active');
				}
				var chkStr = $(discSelect[x]).html().substr($(discSelect[x]).html().indexOf(">") + 1);
				chkStr = chkStr.substr(0, chkStr.indexOf("<"));

				if(chkStr == disc)
				{
					$(discSelect[x]).addClass('active');
					x = discSelect.length;
				}
			}
			$('.discipline-selected', $mimic2).html(disc);
			$hidden2.val(disc);
		}
	</script>
@stop