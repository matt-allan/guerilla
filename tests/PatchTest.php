<?php

declare(strict_types=1);

namespace MattAllan\Guerilla;

use PHPUnit\Framework\TestCase;

class PatchTest extends TestCase
{
    public function tearDown(): void
    {
        foreach (Patch::all() as $patch) {
            $patch->clear();
        }
    }

    function test_can_patch_using_class_instances()
    {
        $now = time() - 10;

        patch('time')
            ->for($this)
            ->using(fn () => $now)
            ->make();

        $this->assertEquals($now, time());
        $this->assertEquals($now, time());
    }

    function test_can_patch_using_class_names()
    {
        $now = time() - 10;

        patch('time')
            ->for(self::class)
            ->using(fn () => $now)
            ->make();

        $this->assertEquals($now, time());
        $this->assertEquals($now, time());
    }

    function test_can_patch_using_namespaces()
    {
        $now = time() - 10;

        patch('time')
            ->within(__NAMESPACE__)
            ->using(fn () => $now)
            ->make();

        $this->assertEquals($now, time());
        $this->assertEquals($now, time());
    }

    function test_can_patch_functions_with_arguments()
    {
        patch('rand')
            ->for($this)
            ->using(fn ($min, $max) => $max)
            ->make();

        $this->assertEquals(10, rand(0, 10));
        $this->assertEquals(9, rand(0, 9));
    }

    function test_can_patch_functions_with_pass_by_reference_arguments()
    {
        patch('sort')
            ->for($this)
            ->using(function (array &$arr) {
                \sort($arr);
                $arr = array_reverse($arr);
            })
            ->make();

        $arr = ['x', 'z', 'y'];
        sort($arr);

        $this->assertEquals(['z', 'y', 'x'], $arr);
    }

    function test_can_patch_functions_with_typed_variadic_arguments()
    {
        patch('something')
            ->for($this)
            ->using(function (string ...$foos) {
                return implode(', ', $foos);
            })
            ->make();

        $this->assertequals('a, b, c', something('a', 'b', 'c'));
    }

    function test_can_patch_functions_with_default_values()
    {
        patch('something')
            ->for($this)
            ->using(function (string $foo = 'first', int $bar = 2, array $baz = []) {
                return [$foo, $bar, $baz];
            })
            ->make();

        $this->assertequals(['first', 2, []], something());
    }

    function test_can_clear_patches()
    {
        $now = time() - 10;

        $patch = patch('time')
            ->for($this)
            ->using(fn () => $now)
            ->make();

        $this->assertEquals($now, time());

        $patch->clear();

        $this->assertNotEquals($now, time());

        $this->assertEmpty(Patch::all());
    }

    function test_can_define_the_same_patch_twice()
    {
        $this->expectNotToPerformAssertions();

        $now = time() - 10;

        patch('time')
            ->for($this)
            ->using(fn () => $now)
            ->make();

        patch('time')
            ->for($this)
            ->using(fn () => $now)
            ->make();
    }

    function test_redefining_the_same_patch_overwrites_it()
    {
        $thePast = time() - 10;

        patch('time')
            ->for($this)
            ->using(fn () => $thePast)
            ->make();

        $this->assertEquals($thePast, time());

        $theFuture = time() + 10;

        patch('time')
            ->for($this)
            ->using(fn () => $theFuture)
            ->make();

        $this->assertEquals($theFuture, time());
    }

    function test_patch_is_registered_lazily()
    {
        $now = time();

        $patch = patch('time');

        $this->assertEmpty(Patch::all());

        $patch->for($this)->using(fn () => 1234)->make();

        $this->assertCount(1, Patch::all());

        $patch->clear();

        $this->assertEmpty(Patch::all());
    }
}
