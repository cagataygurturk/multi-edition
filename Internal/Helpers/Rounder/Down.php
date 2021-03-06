<?php namespace ZN\Helpers\Rounder;

use ZN\Helpers\Rounder\Exception\LogicException;

class Down
{
    //--------------------------------------------------------------------------------------------------------
    //
    // Author     : Ozan UYKUN <ozanbote@gmail.com>
    // Site       : www.znframework.com
    // License    : The MIT License
    // Copyright  : (c) 2012-2016, znframework.com
    //
    //--------------------------------------------------------------------------------------------------------

    //--------------------------------------------------------------------------------------------------------
    // Do
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $number
    // @param int    $count
    //
    //--------------------------------------------------------------------------------------------------------
    public function do(Float $number, Int $count = 0) : Float
    {
        if( $count === 0 )
        {
            return floor($number);
        }

        $numbers = explode(".", $number);

        $edit = 0;

        if( ! empty($numbers[1]) )
        {
            $edit = substr($numbers[1], 0, $count);

            return (float) $numbers[0].".".$edit;
        }
        else
        {
            throw new LogicException('[Rounder::down()] -> Decimal values can not be specified for the integer! Check 2.($count) parameter!');
        }
    }
}
