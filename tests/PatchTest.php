<?php

namespace Yuloh\Guerilla;

class PatchTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Patch::clear();
    }

    function test_it_patches_global_functions()
    {
        Patch::make(__NAMESPACE__, 'time', function () {
            return 555;
        });
        $this->assertSame(555, time());
        $this->assertSame(555, time());
    }

    function test_it_clears_all_patches()
    {
        Patch::make(__NAMESPACE__, 'time', function () {
            return 555;
        });
        Patch::make(__NAMESPACE__, 'function_exists', function () {
            return false;
        });
        Patch::clear();
        $this->assertNotSame(555, time());
        $this->assertTrue(function_exists('strlen'));
    }

    function test_it_clears_specific_patches()
    {
        Patch::make(__NAMESPACE__, 'time', function () {
            return 555;
        });
        Patch::make(__NAMESPACE__, 'function_exists', function () {
            return false;
        });
        Patch::clear('time');
        $this->assertNotSame(555, time());
        $this->assertFalse(function_exists('strlen'));
    }
}
