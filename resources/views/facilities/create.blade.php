@extends('core::layouts.default')

@section('content')
	<div class="row">
		{!! Form::open(['route' => 'facilities.store']) !!}
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>Create {{ Lang::choice('core::terms.facility', 1) }}</h1>
				</div>
				{!! HTML::backlink('facilities.index') !!}
			</div>

			{{-- Identification --}}
			@include('core::facilities.partials.identification')

			{{-- Disciplines --}}
			@include('core::facilities.partials.disciplines')
			
			{{-- Contact --}}
			@include('core::partials.contact', ['name' => 'facility'])

			{{-- Address --}}
			@include('core::facilities.partials.address')

			{{-- Other --}}
			@include('core::facilities.partials.other')

			{{-- Comments --}}
			@include('core::partials.comments')
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			@include('core::facilities.sidebars.create')
		</div>
		{!! Form::close() !!}
	</div>
@stop

@section('scripts')
	@if( ! App::environment('production'))
		<script type="text/javascript">
			$(document).on('click', '#populate', function(){
				$.ajax({
					url: $(this).data('href'),
					dataType: 'json',
					success: function(result){
						$('#name').val(result.name);
						$("#email").val(result.email);
						$("#phone").val(result.phone);
						$("#address").val(result.address);
						$("#city").val(result.city);
						$("#state").val(result.state);
						$("#zip").val(result.zip);
						$('#don').val(result.don);
						$('input[name="holdStatus"][value="true"]').prop('checked', true);
						$('#site_type option[value="'+result.siteType+'"]').prop('selected', true);
						
						// actions
						$('input[name="actions[]"]').prop('checked', false);
						$.each(result.actions, function(i, val){
							$('input[name="actions[]"][value="'+val+'"]').prop('checked', true);
						});
						// discipline
						$('.disp-sel').prop('checked', false);
						$('input[name="discipline_id[]"]').prop('checked', false);
						$.each(result.discipline, function(i, val){
							$('input[name="discipline_id[]"][value="'+val+'"]').prop('checked', true);
						});
					}
				})
			})
		</script>
	@endif
@stop