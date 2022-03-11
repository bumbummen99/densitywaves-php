<?php

namespace SkyRaptor\DensityWaves;

use SkyRaptor\DensityWaves\Helpers\Math;

class Galaxy
{
    const DEG_TO_RAD = 0.01745329251;

    const GRAVITY = 6.672e-11;

    const PC_TO_KM = 3.08567758129e13;

    const SEC_PER_YEAR = 365.25 * 86400;

    private int $starsCount;

    private int $coreRadius = 6000;

    private int $galaxyRadius = 15000;

    private int $distantRadius = 0;

    private float $innerEccentricity = 0.8;

    private float $outerEccentricity = 0.8;

    private float $angularOffset = 0.019;

    private int $disturbances = 3;

    private float $disturbaneDampeningFactor = 40;

    private bool $darkMatter = true;

    private int $age = 0;

    private array $stars = [];

    function __construct(int $radius = 1000, int $radCore = 250, float $deltaAng = 0.00019, float $innerEccentricity = 1.2, float $outerEccentricity = 1.02, int $disturbances = 1, float $dampening = 40, int $starsCount = 5000)
    {
        $this->distantRadius = $radius * 2;
        $this->galaxyRadius = $radius;
        $this->coreRadius = $radCore;
        $this->angularOffset = $deltaAng;
        $this->innerEccentricity = $innerEccentricity;
        $this->outerEccentricity = $outerEccentricity;
        $this->disturbances = $disturbances;
        $this->disturbaneDampeningFactor = $dampening;
        $this->starsCount = $starsCount;

        $cdf = new CumulativeDistributionFunction(1, 0.02, $this->galaxyRadius / 3, $this->coreRadius, 0, $this->distantRadius, 1000);

        /* Initialize the Stars */
        for ($i = 0; $i < $this->starsCount; $i++) {
            $particle = new Particle();

            $particle->m_a = $cdf->value(Math::rrand()); // Math::nrand(0, $this->sigma) * $this->galaxyRadius;
            $particle->m_b = $particle->m_a * $this->eccentricity($particle->m_a);
            $particle->angle = $particle->m_a * $this->angularOffset; // 90 - $particle->m_a * $this->angularOffset;
            $particle->theta = 360.0 * Math::rrand();
            $particle->velocity = $this->getOrbitalVelocity($particle->m_a); //0.000005;

            $particle->temperature = rand(3000, 9000);
            $particle->brightness = rand(0.05, 0.25);

            $particle->position = $this->calcPosition($particle);

            $this->stars[] = $particle;
        }
    }

    public function age(int $years = 13700000000): void
    {
        $this->age += $years;

        /* Use for to not copy the dataset every iteration */
        for ($i = count($this->stars); $i > 0; $i--) {
            /* Age the particle position */
            $this->stars[$i - 1]->position = $this->calcPosition($this->stars[$i - 1]);
        }
    }

    public function getStars(): array
    {
        return $this->stars;
    }

    private function calcPosition(Particle $particle): Position
    {
        /* Get the actual theta of the particle */
        $actualTheta = $particle->theta + $particle->velocity * $this->age;

        /* Calculate */
        $alpha = $actualTheta * self::DEG_TO_RAD; //pi() / 100.0;
        $cosAlpha = cos($alpha);
        $sinAlpha = sin($alpha);
        $cosBeta = cos(-$particle->angle);
        $sinBeta = sin(-$particle->angle);

        /* Move the stars on the ellipsis */
        $position = new Position(
            $particle->m_a * $cosAlpha * $cosBeta - $particle->m_b * $sinAlpha * $sinBeta,
            $particle->m_a * $cosAlpha * $sinBeta - $particle->m_b * $sinAlpha * $cosBeta
        );

        /* Add disturbance and dampening */
        if ($this->disturbaneDampeningFactor > 0 && $this->disturbances > 0) {
            $position->x += ($alpha / $this->disturbaneDampeningFactor) * sin($alpha * 2.0 * (float)$this->disturbances);
            $position->y += ($alpha / $this->disturbaneDampeningFactor) * cos($alpha * 2.0 * (float)$this->disturbances);
        }

        return $position;
    }

    private function eccentricity($r)
    {
        if ($r < $this->coreRadius) {
            return 1 + ($r / $this->coreRadius) * ($this->innerEccentricity - 1);
        } else if ($r > $this->coreRadius &&  $r <= $this->galaxyRadius) {
            $a = $this->galaxyRadius - $this->coreRadius;
            $b = $this->outerEccentricity - $this->innerEccentricity;
            return $this->innerEccentricity + ($r - $this->coreRadius) / $a * $b;
        } else if ($r > $this->galaxyRadius && $r < $this->distantRadius) {
            $a = $this->distantRadius - $this->galaxyRadius;
            $b = 1 - $this->outerEccentricity;
            return $this->outerEccentricity + ($r - $this->galaxyRadius) / $a * $b;
        } else {
            return 0;
        }
    }

    private function getOrbitalVelocity(float $rad): float
    {
        $velKms = 0;
        if ($this->darkMatter) {
            $velKms = Math::velocityWithDarkmatter($rad);
        } else {
            $velKms = Math::velocityWithoutDarkmatter($rad);
        }

        $u = 2.0 * pi() * $rad * self::PC_TO_KM;
        $time = $u / ($velKms * self::SEC_PER_YEAR);

        return 360.0 / $time;
    }
}