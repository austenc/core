<li class="treeview {{!! HTML::activeClass(['utilities']) !!}}">
    <a href="#">
        <i class="glyphicon glyphicon-equalizer"></i>
        <span>Utilities</span>
        <i class="fa fa-angle-left pull-right"></i>
    </a>

    <ul class="treeview-menu">
        <li class="dropdown-header">Overview</li>
        {!! HTML::nav('utilities/test/history', 'Test History') !!}
        
        <li class="divider"></li>

        <li class="dropdown-header">Account Services</li>
        {!! HTML::nav('utilities/users/merge', 'Merge User') !!}
    </ul>
</li>