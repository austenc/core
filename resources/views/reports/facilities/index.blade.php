@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => 'reports.generate', 'method' => 'POST', 'id' => 'generate-frm']) !!}
<div class="col-sm-9">
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

        <hr>

        {{-- Select Report to run --}}
        <div class="form-group">
            {!! Form::label('facility', 'Select '. Lang::choice('core::terms.facility_training', 1)) !!}
            <table class="table table-striped" id="generate-for-program">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>License</th>
                        <th class="col-md-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            {{ $facility->name }}<br>
                            <span class="label label-info">Logged In</span>
                        </td>
                        <td class="monospace">{{ Session::get('discipline.license') }}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" type="submit" id="generate-all-btn" name="info" value="facility,{{ $facility->id }},{{ Session::get('discipline.license') }}">
                                {!! Icon::random() !!} Generate
                            </button>
                        </td>
                    </tr>

                    {{-- Child Programs --}}
                    @foreach($children as $child)
                        <tr>
                            <td>
                                {{ $child->name }}<br>
                                <span class="label label-default">Child</span>
                            </td>
                            <td class="monospace">{{ $child->disciplines->first()->pivot->tm_license }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" type="submit" id="generate-all-btn" name="info" value="facility,{{ $child->id }}">
                                    {!! Icon::random() !!} Generate
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Select Report to run --}}
        @if( ! $instructors->isEmpty())
        <div class="form-group">
            {!! Form::label('instructors', 'Select '. Lang::choice('core::terms.instructor', 1)) !!}
            <table class="table table-striped" id="generate-for-instructor">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>License</th>
                        <th class="col-md-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($instructors as $instructor)
                        <tr>
                            <td>
                                {{ $instructor->full_name }}<br>
                                @if($instructor->pivot->active)
                                    <span class="label label-success">Active</span>
                                @else
                                    <span class="label label-default">Inactive</span>
                                @endif
                            </td>
                            <td class="monospace">
                                {{ $instructor->pivot->tm_license }}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" type="submit" name="info" value="instructor,{{ $instructor->id }},{{ $instructor->pivot->tm_license }}">
                                    {!! Icon::random() !!} Generate
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</div>

<div class="col-md-3 sidebar">
    
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

        });
    </script>
@stop