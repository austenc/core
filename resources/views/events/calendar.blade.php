@extends('core::layouts.default')

@section('content')
	<div class="row">
		<div class="col-md-9">
            <div class="well">
                <div id="calendar"></div>
            </div>
		</div>
		
		<div class="col-md-3">
            @include('core::events.sidebars.index')
        </div>
	</div>
@stop

@section('scripts')
    <script type="text/javascript">
    $(document).ready(function(){
        var calendar = $('#calendar').fullCalendar({
            defaultView: 'month',
            allDayDefault: false,
            events: '/events/json?city={{{ Input::get("city") }}}&facility={{{ Input::get("facility") }}}',
            eventRender: function(event, element) {

                // Add icon if paper event
                if (event.paper) {
                    element.find('.fc-title').prepend('<i class="glyphicon glyphicon-file"></i> ');
                }

                // Lock icon if closed event
                if (event.closed) {
                    element.find('.fc-title').prepend('<i class="glyphicon glyphicon-lock"></i> ');
                }

                // Strikethrough the title if the event is full
                if (event.isFull) {
                    element.find('.fc-title').css('text-decoration', 'line-through');
                }

                // Specify if knowledge / skill tests full
                var tooltipTitle = event.title;
                if (event.fullKnowledge) {
                    tooltipTitle += '<br> Knowledge Test Full';
                }
                if (event.fullSkill) {
                    tooltipTitle += '<br> Skill Test Full'
                }
                
                // Add tooltip with full name
                $(element).tooltip({
                    title: tooltipTitle,
                    container: 'body',
                    html: true
                });
            }
        }); 

        $('.calendar-filter').change(function(){
            $('#filter-form').submit();
        });
    });
    </script>
@stop