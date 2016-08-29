@extends('core::layouts.default')

@section('content')

<h1>Training Program Reports</h1>   
{!! Form::open(['route' => 'reports.generate', 'method' => 'POST', 'id' => 'generate-frm']) !!}
<div class="row">
    {{-- 1) Report Type --}}
    <div class="col-sm-8">
        <div class="well">
            <div class="form-group">
                {!! Form::label('discipline', 'Select Discipline') !!} @include('core::partials.required')
                {!! Form::select('discipline', $disciplines) !!}
            </div>
            <div class="form-group">
                {!! Form::label('report_type', 'Report Type') !!} @include('core::partials.required')
                {!! Form::select('report_type', $reportTypes) !!}
                <span class="text-danger">{{ $errors->first('report_type') }}</span>
            </div>
            <div class="form-group">
                {!! Form::label('license', 'License') !!} @include('core::partials.required', ['class' => 'hide'])
                {!! Form::text('license') !!}
            </div>
        </div>
    </div>

    {{-- 2) Date Range --}}
    <div class="col-sm-4">
        <div class="well">
            <div class="form-group">
                {!! Form::label('from', 'From Date') !!} @include('core::partials.required')
                {!! Form::text('from', null, ['data-provide' => 'datepicker']) !!}
                <span class="text-danger">{{ $errors->first('from') }}</span>
            </div>
            <div class="form-group">
                {!! Form::label('to', 'To Date') !!}
                {!! Form::text('to', null, ['data-provide' => 'datepicker']) !!}
                <span class="text-danger">{{ $errors->first('to') }}</span>
            </div>
        </div>
        
        <div class="form-group">
            <a data-href="{{ route('reports.find_license') }}" class="btn btn-block btn-success" id="find">{!! Icon::search() !!} Find</a>
        </div>
    </div>
</div>

<hr>

<h3>Select Report</h3>
<div class="well table-responsive">
    {{-- Programs/Children --}}
    <table class="table table-striped" id="data-table">
        <thead>
            <tr>
                <th>{{ Lang::choice('core::terms.facility_training', 1) }}</th>
                <th>{{ Lang::choice('core::terms.instructor', 1) }}</th>
                <th>License</th>
                <th class="col-md-1"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td align="center" colspan="4">
                    No Results
                </td>
            </tr>
        </tbody>
    </table>
</div>
{!! Form::close() !!}
@stop

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            // Variables
            var $reportType = $('#report_type');
            var $license    = $('#license');
            var $disc       = $('#discipline');

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

            // perform ajax validation on report type, etc...
            $(document).on('click', '#find', function(e){
                e.preventDefault();

                var lic  = $license.val();
                var disc = $disc.val();

                // empty table rows
                $('#data-table tbody tr').remove();

                $.ajax({
                    url: '/reports/discipline/'+disc+'/'+lic,
                    data: {'report_type': $reportType.val()},
                    success: function(result){   
                        if(result) 
                        {
                            if($.isEmptyObject(result)) {
                                flash('No reports found matching that criteria', 'danger');
                                return;
                            }

                            $.each(result, function(i, val)
                            {
                                var instructor_name_link = val.instructor;
                                var program_name_link    = val.program;
                                var status               = val.status ? 'Active' : 'Inactive';
                                var spanClass            = val.status ? 'success' : 'warning';
                                var license              = val.license ? val.license + '<br><span class="label label-' + spanClass + '">' + status + '</span>' : '';

                                if(val.link)
                                {
                                    if(val.set == 'instructor')
                                        instructor_name_link = $('<a>').attr('href', '/instructors/'+val.id+'/edit').html(val.instructor);
                                    else if (val.set == 'all_facilities')
                                        program_name_link = $('<span>').html('All Training Programs');
                                    else
                                        program_name_link = $('<a>').attr('href', '/facilities/'+val.id+'/edit').html(val.program);
                                }
                               
                                var btnVal = val.set+','+val.id;

                                if(val.license) {
                                    btnVal += ','+val.license;
                                }

                                var newRow = $('<tr>')
                                                .append($('<td>')
                                                    .append(program_name_link)
                                                )
                                                .append($('<td>')
                                                    .append(instructor_name_link)
                                                )
                                                .append($('<td class="monospace">')
                                                    .append(license)
                                                )
                                                .append($('<td>')
                                                    .append($('<button>')
                                                        .append('Generate')
                                                        .attr('name', 'info')
                                                        .attr('class', 'btn btn-sm btn-primary')
                                                        .attr('type', 'submit')
                                                        .attr('value', btnVal)
                                                    )
                                                );

                                $('#data-table').find('tbody').append(newRow);
                            });
                        }
                    },
                    error: function(message) {
                        $.each(message.responseJSON, function(i, msg) {
                            flash(msg, 'danger');
                        });
                    }
                }); 
            });
        });
    </script>
@stop