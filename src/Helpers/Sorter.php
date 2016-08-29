<?php namespace Hdmaster\Core\Helpers;

use \Input;
use \Icon;
use \HTML;

class Sorter
{
    
    public static function order()
    {
        if (strtolower(Input::get('order')) == 'desc') {
            return 'desc';
        } else {
            return 'asc';
        }
    }

    /**
     * Gets an up or down arrow to put on sorting columns
     * @return mixed
     */
    public static function arrow()
    {
        $input = Input::get('order');


        if ($input == 'asc') {
            return Icon::chevron_up();
        } elseif ($input == 'desc') {
            return Icon::chevron_down();
        } else {
            return '';
        }
    }

    public static function link($route, $text, $parameters = [], $attributes=[])
    {
        // only add arrow if param for sorting matches input
        if (! empty($parameters['sort'])) {
            // If this is the current sort type
            if (strtolower($parameters['sort']) === strtolower(Input::get('sort'))) {
                $text .= ' '.self::arrow();
                $parameters['order'] = self::linkOrder();
            } else {
                // is there no sort param, with a default specified?
                if (Input::get('sort') === null && array_key_exists('default', $parameters)) {
                    // add the arrow to the default
                    $text .= ' '.Icon::chevron_up();
                    ;
                    // default to descending because default links should be ascending already
                    $parameters['order'] = 'desc';
                } else {
                    $parameters['order'] = 'asc';
                }
            }

            // unset 'default' param so it never gets added to URL
            unset($parameters['default']);
        }

        // Keep any existing extraneous GET vars
        // Use the regular superglobal instead of input class
        // so we can be sure it's only GET vars that come in (and not POST!)
        $getVars = $_GET;
        unset($getVars['sort']);
        unset($getVars['order']);
        $parameters = array_merge($parameters, $getVars);

        return HTML::decode(link_to_route($route, $text, $parameters, $attributes));
    }

    public static function linkOrder()
    {
        if (strtolower(Input::get('order')) == 'asc') {
            return 'desc';
        } else {
            return 'asc';
        }
    }
}
