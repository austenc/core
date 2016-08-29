@extends('core::layouts.default')

@section('content')
    <div class="col-md-9">
        <div class="row">
            <div class="col-xs-8">
                <h1>
                    Past Events <small>{{ $facility->name }}</small>
                </h1>
            </div>
            <div class="col-xs-4 back-link">
                <a href="{{ route('facilities.edit', $facility->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to {{ Lang::choice('core::terms.facility_testing', 1) }}</a>
            </div>
        </div>

        <div class="alert alert-warning">
            <strong>{!! Icon::exclamation_sign() !!} Results</strong> Showing {{ $facility->events->count() }} Past events.
        </div>

        <div class="well table-responsive">
            @if($facility->events->isEmpty())
                No Past Test Events
            @else
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Test Date</th>
                        <th class="hidden-xs">{{ Lang::choice('core::terms.observer', 1) }}</th>
                        <th>Discipline Exams</th>
                        <th>Ended</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($facility->events as $event)
                    <tr>
                        <td><span class="text-muted lead">{{ $event->id }}</span></td>
                        <td>
                            {{ $event->test_date }}<br>
                            <small>{{ $event->start_time }}</small>
                        </td>

                        <td class="hidden-xs">
                            @if(Auth::user()->ability(['Admin', 'Staff'], []))
                            <a href="{{ route('observers.edit', $event->observer->id) }}">{{ $event->observer->commaName }}</a>
                            @else
                            {{ $event->observer->commaName }}
                            @endif
                        </td>

                        <td>
                            <strong>{{ $event->discipline->name }}</strong><br>
                            @if( ! $event->exams->isEmpty())
                                {!! implode('<br>', $event->exams->lists('pretty_name')) !!}<br>
                            @endif

                            @if( ! $event->skills->isEmpty())
                                {!! implode('<br>', $event->skills->lists('pretty_name')) !!}<br>
                            @endif
                        </td>

                        <td>
                            @if($event->ended)
                                {{ date('m/d/Y H:i A', strtotime($event->ended)) }}
                            @endif
                        <td>
                            <div class="pull-right btn-group">
                                @if(Auth::user()->can('events.edit'))
                                    <a title="Edit" data-toggle="tooltip" href="{{ route('events.edit', $event->id) }}" class="btn btn-link pull-right">
                                        {!! Icon::pencil() !!}
                                    </a>
                                @endif
                                
                                {{-- Regional/Closed --}}
                                @if($event->is_regional)
                                    <a title="{{{ Lang::get('events.regional') }}} Event" data-toggle="tooltip" class="btn btn-link">{{ Icon::globe() }}</a>
                                @else
                                    <a title="{{{ Lang::get('events.closed') }}} Event" data-toggle="tooltip" class="btn btn-link">{{ Icon::flag() }}</a>
                                @endif
                                
                                {{-- Paper/Web --}}
                                @if($event->is_paper)
                                    <a title="Paper Event" data-toggle="tooltip" class="btn btn-link">{!! Icon::file() !!}</a>
                                @endif

                                {{-- Locked/Unlocked --}}
                                @if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []) && $event->locked)
                                    <a title="Locked Event" data-toggle="tooltip" class="btn btn-link">{!! Icon::lock() !!}</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

    </div>
    <div class="col-md-3 sidebar">
    </div>
@stop