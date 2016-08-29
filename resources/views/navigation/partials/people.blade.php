<li class="treeview {!! HTML::activeClass(['students', 'instructors', 'proctors', 'actors', 'observers']) !!}">
    <a href="#">
        <i class="glyphicon glyphicon-user"></i>
        <span>People</span>
        <i class="fa fa-angle-left pull-right"></i>
    </a>
    <ul class="treeview-menu">
        {!! HTML::nav('students', Lang::choice('core::terms.student', 2)) !!}
        {!! HTML::nav('instructors', Lang::choice('core::terms.instructor', 2)) !!}            
        {!! HTML::nav('proctors', Lang::choice('core::terms.proctor', 2)) !!}
        {!! HTML::nav('actors', Lang::choice('core::terms.actor', 2)) !!}
        {!! HTML::nav('observers', Lang::choice('core::terms.observer', 2)) !!}
    </ul>
</li>
{!! HTML::nav('facilities', Lang::choice('core::terms.facility', 2), 'home') !!}