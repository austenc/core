<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active">
        <a href="#facility-info" aria-controls="facility info" role="tab" data-toggle="tab">{!! Icon::info_sign() !!} Facility Info</a>
    </li>

    @if(Auth::user()->can('facilities.view_events') && ! $facility->events->isEmpty())
        <li role="presentation">
            <a href="#facility-events" aria-controls="facility events" role="tab" data-toggle="tab">{!! Icon::calendar() !!} Test Events</a>
        </li>
    @endif

    @if($facility->allDisciplines)
        @foreach($facility->allDisciplines as $d)
            @if($d->pivot->active || Auth::user()->ability(['Admin', 'Staff'], []))
                <li role="presentation" class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        {!! Icon::briefcase() !!} {{ $d->name }} <span class="caret"></span>
                        @if( ! $d->pivot->active)
                            <small>(disabled)</small>
                        @endif
                    </a>

                    <ul class="dropdown-menu">
                        <li>
                            <a href="#facility-discipline-{{{ strtolower($d->abbrev) }}}-info" aria-controls="facility discipline" role="tab" data-toggle="tab">
                                Info
                            </a>
                        </li>

                        {{-- Instructors --}}
                        <li>
                            <a href="#facility-discipline-{{{ strtolower($d->abbrev) }}}-instructors" class="{{ $instructors[$d->id]->isEmpty() ? 'hide' : '' }}" aria-controls="facility instructors" role="tab" data-toggle="tab">
                                {{ Lang::choice('core::terms.instructor', 2) }}
                            </a>
                        </li>

                        {{-- Test Team --}}
                        <li>
                            <a href="#facility-discipline-{{{ strtolower($d->abbrev) }}}-testteam" class="{{ $testteam[$d->id]->isEmpty() ? 'hide' : '' }}" aria-controls="facility test team" role="tab" data-toggle="tab">
                                Test Team
                            </a>
                        </li>

                        {{-- Students --}}
                        <li>    
                            <a href="#facility-discipline-{{{ strtolower($d->abbrev) }}}-students" class="{{ $students[$d->id]->isEmpty() ? 'hide' : '' }}" aria-controls="facility students" role="tab" data-toggle="tab">
                                {{ Lang::choice('core::terms.student', 2) }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
        @endforeach
    @endif
</ul>
