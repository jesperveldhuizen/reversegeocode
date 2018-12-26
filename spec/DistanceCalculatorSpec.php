<?php

namespace spec\Fieldhousen\ReverseGeocode;

use Fieldhousen\ReverseGeocode\DistanceCalculator;
use PhpSpec\ObjectBehavior;

class DistanceCalculatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DistanceCalculator::class);
    }

    function it_calculates_correct_distance()
    {
        $this::calculate(53.00046911, 6.54232354, 52.98519933, 6.54093195)->shouldReturn(1700.47);
    }
}
