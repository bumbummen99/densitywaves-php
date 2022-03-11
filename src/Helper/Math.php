<?php

namespace SkyRaptor\DensityWaves\Helpers;

use SkyRaptor\DensityWaves\Galaxy;

class Math
{
    /**
     * Returns bool, will be true based on the chance/percentage
     *
     * @param float $chance Chance to be true on pc
     * @return boolean
     */
    static function chance(float $chance): bool
    {
        return round(mt_rand(1, (1 / $chance) * 100)) === 1;
    }

    /**
     * Gaussian normal distribution
     *
     * @param float $mean
     * @param float $sd
     * @return float
     */
    static function nrand(float $mean, float $sd): float
    {
        $x = mt_rand() / mt_getrandmax();
        $y = mt_rand() / mt_getrandmax();
        return sqrt(-2 * log($x)) * cos(2 * pi() * $y) * $sd + $mean;
    }

    /**
     * Random float between 0 and 1
     *
     * @return float
     */
    static function rrand(): float
    {
        return (float)rand()/(float)getrandmax();
    }

    public static function velocityWithDarkmatter(float $rad): float
    {
        if ($rad == 0) {
            return 0;
        }

        $MZ = 100;
        $massHalo = self::massHalo($rad);
        $massDisc = self::massDisc($rad);

        $squirt = sqrt(Galaxy::GRAVITY * ($massHalo + $massDisc + $MZ) / $rad);
        return 20000.0 * sqrt(Galaxy::GRAVITY * ($massHalo + $massDisc + $MZ) / $rad);
    }

    public static function velocityWithoutDarkmatter(float $rad): float
    {
        if ($rad == 0) {
            return 0;
        }

        $MZ = 100;

        return 20000.0 * sqrt(Galaxy::GRAVITY * (self::massDisc($rad) + $MZ) / $rad);
    }

    private static function massHalo(float $rad): float
    {
        $rho_h0 = 0.15;
        $rC = 2500;
        
        return $rho_h0 * 1 / (1 + pow($rad / $rC, 2)) * (4 * pi() * pow($rad, 3) / 3);
    }

    private static function massDisc(float $rad): float
    {
        $d = 2000;
        $rho_so = 1;
        $rH = 2000;

        return $rho_so * exp(-$rad / $rH) * ($rad * $rad) * pi() * $d;
    }
}