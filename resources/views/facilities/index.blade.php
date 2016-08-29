@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'facilities.search', 'method' => 'POST']) !!}
	<div class="row">
		<div class="col-md-9">
			<p class="lead">{{ Lang::choice('core::terms.facility', 2) }}</p>

			<div class="form-group search-form">
				{!! Form::label('search', 'Search By') !!}
				<div class="input-group">
					<!-- Single button -->
					<div class="btn-group input-group-btn" data-mimic="dropdown" data-mimic-target="#search-type">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							<span class="mimic-selected">Name</span> <span class="caret"></span>
						</button>
						@include('core::facilities.partials.search_types')
					</div>
					<div id="divDiscipline" class="btn-group input-group-btn" data-discipline="discipline" data-discipline-target="#search-discipline">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							<span class="discipline-selected">All</span> <span class="caret"></span>
						</button>
						<ul class="discipline dropdown-menu">
							<li class="active"><a href="#" selected>All</a></li>
							@foreach($disciplines as $discipline)
								<li><a href="#">{{ $discipline->abbrev }}</a></li>
							@endforeach
						</ul>
					</div>
					{!! Form::text('search', null, ['placeholder' => 'Enter search term(s)', 'autofocus' => 'autofocus']) !!}
					<span class="input-group-btn">
						<button class="btn btn-primary search-submit" type="submit">
							{!! Icon::search() !!} <span class="hidden-xs">Search</span>
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
					'controller'  => 'facilities'
				])
			@endif

  			<div class="well table-responsive">
  				<table class="table table-striped table-hover results-table">
					<thead>
						<tr>
							<th>{!! Sorter::link('facilities.index', 'Name', ['sort' => 'name']) !!}</th>
							<th>License</th>
							<th class="hidden-xs">Actions</th>
							<th class="hidden-xs"></th>
						</tr>
					</thead>
					<tbody>
						@foreach ($facilities as $facility)
							<tr>
								<td>
									<a href="{{ route('facilities.edit', $facility->id) }}">{{ ucwords($facility->name) }}</a>
									<br>
									<small>{{ $facility->city }}, {{ strtoupper($facility->state) }}</small>
								</td>

								<td>
									<?php
                                        // gathered via query builder join so returned in string delimited form
                                        $disciplines = explode(',', $facility->disc);
                                        $tmLicenses  = explode(',', $facility->tm_license);
                                        $active      = explode(',', $facility->disc_active);
                                    ?>
									@foreach($disciplines as $i => $d)
										{{-- Staff Only see Disabled Disciplines --}}
										@if($active[$i] == 0 && Auth::user()->ability(['Admin', 'Staff'], []))
											<span class="text-muted">{{ $d }} <small>(disabled)</small></span><br>
											<small class="monospace text-muted">{{ $tmLicenses[$i] }}</small><br>

										{{-- All Other Users only Active Disciplines --}}
										@elseif($active[$i] == 1)
											{{ $d }}<br>
											<small class="monospace">{{ $tmLicenses[$i] }}</small><br>
										@endif
									@endforeach
								</td>

								{{-- Actions --}}
								<td class="hidden-xs">
									@if( ! empty($facility->actions))
										@foreach(explode('|', $facility->actions) as $a)
											<div><small>{{ ucfirst($a) }}</small></div>
										@endforeach
									@endif
								</td>

								<td class="hidden-xs">
									<div class="btn-group pull-right">
										<a href="{{ route('facilities.edit', $facility->id) }}" class="btn btn-sm btn-primary">Edit</a>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
  			</div>
			{!! $facilities->appends(Input::except('page'))->render() !!}
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3">
			@include('core::facilities.sidebars.index')
		</div>
	</div>
	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/facilities/index.js') !!}
@stop