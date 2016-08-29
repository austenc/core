<div id="calendar">
    <div id="calendar-load" style="z-index: 9999; position: absolute; top: 0; left: 0; background: url('/vendor/hdmaster/core/img/gear.svg') center center no-repeat; width: 100%; min-height: 100%; background-color: rgba(255, 255, 255, 0.4);">
        &nbsp;
    </div>
</div>
<br>
@include('core::events.calendar_legend')

@section('scripts')
    @parent

    <script type="text/javascript">
    $(document).ready(function(){
        var calendar = $('#calendar').fullCalendar({
            defaultView: 'month',
            allDayDefault: false,
            events: {
                url: '/events/json?city={{{ Input::get("city") }}}&facility={{{ Input::get("facility") }}}',
                global: false
            },
            eventAfterAllRender: function(view) {
                $('#calendar-load').hide();
            },
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