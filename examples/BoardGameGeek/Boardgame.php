<?php

class Boardgame extends BoardGameGeekApi
{
    protected $elementName = 'boardgame';

    public function getNameAttribute($value)
    {
        return is_array($value) ? $value[0] : $value;
    }

    public function resolveFind($data)
    {
        return $data['boardgame'];
    }
}
