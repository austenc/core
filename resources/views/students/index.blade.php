@extends('core::layouts.default')

@section('content')
	<div class="row">
		<div class="col-md-9">
			<p class="lead">{{ Lang::choice('core::terms.student', 2) }}</p>

			{!! Form::open(['route' => 'students.search', 'method' => 'POST']) !!}
				<div class="form-group search-form">
					{!! Form::label('search', 'Search By') !!}
					<div class="input-group">
						<!-- Single button -->
						<div class="btn-group input-group-btn" data-mimic="dropdown" data-mimic-target="#search-type">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								<span class="mimic-selected">
									@if(Auth::user()->ability(['Admin', 'Staff'], [])) SSN @else Name @endif
								</span> <span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu">

								<li {{ Auth::user()->ability(['Admin', 'Staff'], []) ? '' : 'class="active"' }}><a href="#">Name</a></li>
								<li {{ Auth::user()->ability(['Admin', 'Staff'], []) ? 'class="active"' : '' }}><a href="#">SSN</a></li>

								@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
								<li><a href="#">TestID</a></li>
								@endif

								<li><a href="#">Email</a></li>
								<li><a href="#">City</a></li>

								<li class="divider"></li>
								<li><a href="#">Trained At (name)</a></li>
								<li><a href="#">Trained At (license)</a></li>
								<li class="divider"></li>
								<li><a href="#">Trained By (name)</a></li>
								<li><a href="#">Trained By (license)</a></li>
								<li class="divider"></li>
								<li><a href="#">Training Type</a></li>
								<li><a href="#">Training Status</a></li>
								<li><a href="#">Training Begin</a></li>
								<li><a href="#">Training End</a></li>
								<li><a href="#">Training Expires</a></li>

								@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
								<li class="divider"></li>
								<li><a href="#">ADA Status</a></li>
								<li><a href="#">Created On</a></li>
								<li><a href="#">Updated On</a></li>
								@endif
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
					
					{!! Form::hidden('search_type', (Auth::user()->ability(['Admin', 'Staff'], []) ? 'SSN' : 'Name'), ['id' => 'search-type']) !!}
				</div>

				@if( ! empty($searchTypes))
					@include('core::partials.search_terms', [
						'searchTypes' => $searchTypes,
						'controller'  => 'students'
					])
				@endif
			{!! Form::close() !!} {{-- End search form --}}

			{{-- Search Results --}}
			{!! Form::open(['route' => 'students.mass', 'method' => 'POST', 'class' => 'form-inline']) !!}
				<div class="well">
					@if($students->isEmpty())
						No {{ Lang::choice('core::terms.student', 2) }} found.
					@else
						<div class="table-responsive">
							<table class="table results-table table-striped table-hover">
								<thead>
									<tr>
										@if(Auth::user()->ability(['Admin', 'Staff', 'Instructor'], []))
											<th>
												{!! Form::checkbox('select-all', NULL, FALSE, [
													'data-action' => 'select-all', 
													'data-target' => '.results-table tbody input[type="checkbox"]'
												]) !!}
											</th>
										@endif

										<th>{!! Sorter::link('students.index', 'Name', ['sort' => 'last', 'default' => true]) !!}</th>

										<th class="hidden-xs">
											{!! Sorter::link('students.index', 'Email', ['sort' => 'email']) !!}
										</th>

										<th>Location</th>
										<th>&nbsp;</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($students as $student)
										<tr>
											@if(Auth::user()->ability(['Admin', 'Staff', 'Instructor'], []))
												<td>{!! Form::checkbox('student_ids[]', $student->id) !!}</td>
											@endif

											<td>
												@if(strpos($student->status, 'archive') !== false)
													<a data-toggle="tooltip" title="Archived">{!! Icon::flag() !!}</a>
												@endif
												
												<a href="{{ route('students.edit', $student->id) }}" data-loader>
													{{ $student->last }}, {{ $student->first }}
												</a><br>
												<small>{{ $student->username }}</small>
											</td>

											<td>{{ $student->email }}</td>

											<td>{{ $student->city }}, {{ strtoupper($student->state) }}</td>

											<td>
												<div class="btn-group pull-right">
													@if(Auth::user()->ability([], 'students.edit'))
														<a href="{{ route('students.edit', $student->id) }}" class="btn btn-sm btn-primary" data-loader>
															Edit
														</a>
													@endif
												</div>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
							{!! $students->appends(Input::except('page'))->render() !!}
						</div>
					@endif
				</div>

				@if(Auth::user()->ability(['Admin', 'Staff', 'Instructor'], []))
					<div class="well">
						{{-- Actions 'With Selected' --}}
						{!! Form::label('action_type', 'With Selected: ') !!}

						{{-- Change Owner --}}
						@if(Auth::user()->isRole('Instructor'))
							{!! Form::select('action_type', [
								'print-certificates' => 'Print Certificates',
								'print-roster'       => 'Print Roster'
							]) !!}
						@elseif(Auth::user()->ability(['Admin', 'Staff'], []))
							{!! Form::select('action_type', [
								'change_owner' => 'Change Owner'
							]) !!}
						@endif
						<div class="btn-group">
							<button type="submit" name="mass-submit" value="true" class="btn btn-primary">Go</button>
						</div>
					</div>
				@endif
			{!! Form::close() !!}
		</div>
		
		{{-- Sidebar --}}
		<div class="col-md-3">
			@include('core::students.sidebars.index')
		</div>
	</div>
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