# DensityWaves PHP

From Wikipedia:
> "Density wave theory or the Lin–Shu density wave theory **is a theory proposed by C.C. Lin and Frank Shu in the mid-1960s to explain the spiral arm structure of spiral galaxies.**"

This is a re-implementation of the [beltoforion article & project](https://github.com/beltoforion/Galaxy-Renderer).

## Installation

You can install this package using composer:
```
composer require skyraptor/densitywaves-php
```

## Usage

Simply inizalize the `Galaxy` with the appropiate parameters. You can then age it as you desire and access the stars with `getStars()`.

Example:
```php
use DensityWaves\Galaxy;
  
...
  
$radius = 1000;
$galaxy = new Galaxy($radius, round($radius * 0.25), 0.00015, 1.2, 1.02, 1, 40);
$galaxy->age();

```

You **will** have to play with the parameters to get good results. Check the browser version for an idea of how it does work:
[Procedural generation of spiral Galaxies
](https://beltoforion.de/en/spiral_galaxy_renderer/spiral-galaxy-renderer.html)