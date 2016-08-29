<li class="{{ Request::is('scores/*') ? 'active' : null }}">
    <a href="{{ route('scores.pending') }}">
        <i class="glyphicon glyphicon-ok"></i>
        <span>
            Scoring <span class="badge pending-scores-count">{{{ $pendingScores or '' }}}</span>
        </span>
    </a>
</li>