
{{-- Display the proper label --}}
@if($attempt->status == 'passed' || $attempt->status == 'failed')
    @if($attempt->seeResults || Auth::user()->ability(['Admin', 'Staff'], []))
        @if($attempt->status == 'passed')
            <span class="label label-success">
        @elseif($attempt->status == 'failed')
            <span class="label label-danger">
        @endif
    @else
        <span class="label label-default">
    @endif

{{-- Label for assigned or scheduled --}}
@elseif(in_array($attempt->status, ['assigned', 'pending']))
    <span class="label label-warning">

{{-- If it's not a status that requires a special label, use default --}}
@else
    <span class="label label-default">
@endif

    {{-- Display the proper text in the label based on attempt status --}}
    @if($attempt->status == 'assigned')
        Scheduled
    @else
        {{-- If passed failed, can they see results? --}}
        @if($attempt->status == 'passed' || $attempt->status == 'failed')
            @if($attempt->seeResults || Auth::user()->ability(['Admin', 'Staff'], []))
                {{ ucfirst($attempt->status) }}
            @else
                Being Scored
            @endif
        @else {{-- Otherwise, just show the status! --}}
            {{ ucfirst($attempt->status) }}
        @endif
    @endif

{{-- End the label --}}
</span>