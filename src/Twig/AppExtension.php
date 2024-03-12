<?php

namespace App\Twig ;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('is_decimal', [$this, 'isDecimal']),
            new TwigFilter('format_number', [$this, 'formatNumber']),
        ];
    }

    public function isDecimal($number)
    {
        return is_float($number) || is_numeric($number) && strpos($number, '.') !== false;
    }

    public function formatNumber($number)
    {
        if(is_float($number) || is_numeric($number) && strpos($number, '.') !== false)
        {
            $fractional_part = fmod($number, 1);
            if($fractional_part > 0)
                return number_format($number,2,","," ") ;
            else
                return number_format($number,0,","," ") ;
        }
        else
        {
            return number_format($number,0,","," ") ;
        }
    }
}

