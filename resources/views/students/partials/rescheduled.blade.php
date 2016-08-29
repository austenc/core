@if(Auth::user()->ability(['Staff', 'Admin'], []) && ! empty($rescheduled))
    <h3 id="testing">Rescheduled</h3>
    <div class="well table-responsive">
        <table class="table table-striped table-hover" id="resched-table">
            <thead>
                <tr>
                    <th>Exam</th>
                    <th>Status</th>
                    <th>Test Date</th>
                    <th>Options</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($rescheduled as $r)
                    <tr>
                        <td>
                            @if(isset($r->exam_id))
                                {{ $allExams->keyBy('id')->get($r->exam_id)->name }}<br>
                                <small class="testtype">Knowledge</small>
                            @else
                                {{ $allSkills->keyBy('id')->get($r->skillexam_id)->name }}<br>
                                <small class="testtype">Skill</small>
                            @endif
                        </td>


                        <td>{{ ucfirst($r->status) }}</td>
                        <td>{{ $r->event_date }}</td>

                        <td>
                            <div class="btn-group pull-right">
                                @if(isset($r->is_oral) && $r->is_oral == 1)
                                    <a data-toggle="tooltip" title="Oral Test" class="btn btn-link">{!! Icon::volume_up() !!}</a>
                                @endif
                            </div>
                        </td>

                        <td>
                            <div class="btn-group pull-right">
                                <a href="{{ route('events.edit', $r->testevent_id) }}" class="btn btn-default btn-sm">Event</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif