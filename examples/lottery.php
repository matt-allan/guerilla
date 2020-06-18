<?php

namespace App;

require __DIR__.'/../vendor/autoload.php';

use function MattAllan\Guerilla\patch;

class Lottery
{
    public function getNumbers(): int
    {
        return rand(100, 999);
    }
}

$patch = patch('rand')
    ->for(Lottery::class)
    ->using(fn (int $min, int $max): int => 123)
    ->make();


var_dump((new Lottery())->getNumbers());

var_dump((new Lottery())->getNumbers());

$patch->clear();

var_dump((new Lottery())->getNumbers());
