<li class="treeview {{!! HTML::activeClass(['testitems', 'testforms', 'testplans', 'exams', 'skills', 'tasks', 'skillexams', 'subjects', 'steps']) !!}}">
    <a href="#">
        <i class="glyphicon glyphicon-list-alt"></i>
        <span>Testbank</span>
        <i class="fa fa-angle-left pull-right"></i>
    </a>
    <ul class="treeview-menu">
        <li class="dropdown-header">Knowledge</li>
        <li class="divider"></li>
        {!! HTML::nav('exams', 'Exams') !!}
        {!! HTML::nav('subjects', 'Subjects') !!}
        {!! HTML::nav('testitems', 'Items') !!}
        {!! HTML::nav('testforms', 'Forms') !!}
        {!! HTML::nav('testplans', 'Plans') !!}

        <li class="dropdown-header">Skills</li>
        <li class="divider"></li>
        {!! HTML::nav('skillexams', 'Exams') !!}
        {!! HTML::nav('skills', 'Tests') !!}
        {!! HTML::nav('tasks', 'Tasks') !!}
        {!! HTML::nav('steps', 'Steps') !!}
    </ul>
</li>
