<?php

// Determines if menu items are active when route matches
HTML::macro('activeClass', function ($pattern) {
    $matched = false;

    // Force it to be an array
    if (is_string($pattern)) {
        $pattern = (array) $pattern;
    }

    foreach ($pattern as $route) {
        // replace any dots with slashes
        $route = str_replace('.', '/', $route);

        // if it's a matching route, a child route of this main route, or the actual url
        if (Request::is($route . '/*') || Request::is($route)) {
            $matched = true;
        }
    }

    return $matched === true ? 'active' : '';
});

// Generates a list item with an anchor tag inside and automatically applies the active class
// options for iconType are 'glyphicon', 'fa', or 'ion' 
HTML::macro('nav', function ($route, $title, $icon = null, $iconType = 'glyphicon') {

    // Add icon if specified
    if ($icon !== null) {

        // Different markup depending on icon library used
        switch ($iconType) {
            case 'fa':
                $icon = '<i class="fa fa-' . $icon . '"></i>';
            break;

            case 'ion':
                $icon = '<i class="icon ion-' . $icon . '"></i>';
            break;

            case 'glyphicon':
            default:
                $icon = '<i class="glyphicon glyphicon-' . $icon . '"></i>';
        }
    }

    // Allow named routes or slash-based url routes
    $url = strpos($route, '.') !== false ? route($route) : url($route);

    // Add a 'span' tag around the title
    $title = '<span>' . $title . '</span>';

    // build the link item
    $link = '<li class="'.HTML::activeClass($route).'">';
    $link .= '<a href="' . $url . '">' . $icon . ' ' . $title . '</a>';
    $link .= '<li>';
    
    return $link;
});

// Makes a blank modal with a given ID, for use with ajax loading
HTML::macro('modal', function ($id, $class = null) {
    return View::make('core::layouts.modal')->with([
        'id'    => $id,
        'class' => $class
    ]);
});

// creates a nice 'back' arrow link used for getting from sub-pages back to index
HTML::macro('backlink', function ($routeName, $routeParams = [], $text = 'Back to all', $class = 'col-xs-4') {
    $html = '<div class="'.$class.' back-link">';
    $html .= '<a href="'.route($routeName, $routeParams).'" class="btn btn-link pull-right">'.Icon::arrow_left().' '.$text.'</a>';
    $html .= '</div>';
    return $html;
});

/**
 * Answer boxes like ABCDE
 */
HTML::macro('answerBox', function ($choices = null, $params = []) {

    // Grab params from array or set defaults
    $max         = array_key_exists('max', $params) ? $params['max'] : 20;
    $iteration   = array_key_exists('iteration', $params) ? $params['iteration'] : 0;
    $answers     = array_key_exists('answers', $params) ? $params['answers'] : null;
    $comments    = array_key_exists('comments', $params) ? $params['comments'] : []; // step comments
    $ids         = array_key_exists('ids', $params) ? $params['ids'] : null; // used for marking rows with a given ID
    $flagIds     = array_key_exists('flagIds', $params) ? $params['flagIds'] : null; // used for flagging rows with a given id (as key steps)
    $flagClass   = array_key_exists('flagClass', $params) ? $params['flagClass'] : 'key-step';
    $rowClass    = array_key_exists('rowClass', $params) ? $params['rowClass'] : 'item';
    $tasksByStep = array_key_exists('tasksByStep', $params) ? $params['tasksByStep'] : [];
    $ordinals    = array_key_exists('ordinals', $params) ? $params['ordinals'] : [];

    // figure out where we need to start if past our 1st iteration
    $start = 0;
    if ($iteration > 0) {
        $start = $iteration * $max;
    }

    // return the view with appropriate data
    return View::make('core::scantron.answer_box')->with([
        'start'       => $start,
        'choices'     => $choices,
        'answers'     => $answers,
        'comments'    => $comments,
        'rowClass'    => $rowClass,
        'max'         => $max,
        'ids'         => $ids,
        'flagIds'     => $flagIds,
        'flagClass'   => $flagClass,
        'tasksByStep' => $tasksByStep,
        'ordinals'    => $ordinals
    ]);
});

/**
 * Horizontally-slugged bubble box
 */
HTML::macro('bubbleBox', function ($columns, $title='', $params = []) {

    $bubbleRange = array_key_exists('bubbleRange', $params) ? $params['bubbleRange'] : range('A', 'Z');
    $rowClass    = array_key_exists('rowClass', $params) ? $params['rowClass'] : '';
    $includeGrid = array_key_exists('includeGrid', $params) ? $params['includeGrid'] : true;
    $data        = array_key_exists('data', $params) ? $params['data'] : null;
    $subtitle    = array_key_exists('subtitle', $params) ? $params['subtitle'] : null;
    $table       = array_key_exists('table', $params) ? $params['table'] : null;

    if ($columns > 1) {
        $rowClass .= ' striped ';
    }

    $viewName = $table === true ? 'bubble_table' : 'bubble_box';

    return View::make('core::scantron.' . $viewName)->with([
        'columns'     => $columns,
        'title'       => $title,
        'subtitle'    => $subtitle,
        'data'        => empty($data) ? null : str_split($data),
        'bubbleRange' => $bubbleRange,
        'rowClass'    => $rowClass,
        'includeGrid' => (boolean) $includeGrid
    ]);
});
