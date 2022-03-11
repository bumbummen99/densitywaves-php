<?php

namespace SkyRaptor\DensityWaves;

class Position
{
    public float $x;

    public float $y;

    public float $z;

    function __construct(float $x = 0, float $y = 0, float $z = 0)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }    
}