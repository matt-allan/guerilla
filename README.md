# Guerilla

A small utility to guerilla patch global PHP functions.

## Why

If you need to test a function that always returns a different value like `time`, this library can help.

## How

Making a patch defines a namespaced function that gets used instead of the core function.  If your code looks like this:

```php
<?php

namespace App;

$time = time();
```

...we essentially make a file like this and include it before your code runs:

```php
<?php

namespace App;

function time()
{
    // some other code...
}
```

Since we defined a function in the namespace, PHP doesn't fall back to the global function and your patch runs instead.  [Check out the PHP manual for more info](http://php.net/manual/en/language.namespaces.fallback.php).

## Installation

```
composer require yuloh/guerilla
```

## Usage

To define a patch, call `Patch::make`.  You need to specify the namespace to patch the function within, the name of the function, and a callback.

```php
<?php

namespace App;

use Yuloh\Guerilla\Patch;

Patch::make(__NAMESPACE__, 'time', function () {
    return 555555;
});

echo time(); // 555555
```

You can clear a specific patch by specifying the function name.  This will clear the patched function from all namespaces.

```php
Patch::clear('time');
```

You can also clear all patches.  It's a good idea to clear your patches as soon as you are done testing.  If you are using PHPUnit, clearing the patches in the `tearDown` method is a good idea.

```php
Patch::clear();
```

## Caveats

* Patching doesn't work in the global namespace.
* Patching only works when the function call is unqualified, i.e. `\time()` cannot be patched.
* The patch has to be defined before the first call to the function in the namespace you are patching ([example](https://3v4l.org/5BpT9) & [PHP bug](https://bugs.php.net/bug.php?id=64346)).  If you are using PHPUnit `@runInSeparateProcess` should do the trick.
* When a patch is cleared the function still exists in memory until the process ends.  It just forwards to the global function instead.

## Alternatives

[php-mock](https://github.com/php-mock/php-mock) is a mature alternative that uses the same technique.
