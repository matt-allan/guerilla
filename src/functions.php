<?php

declare(strict_types=1);

namespace MattAllan\Guerilla;

function patch(string $name): Patch
{
    return Patch::get($name);
}
