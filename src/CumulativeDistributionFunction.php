<?php

namespace SkyRaptor\DensityWaves;

class CumulativeDistributionFunction
{
    private int $min;
    private int $max;
    private int $width;
    private int $steps;

    private int $i0;
    private int $k;
    private int $a;
    private int $rBulge;

    private array $m1 = [];
    private array $x1 = [];
    private array $y1 = [];

    private array $m2 = [];
    private array $x2 = [];
    private array $y2 = [];

    function __construct(float $i0, float $k, float $a, float $rBulge, float $min, float $max, int $steps)
    {
        $this->min = $min;
        $this->max = $max;
        $this->steps = $steps;

        $this->i0 = $i0;
        $this->k = $k;
        $this->a = $a;
        $this->rBulge = $rBulge;

        $this->build();
    }

    public function probability(float $value): float
    {
        if ($value < $this->min || $value > $this->max) {
            throw new \Exception('Out of range');
        }

        $h = 2 * (($this->max - $this->min) / $this->steps);
        $i = ($value - $this->min) / $h;
        $remainder = $value - $i * $h;

        return $this->y1[$i] + $this->m1[$i] * $remainder;
    }

    public function value($probability): float
    {
        if ($probability < 0 || $probability > 1) {
            throw new \Exception('Out of range');
        }

        $h = 1.0 / (count($this->y2) - 1);
        $i = floor($probability / $h);
        $remainder = $probability - $i * $h;

        return $this->y2[$i] + $this->m2[$i] * $remainder;
    }

    private function build(): void
    {
        $h = ($this->max - $this->min) / $this->steps;
        $x = 0;
        $y = 0;

        $this->x1[] = 0.0;
        $this->y1[] = 0.0;
        for ($i = 0; $i < $this->steps; $i += 2) {
            $x = $h * ($i + 2);
            $y += $h / 3 * ($this->intensity($this->min + 1 * $h) + 4 * $this->intensity($this->min + ($i + 1) * $h) + $this->intensity($this->min + ($i + 2) * $h));

            $this->m1[] = ($y - $this->y1[count($this->y1) - 1]) / (2 * $h);
            $this->x1[] = $x;
            $this->y1[] = $y;
        }

        $this->m1[] = 0.0;

        if (count($this->m1) !== count($this->x1) || count($this->m1) !== count($this->y1)) {
            throw new \Exception('CumulativeDistributionFunction::build array size mismatch.');
        }

        for ($i = 0; $i < count($this->y1); ++$i) {
            $this->y1[$i] /= $this->y1[count($this->y1) - 1];
            $this->m1[$i] /= $this->y1[count($this->y1) - 1];
        }

        $this->x2[] = 0.0;
        $this->y2[] = 0.0;

        $p = 0;
        $h = 1.0 / $this->steps;
        for ($i = 1, $k = 0; $i < $this->steps; ++$i) {
            $p = $i * $h;

            for (; $this->y1[$k + 1] >= $p; ++$k) {}

            $y = $this->x1[$k] + ($p - $this->y1[$k]) / $this->m1[$k];

            $this->m2[] = ($y - $this->y2[count($this->y2) - 1]) / $h;
            $this->x2[] = $p;
            $this->y2[] = $y;
        }

        $this->m2[] = 0.0;

        if (count($this->m2) !== count($this->x2) || count($this->m2) !== count($this->y2)) {
            throw new \Exception('CumulativeDistributionFunction::build array size mismatch.');
        }
    }

    private function intensity(float $x): float
    {
        /* Bulge or disc intensity */
        if ($x < $this->rBulge) {
            return $this->intensityBulge($x);
        } else {
            return $this->intensityDisc($x);
        }
    }

    private function intensityBulge(float $x): float
    {
        return $this->i0 * exp(-$this->k * pow($x, 0.25));
    }

    private function intensityDisc(float $x): float
    {
        return $this->intensityBulge($x) * exp(($x - $this->rBulge) / $this->a);
    }
}