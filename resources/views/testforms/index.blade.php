@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => 'testforms.index', 'method' => 'get']) !!}
<div class="row">
	<div class="col-md-9">
		<p class="lead">Manage Testforms</p>

		{{-- Search By --}}
		<div class="form-group search-form">
			{!! Form::label('search', 'Search By') !!}
			<div class="input-group">
				<div class="btn-group input-group-btn" data-mimic="dropdown" data-mimic-target="#search-type">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						<span class="mimic-selected">
							{{ Input::get('search_type') ?: 'Name' }}
						</span> <span class="caret"></span>
					</button>
					<ul class="dropdown-menu" role="menu">
						<li {{ Input::get('search_type') == 'Name' ? 'class="active"' : '' }}><a href="#">Name</a></li>
					</ul>
				</div>
				{!! Form::text('search', Input::get('search'), ['placeholder' => 'Search for Testforms', 'autofocus' => 'autofocus']) !!}
				<span class="input-group-btn">
					<button class="btn btn-primary search-submit" type="submit">
						{!! Icon::search() !!} <span class="hidden-xs">Search</span>
					</button>
				</span>
			</div>
			{!! Form::hidden('search_type', Input::get('search_type') ?: 'Name', ['id' => 'search-type']) !!}
		</div>

		{{-- Search Results --}}
		<div class="well">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Testplan</th>
						<th class="hidden-xs">Minimum</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@foreach($testforms as $form)
						@if($form->status == 'active')
						<tr class="success">
						@elseif($form->status == 'archived')
						<tr class="danger">
						@else
						<tr class="warning">
						@endif
							<td class="lead text-muted">{{ $form->id }}</td>

							<td>
								@if($form->status == 'draft')
									<a href="{{ route('testforms.edit', $form->id) }}">
										@if(Input::get('search_type') == 'Name')
											{{ preg_replace('/'.Input::get('search').'/i', '<strong>$0</strong>', $form->name) }}
										@else
											{{ $form->name }}
										@endif
									</a>
								@else
									<a href="{{ route('testforms.show', $form->id) }}">
										@if(Input::get('search_type') == 'Name')
											{{ preg_replace('/'.Input::get('search').'/i', '<strong>$0</strong>', $form->name) }}
										@else
											{{ $form->name }}
										@endif
									</a>
								@endif

								<br>

								@if($form->status == 'active')
								<span class="label label-success">
								@elseif($form->status == 'archived')
								<span class="label label-danger">
								@else
								<span class="label label-warning">
								@endif
									{{ ucfirst($form->status) }}
								</span>
							</td>

							<td>
								<a href="{{ route('testplans.edit', $form->testplan->id) }}" data-toggle="tooltip" title="{{{ $form->testplan->name }}}">
									{{ str_limit($form->testplan->name, 20) }}
								</a>
							</td>

							<td class="hidden-xs monospace">{{ $form->minimum }}</td>

							<td>
								<div class="btn-group pull-right">
									@include('core::testforms.partials.activate', ['form' => $form])

									{{-- Scramble --}}
									@if(strpos($form->name, 'LEGACY') === false)
										<a href="{{ route('testforms.scrambled', $form->id) }}" class="btn btn-link" data-toggle="tooltip" title="Scramble">
											{!! Icon::random() !!}
										</a>
									@endif

									{{-- Spanish --}}
									@if($form->getOriginal('spanish'))
										<a class="btn btn-link" data-toggle="tooltip" title="Spanish">{!! Icon::globe() !!}</a>	
									@endif

									{{-- Oral --}}
									@if($form->getOriginal('oral'))
										<a class="btn btn-link" data-toggle="tooltip" title="Oral">{!! Icon::volume_up() !!}</a>	
									@endif
								</div>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<p class="lead">Views</p>
		<div class="list-group">
			<a class="list-group-item {{ $status === null ? 'active' : '' }}" href="{{ route('testforms.index') }}">
				{!! Icon::list_alt() !!} All 
				<span class="badge alert-info">{{ $all }}</span>
			</a>

			<a class="list-group-item {{ $status === 'active' ? 'active' : '' }}" href="{{ route('testforms.index', ['status' => 'active']) }}">
				{!! Icon::play() !!} Active
				<span class="badge alert-success">{{ $active }}</span>
			</a>
			
			<a class="list-group-item {{ $status === 'draft' ? 'active' : '' }}" href="{{ route('testforms.index', ['status' => 'draft']) }}">
				{!! Icon::flag() !!} Draft
				<span class="badge alert-warning">{{ $draft }}</span>
			</a>

			<a class="list-group-item {{ $status === 'archived' ? 'active' : '' }}" href="{{ route('testforms.index', ['status' => 'archived']) }}">
				{!! Icon::warning_sign() !!} Archived
				<span class="badge alert-danger">{{ $archived }}</span>
			</a>
		</div>
	</div>
</div>

{!! $testforms->appends(Input::except('page'))->render() !!}
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