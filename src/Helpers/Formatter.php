<?php namespace Hdmaster\Core\Helpers;

class Formatter
{

    public static function format_phone($phone)
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);
    
        if (strlen($phone) == 7) {
            return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
        } elseif (strlen($phone) == 10) {
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
        } elseif (strlen($phone) == 11) {
            // country code

            return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 ($2) $3-$4", $phone);
        } else {
            return $phone;
        }
    }

    public static function format_ssn($ssn)
    {
        $ssn = str_replace(['-', '_', ' '], '', $ssn);

        return preg_replace("/([0-9]{3})([0-9]{2})([0-9]{4})/", "$1-$2-$3", $ssn);
    }

    /**
     * Returns the value if greater than zero, otherwise another string (default '-')
     */
    public static function nonZero($value, $replaceWith = '-')
    {
        if ($value > 0) {
            return $value;
        } else {
            return $replaceWith;
        }
    }
}
