@if(method_exists($results, 'appends'))
    <div class="pagination-contain">
        <small class="total-results">Showing {{ $results->firstItem() }} - {{ $results->lastItem() }} of {{ $results->total() }} total results</small>
        <div class="links">{!! $results->appends(Input::except('page'))->render() !!}</div>
    </div>
@endif