<?php namespace Hdmaster\Core\Helpers;

use \InputField;

class BBCode
{

    /**
     * Parses a radio input
     */
    public static function parseRadio($input, $parent_id, $parse_type='web', $input_data=array())
    {
        $field_count = $_SESSION['bbcode_field_count'];
        $field_name = $parent_id.'-radio-'.$field_count;
        $options = ! empty($input->value) ? explode('|', $input->value) : [];

        $returnStr = ($parse_type === 'web') ? '' : "\n\n".'<br>Please Circle <strong>ONE</strong>:<br> '."\n\n";

        // radio options
        foreach ($options as $opt) {
            $val = explode(',', $opt);
            
            if ($parse_type === 'web') {
                // if response exists, and this radio was the selected response, reselect it
                if (isset($input_data[$field_name]) && $input_data[$field_name] == $val[0]) {
                    $returnStr .= '<input type="radio" name="'.$field_name.'" value="'.$val[0].'" checked="checked">'.$val[1]."\n";
                } else {
                    $returnStr .= '<input type="radio" name="'.$field_name.'" value="'.$val[0].'">'.$val[1]."\n";
                }
            } else {
                $returnStr .= $val[0]. ' | ';
            }
        }
        
        // trim off any trailing ' | ' if it's a paper test
        return rtrim($returnStr, ' | ');
    }

    /**
     * Parses a dropdown input
     */
    public static function parseDropdown($input, $parent_id, $parse_type='web', $input_data=array())
    {
        $field_count = $_SESSION['bbcode_field_count'];
        $field_name = $parent_id.'-dropdown-'.$field_count;
        $options = ! empty($input->value) ? explode('|', $input->value) : [];

        $returnStr = ($parse_type === 'web') ? '<select name="'.$field_name.'">'."\n" : "\n\n".'<br>Please Circle <strong>ONE</strong>:<br> '."\n\n";

        // add default select option for web parsing
        if ($parse_type === 'web') {
            $returnStr .= '<option value="-1" selected>SELECT ONE</option>\n"';
        }

        // dropdown options
        foreach ($options as $opt) {
            $val = explode(',', $opt);
            
            if ($parse_type === 'web') {
                // if response exists, and this option was the selected response, reselect it
                if (isset($input_data[$field_name]) && $val[0] == $input_data[$field_name]) {
                    $returnStr .= '<option value="'.$val[0].'" selected>'.$val[1]."</option>\n";
                } else {
                    $returnStr .= '<option value="'.$val[0].'">'.$val[1]."</option>\n";
                }
            } else {
                $returnStr .= $val[1]. ' | ';
            }
        }

        // Closing tag?
        if ($parse_type === 'web') {
            $returnStr .= '</select>'."\n";
        } else {
            $returnStr = rtrim($returnStr, ' | ');
        }

        return $returnStr;
    }

    /**
     * Parses a textbox input
     */
    public static function parseTextbox($input, $parent_id, $parse_type='web', $input_data=array())
    {
        $field_count = $_SESSION['bbcode_field_count'];
        $field_name = $parent_id.'-textbox-'.$field_count;

        // existing input response?
        $value = isset($input_data[$field_name]) ? $input_data[$field_name] : '';

        return ($parse_type === 'web') ? '<input type="text" name="'.$field_name.'" value="'.$value.'">'."\n" : ' _______________________';
    }

    /**
     * Finds [input id=""] bbcode style tags in a skill step, parses and returns input id
     */
    public static function parseInput($str, $parent_id, $parse_type='web', $input_data=array())
    {
        $_SESSION['bbcode_field_count'] = 1;

        // match all [input id=""] tags
        preg_match_all("^\[(.*?)\]^", $str, $out);
        $matched = $out[1];

        // loop thru all matches, grabbing the id
        foreach ($matched as $match) {
            $m = [];

            // now parse out the id value in [input id=""]
            preg_match('/^[^"]*"([^"]*)"$/', $match, $m);

            if (! empty($m)) {
                $input_id = (int) $m[1];
                $input = InputField::find($input_id);

                // couldnt find 
                if (is_null($input)) {
                    $field = '[???]';
                } else {
                    switch ($input->type) {
                        case 'dropdown':
                            $field = BBCode::parseDropdown($input, $parent_id, $parse_type, $input_data);
                            break;
                        case 'textbox':
                            $field = BBCode::parseTextbox($input, $parent_id, $parse_type, $input_data);
                            break;
                        case 'radio':
                            $field = BBCode::parseRadio($input, $parent_id, $parse_type, $input_data);
                            break;
                        default:
                    }
                }
                
                $_SESSION['bbcode_field_count'] += 1;

                // replace original input tag with html input
                $str = str_replace('[input id="'.$input_id.'"]', $field, $str);
            }
        }

        unset($_SESSION['bbcode_field_count']);
        return $str;
    }

    /**
     * Strips out any [input] stuff in the text
     */
    public static function strip($input)
    {
        return preg_replace('#\[[input].*\]#', '', $input);
    }
}
