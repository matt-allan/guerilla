# Guerilla

A small utility to guerilla patch global PHP functions.

This library patches global functions using PHP's [namespace fallback policy](https://www.php.net/manual/en/language.namespaces.fallback.php) and [`eval`](https://www.php.net/manual/en/function.eval.php).

## Installation

```
composer require matt-allan/guerilla
```

## Usage

To patch a function, call the `patch` helper function with the name of the function you would like to patch.

```php
<?php

use function MattAllan\Guerilla\patch;

$patch = patch('rand');
```

You can then specify the namespaces you would like to patch by calling the `within` method.

```php
<?php

$patch->within('App');
```

Typically you want to patch a given class. You may pass a class name or instance to the `for` method to patch that classes' namespace instead.

```php
<?php

$patch->for(App\Lottery::class);
```

To specify the closure that should be used instead, call the `using` method.

```php
<?php

$patch->using(fn () => 123);
```

Finally, call the `make` method to create the patch.

```php
<?php

$patch->make();
```

A complete example looks like this:

```php
<?php

namespace App;

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

var_dump((new Lottery())->getNumbers()); // returns `123`
```

## Clearing patches

Once the patch is created it will continue to intercept calls until the `clear` method is called.

```php
<?php

$patch->clear();
```

To simplify cleaning up after tests, you can obtain a reference to all active patches using the `Patch::all` method. You can then iterate over the array and clear the patches. A good place to do this is in PHPUnit's `teardown` method.

```php
<?php

use MattAllan\Guerilla\Patch;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function tearDown(): void
    {
        foreach (Patch::all() as $patch) {
            $patch->clear();
        }
    }
}
```

## Caveats

* Patching doesn't work in the global namespace.
* Patching only works when the function call is unqualified, i.e. `\time()` cannot be patched.
* The patch has to be defined before the first call to the function in the namespace you are patching ([example](https://3v4l.org/5BpT9) & [PHP bug](https://bugs.php.net/bug.php?id=64346)).  If you are using PHPUnit `@runInSeparateProcess` should do the trick.
* When a patch is cleared the function still exists in memory until the process ends.  It just forwards to the global function instead.
* **Patching uses `eval`**. Never pass user input to any of the patch methods.

## Alternatives

[php-mock](https://github.com/php-mock/php-mock) is a mature alternative that uses the same technique.
