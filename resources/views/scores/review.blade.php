@extends('core::layouts.default')

@section('content')

<h1>Review Test</h1>

<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
  <li role="presentation" class="active"><a href="#review" aria-controls="review" role="tab" data-toggle="tab">Review Score</a></li>
  <li role="presentation"><a href="#revisions" aria-controls="revisions" role="tab" data-toggle="tab">Revision History</a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content well">
    <div role="tabpanel" class="tab-pane active" id="review">
        {!! Form::open(['route' => 'scores.update']) !!}

        <div class="row">
            {{-- Test Info --}}
            <div class="col-lg-2">
                <p class="lead">Test Information</p>
                <div class>
                    <p>
                        <strong>{{ Lang::choice('core::terms.student', 1) }}</strong> <br>
                        {{ $person->commaName }}
                    </p>
                    <p>
                        <strong>{{ Lang::choice('core::terms.facility_testing', 1) }}</strong> <br>
                        {{ $event->facility->name }}
                    </p>
                    <p>
                        <strong>Test Date</strong> <br>
                        {{ $event->test_date }} 
                    </p>
                </div>

                <hr>

                {{-- Skill score --}}
                @if($skill && in_array($skill->status, ['passed', 'failed']))
                    <h4>Skills: <span class="text-{{ $skill->statusClass }}">{{{ ucfirst($skill->status) }}}</span></h4>
                    <table class="table-striped table-condensed">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>                        
                            @foreach($skill->skilltest->tasks as $task)
                                <?php
                                    $taskId = $task->id;
                                    $response = $skill->responses->filter(function ($response) use ($taskId) {
                                        return $response->skilltask_id == $taskId;
                                    })->first();
                                ?>
                                <tr>
                                    <td>
                                        <strong><small>{{{ $task->title }}}</small></strong>
                                    </td>
                                    <td class="text-right">
                                        <small>
                                            @if($response) <strong>{{{ $response->scoreReason }}}</strong>{{{ $response->score }}}% @endif
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        
                    </table>
                @endif

                {{-- No-show warnings --}}
                @if($knowledge && $knowledge->status == 'noshow')
                    <div class="alert alert-info">
                        <small>Knowledge no-show</small>
                    </div>
                @endif
                @if($skill && $skill->status == 'noshow')
                    <div class="alert alert-info">
                        <small>Skill no-show</small>
                    </div>
                @endif
            </div>

            {{-- Scantron Form --}}
            <div class="col-lg-10">
                <div class="scantron-form">
                    {{-- Show answers checkbox --}}
                    @if($pendingKnowledge)
                        <div class="alert alert-info">
                            <div class="form-group">
                                {{-- List their actual score --}}
                                @if($knowledgeScore)
                                    <h3>
                                        Knowledge test score: <strong>{{ $knowledgeScore }}</strong> ({{ $knowledgeStatus }})
                                    </h3>
                                @endif
                
                                <div class="checkbox">
                                    <label>
                                        {!! Form::checkbox('show_answers', true, false) !!} Show Answers?
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif
                
                    <p class="lead">Responses</p>
                
                    {{-- Form for selecting answers --}}
                    <div class="answers-contain">
                        <div class="row">
                            @foreach(range(0, 5) as $i)
                                {!! HTML::answerBox($kChoices, [
                                    'iteration' => $i,
                                    'answers'   => $kAnswers,
                                    'ids'       => $itemIds
                                ]) !!}
                            @endforeach
                        </div>
                
                        <!-- second row -->
                        <div class="row">
                            @foreach(range(0, 5) as $i)
                                {!! HTML::answerBox($sChoices, [
                                    'max'         => 30,
                                    'iteration'   => $i,
                                    'rowClass'    => 'step',
                                    'ids'         => $stepIds,
                                    'flagIds'     => $keyStepIds,
                                    'comments'    => $stepComments,
                                    'tasksByStep' => $tasksByStep,
                                    'ordinals'    => $ordinals
                                ]) !!}
                            @endforeach
                        </div><!-- third row -->
                    </div>
                
                    <ul class="answer-list key-step">
                        <li>Indicates key step</li>
                    </ul>
                
                    {{-- Save answers buttons --}}
                    <button type="submit" class="btn btn-success" name="save_answers">
                        {!! Icon::save() !!} Save Answers
                    </button>
                
                    <button type="submit" class="btn btn-warning btn-accept-answers" name="accept_answers" value="true" data-confirm="Once the answers are accepted, they cannot be changed. Are you sure?">
                        {!! Icon::ok() !!} Accept Answers
                    </button>

                    <button type="submit" class="btn btn-danger" name="mark_rescheduled" value="true" data-confirm="If you mark these test attempt(s) as rescheduled, this cannot be changed. Are you sure?">
                        {!! Icon::calendar() !!} Mark Rescheduled
                    </button>
                </div><!-- Scantron Form -->
            </div>
        </div>

        {!! Form::hidden('pending_knowledge', $pendingKnowledge) !!}
        {!! Form::hidden('pending_skill', $pendingSkill) !!}
        {!! Form::close() !!}
    </div>
    <div role="tabpanel" class="tab-pane" id="revisions">
        {{-- Revision history --}}
        @include('core::scores.partials.history', [
            'history' => $kHistory,
            'title'   => 'Knowledge'
        ])
        {{-- Since there's an array of histories (one for each skilltask_response) --}}
        @if($sHistory)
            <p class="lead">Skill Revision History</p>
            @foreach($sHistory as $k => $revSet)            
                @include('core::scores.partials.history', [
                    'history' => $revSet,
                    'count'   => $k+1,
                    'title'   => 'Skill Response',
                    'stepIds' => $stepIds,
                    'skill'   => true
                ])
            @endforeach
        @endif        
    </div>
</div>

    @if(! empty($setups))
        @foreach($setups as $taskId => $setup)
            <p class="lead">Setup for {{{ array_get($taskNames, $taskId) }}}</p> 
            <p class="well">{{{ $setup->setup }}}</p>
        @endforeach 
    @endif

{!! HTML::modal('revision-modal') !!}

@stop

@section('scripts')
    <script type="text/javascript">
    $(document).ready(function() {

        // Disable double-submit in the data-confirm modal
        $('body').on('click', '#dataConfirmOK', function() {
            $('#main-ajax-load').show();
            $('button, input[type="button"], input[type="submit"]').prop('disabled', true);
        });
        $('body').on('click', '#dataConfirmCancel', function() {
           $('button, input[type="button"], input[type="submit"]').prop('disabled', false);
        });

        // Show knowledge answers
        $('body').on('click', 'input[name="show_answers"]', function(){
            $('.answers-contain').toggleClass('show-answers');
        });

        // change a knowledge answer
        $('body').on('click', 'span[class^="item-"] ul li', function() {
            handleBubbleClick($(this));
        });

        $('body').on('click', 'span[class^="step-"] ul li', function() {
            handleBubbleClick($(this), 'step');
        });
    });

    function handleBubbleClick($obj, prefix)
    {
        prefix = typeof prefix !== 'undefined' ? prefix : 'item';

        if($('span.circle.glyphicon', $obj).length > 0) {
             return false;
        }

        $parentSpan = $obj.parents('span[class^="'+prefix+'-"]');
        $parentUl   = $obj.parents('ul');
        $circle     = $('span.circle', $obj);

        // Un-mark an already marked circle
        if($circle.hasClass('filled'))
        {
            $circle.removeClass('filled');

            // un-mark this answer in hidden input
            $('input[type="hidden"]', $parentSpan).val('');
            return; 
        }

        // clear any other marks
        $('span.circle', $parentUl).removeClass('filled');

        // mark one that was clicked
        $('span', $obj).addClass('filled');

        // update the value of the hidden field!
        $('input[type="hidden"]', $parentSpan).val($('span', $obj).text());
    }
    </script>
@stop