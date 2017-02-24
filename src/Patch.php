<?php

namespace Yuloh\Guerilla;

class Patch
{
    /**
     * @var []
     */
    static $patches = [];

    /**
     * Make a new patch.
     *
     * @param string   $namespace
     * @param string   $functionName
     * @param callable $callback
     */
    public static function make($namespace, $functionName, callable $callback)
    {
        $fqfn                   = static::fqfn($namespace, $functionName);
        static::$patches[$fqfn] = $callback;
        if (!function_exists($fqfn)) {
            eval(static::template($namespace, $functionName));
        }
    }

    /**
     * Clear a patch.
     *
     * @param string|null $functionName
     */
    public static function clear($functionName = null)
    {
        if (!$functionName) {
            static::$patches = [];
            return;
        }

        static::$patches = array_filter(static::$patches, function ($key) use ($functionName) {
            return $functionName !== substr($key, -strlen($functionName));
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @internal
     *
     * @param string $namespace
     * @param string $functionName
     * @param array $args
     *
     * @return mixed
     */
    public static function run($namespace, $functionName, array $args)
    {
        $fqfn = static::fqfn($namespace, $functionName);
        if (!isset(static::$patches[$fqfn])) {
            return call_user_func_array($functionName, $args);
        }

        return call_user_func_array(static::$patches[$fqfn], $args);
    }

    /**
     * @param string $namespace
     * @param string $functionName
     *
     * @return string
     */
    private static function fqfn($namespace, $functionName)
    {
        return rtrim($namespace, '\\') . '\\' . $functionName;
    }

    /**
     * @param string $namespace
     * @param string $functionName
     *
     * @return string
     */
    private static function template($namespace, $functionName)
    {
       return strtr(file_get_contents(__DIR__ . '/patch.stub'), [
           '{namespace}'    => $namespace,
           '{functionName}' => $functionName,
       ]);
    }
}
