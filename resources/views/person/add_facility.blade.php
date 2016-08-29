@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => ['person.facility.store', $type, $person->id]]) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Add Facility <small>{{ $person->full_name }} - {{ $singular }}</small></h1>
			</div>

			{!! HTML::backlink($type.'.edit', $person->id, 'Back to '.$person->full_name) !!}
		</div>

		<div class="well">
			<div class="form-group">
				{!! Form::label('discipline_id', 'Select Discipline') !!}
				{!! Form::select('discipline_id', $disciplines, $selDiscipline) !!}
				<span class="text-danger">{{ $errors->first('discipline_id') }}</span>
			</div>

			<div class="form-group">
				{!! Form::label('facility_id', 'Select '.Lang::choice('core::terms.facility', 1)) !!}
				{!! Form::select('facility_id', $facilities) !!}
				<span class="text-danger">{{ $errors->first('facility_id') }}</span>
			</div>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		{!! Button::success(Icon::plus_sign().' Create')->submit() !!}
	</div>
	{!! Form::close() !!}
@stop

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			$(document).on('change', '#discipline_id', function(e){
				var disciplineId = $(this).val();

				// reset training programs dropdown
				$('#facility_id').empty().append(
					$('<option></option>').val(0).html("Select Facility")
				);

				if(disciplineId == 0)
				{
					return;
				}

				$.ajax({
					url: "/{{ $type }}/{{ $person->id }}/discipline/" + disciplineId + "/facility/available",
					success: function(result){
						$.each(result, function(i, fac){
							$('#facility_id').append($('<option></option>').val(fac.id).html(fac.name));
			        	});
			        }
				});
			});
		});
	</script>
@stop