<?php

namespace SkyRaptor\DensityWaves;

class Particle
{
    public float $theta = 0;

    public float $velocity = 0;

    public float $angle = 0;

    public float $m_a = 0;

    public float $m_b = 0;

    public float $size = 0;

    public float $type = 0;

    public float $temperature = 0;

    public float $brightness = 0;

    public Position $position;

    function __construct()
    {
        $this->position = new Position();
    }
}