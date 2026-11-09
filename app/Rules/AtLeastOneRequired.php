<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AtLeastOneRequired implements Rule
{
    protected $fields;

    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function passes($attribute, $value)
    {
        foreach ($this->fields as $field) {
            if (request()->input($field) == '1') {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'At least one of the following fields must have a value of Yes: ' . implode(', ', $this->fields);
    }

}
