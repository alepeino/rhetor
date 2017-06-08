<?php

class Boardgame extends BoardGameGeekApi
{
    protected $elementName = 'boardgame';
    protected $driverOptions = ['resolutionAccessor' => 'boardgame'];

    public function getNameAttribute($value)
    {
        return is_array($value) ? $value[0] : $value;
    }
}
