@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => 'reports.generate', 'method' => 'POST', 'id' => 'generate-frm']) !!}
<div class="col-xs-12">
    <h1>Reports</h1>   
    <div class="well">
        <div class="form-group">
            {!! Form::label('report_type', 'Report Type') !!} @include('core::partials.required')
            {!! Form::select('report_type', $reportTypes) !!}
            <span class="text-danger">{{ $errors->first('report_type') }}</span>
        </div>

        <div class="form-group">
            {!! Form::label('from', 'From Date') !!}
            {!! Form::text('from', null, ['class' => 'datepicker']) !!}
            <span class="text-danger">{{ $errors->first('from') }}</span>
        </div>
        <div class="form-group">
            {!! Form::label('to', 'To Date') !!}
            {!! Form::text('to', null, ['class' => 'datepicker']) !!}
            <span class="text-danger">{{ $errors->first('to') }}</span>
        </div>
        
        <div class="form-group">
            {!! Form::label('training_program', Lang::choice('core::terms.facility_training', 1)) !!}
            {!! Form::text('training_program', Session::get('discipline.program.name'), ['disabled']) !!}
        </div>
    </div>
    <button class="btn btn-primary" type="submit" id="generate-all-btn" name="info" value="instructor,{{ $instructor->id }},{{ Session::get('discipline.program.license') }}">
        {!! Icon::list_alt() !!} Generate Report
    </button>
</div>


{!! Form::hidden('discipline', Session::get('discipline.id')) !!}
{!! Form::close() !!}
@stop

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {

            // adjust where datepicker appears so doesnt get cutoff
            $('.datepicker').datepicker({orientation: 'bottom'});

            // Variables
            var $reportType = $('#report_type');
            var $license    = $('#license');

            // when changing report type, make UI indicate required license if pass/fail
            if ($reportType.val() == 'pass_fail') {
                $license.parent().find('.required-field').removeClass('hide').show();
            }

            // When selecting a report type
            $reportType.change(function() {
               if ($reportType.val() == 'pass_fail') {
                 $license.parent().find('.required-field').show();
               } else {
                 $license.parent().find('.required-field').hide()
               }
            });

        });
    </script>
@stop