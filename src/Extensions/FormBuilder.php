<?php namespace Hdmaster\Core\Extensions;

class FormBuilder extends \Bootstrapper\Form
{

    // Submit
    public function submit($value = null, $options = [])
    {
        if ($this->disableFields($options)) {
            $options[] = 'disabled';
        }

        return parent::submit($value, $options);
    }

    // Select
    public function select($name, $list = [], $selected = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::select($name, $list, $selected, $attributes);
    }

    // Password
    public function password($name, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::password($name, $attributes);
    }

    // Text
    public function text($name, $value = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::text($name, $value, $attributes);
    }

    // Email
    public function email($name, $value = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::email($name, $value, $attributes);
    }

    // Datetime
    public function datetime($name, $value = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::input('datetime', $name, $value, $attributes);
    }

    // Datetime Local
    public function datetimelocal($name, $value = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::input('datetime-local', $name, $value, $attributes);
    }

    // Date
    public function date($name, $value = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::input('date', $name, $value, $attributes);
    }

    // Month
    public function month($name, $value = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::input('month', $name, $value, $attributes);
    }

    // Week
    public function week($name, $value = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::input('week', $name, $value, $attributes);
    }

    // Time
    public function time($name, $value = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::input('time', $name, $value, $attributes);
    }

    // Number
    public function number($name, $value = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::input('number', $name, $value, $attributes);
    }

    // URL
    public function url($name, $value = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::input('url', $name, $value, $attributes);
    }

    // Search
    public function search($name, $value = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::input('search', $name, $value, $attributes);
    }

    // Tel
    public function tel($name, $value = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::input('tel', $name, $value, $attributes);
    }

    // Color
    public function color($name, $value = null, $attributes = [])
    {
        if ($this->disableFields($attributes)) {
            $attributes[] = 'disabled';
        }

        return parent::input('color', $name, $value, $attributes);
    }

    // Checkbox
    public function checkbox($name, $value = 1, $checked = null, $options = [])
    {
        if ($this->disableFields($options)) {
            $options[] = 'disabled';
        }
        
        return parent::checkbox($name, $value, $checked, $options);
    }

    // Radio Buttons
    public function radio($name, $value = null, $checked = null, $options = [])
    {
        if ($this->disableFields($options)) {
            $options[] = 'disabled';
        }

        return parent::radio($name, $value, $checked, $options);
    }

    // Textarea
    public function textarea($name, $value = null, $options = [])
    {
        if ($this->disableFields($options)) {
            $options[] = 'disabled';
        }

        return parent::textarea($name, $value, $options);
    }

    // Checks if the session param for disabling fields has been set
    private function disableFields($options = array())
    {
        if ($this->isDisabled()
            && (is_array($options) && ! in_array('disabled', $options))) {
            return true;
        }

        return false;
    }

    /**
     * Are fields disabled?
     */
    public function isDisabled()
    {
        return (\Session::has('disableFields') && \Session::get('disableFields') === true);
    }
}
