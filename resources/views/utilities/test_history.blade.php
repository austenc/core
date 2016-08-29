@extends('core::layouts.default')

@section('content')
    {!! Form::open(['route' => 'utilities.test.history']) !!}
    <div class="col-md-9">
        <div class="row">
            <div class="col-xs-8">
                <h1>Test History</h1>
            </div>
        </div>

        <div class="alert alert-info">
            @if( ! is_null($history))
                <strong>{!! Icon::info_sign() !!} Results</strong> from <span class="monospace">{{ Input::get('start') }}</span> to <span class="monospace">{{ Input::get('end') ?: date('m/d/Y', strtotime("+ 1 day")) }}</span> showing
            @else
                <strong>{!! Icon::info_sign() !!} Results</strong> Enter a Start date to begin generating history.</span>
            @endif
        </div>

        <h3>Date Range</h3>
        <div class="well">
            <div class="form-group">
                {!! Form::label('start', 'Start Date') !!}
                {!! Form::text('start', $start, ['data-provide' => 'datepicker']) !!}
                <span class="text-danger">{{ $errors->first('start') }}</span>
            </div>
            <div class="form-group">
                {!! Form::label('end', 'End Date') !!}
                {!! Form::text('end', $end, ['data-provide' => 'datepicker']) !!}
                <span class="text-danger">{{ $errors->first('end') }}</span>
            </div>
        </div>

        @if( ! is_null($history))
            {{-- Test History --}}
            @foreach($disciplines as $discipline)
                <h3>{{ $discipline->name }}</h3>
                <div class="well">
                    {{-- Knowledge Exams --}}
                    @if( ! $discipline->exams->isEmpty())
                        @foreach($discipline->exams as $exam)
                            <h4>
                                Knowledge <small>{{ $exam->name}}</small>
                            </h4>
                            <table class="table table-striped table-hover">
                                <thead>
                                    <th class="col-md-8">Status</th>
                                    <th>Total</th>
                                </thead>
                                <tbody>
                                    @foreach($endTestStatus as $status)
                                        <tr>
                                            <td>{{ ucfirst($status) }}</td>
                                            <td class="monospace">{{ $history[$discipline->id]['knowledge'][$exam->id][$status] }}</td>
                                        </tr>
                                    @endforeach

                                    {{-- Totals --}}
                                    <tr class="strong">
                                        <td>Total</td>
                                        <td class="monospace">{{ $history[$discipline->id]['knowledge'][$exam->id]['total'] }}</td>
                                    </tr>

                                    {{-- Orals --}}
                                    <tr>
                                        <td>Oral <small>(included in Total)</small></td>
                                        <td class="monospace">{{ $history[$discipline->id]['knowledge'][$exam->id]['oral'] }}</td>
                                    </tr>

                                    {{-- Pending Scores --}}
                                    <tr>
                                        <td>Pending Scores <small>(excluded from Total)</small></td>
                                        <td class="monospace">{{ $history[$discipline->id]['knowledge'][$exam->id]['pending_scores'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endforeach
                    @endif

                    {{-- Skill Exams --}}
                    @if( ! $discipline->skills->isEmpty())

                        {{-- Add Divider if Knowledge exists --}}
                        @if( ! $discipline->exams->isEmpty())
                            <hr>
                        @endif

                        @foreach($discipline->skills as $skill)
                            <h4>
                                Skill <small>{{ $skill->name }}</small>
                            </h4>
                            <table class="table table-striped table-hover">
                                <thead>
                                    <th class="col-md-8">Status</th>
                                    <th>Total</th>
                                </thead>
                                <tbody>
                                    @foreach($endTestStatus as $status)
                                        <tr>
                                            <td>{{ ucfirst($status) }}</td>
                                            <td class="monospace">{{ $history[$discipline->id]['skill'][$skill->id][$status] }}</td>
                                        </tr>
                                    @endforeach

                                    {{-- Totals --}}
                                    <tr class="strong">
                                        <td>Total</td>
                                        <td class="monospace">{{ $history[$discipline->id]['skill'][$skill->id]['total'] }}</td>
                                    </tr>

                                    {{-- Pending Scores --}}
                                    <tr>
                                        <td>Pending Scores <small>(excluded from Total)</small></td>
                                        <td class="monospace">{{ $history[$discipline->id]['skill'][$skill->id]['pending_scores'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endforeach
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    <div class="col-md-3 sidebar">
        <div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core::affixOffset') }}" data-clampedwidth=".sidebar">
            {!! Button::success(Icon::flash() . ' Generate')->submit()->block() !!}
        </div>
    </div>
    {!! Form::close() !!}
@stop