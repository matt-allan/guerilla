<?php

declare(strict_types=1);

namespace MattAllan\Guerilla;

final class Patch
{
    private static array $registry = [];

    private array $namespaces = [];

    private string $name;

    private ?\Closure $callback = null;

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function get(string $name): self
    {
        return static::$registry[$name] ?? new self($name);
    }

    /**
     * @return self[]
     */
    public static function all(): array
    {
        return array_values(static::$registry);
    }

    public function for(...$classes): self
    {
        return $this->within(
            ...array_map(
                fn ($className) => (new \ReflectionClass($className))
                    ->getNamespaceName(),
                $classes
            )
        );
    }

    public function within(string ...$namespaces): self
    {
        $this->namespaces = $namespaces;

        return $this;
    }

    public function make(): self
    {
        if (empty($this->namespaces)) {
            throw new \LogicException(
                'A namespace must be specified to create a patch.'
            );
        }

        if (!$this->callback) {
            throw new \LogicException(
                'A callback must be specified to create a patch.'
            );
        }

        static::$registry[$this->name] = $this;

        $template = static::template();

        foreach ($this->namespaces as $namespace) {
            if (!\function_exists("{$namespace}\\{$this->name}")) {
                eval("namespace {$namespace} { {$template} }");
            }
        }

        return $this;
    }

    public function using(\Closure $callback): self
    {
       $this->callback = $callback;

       return $this;
    }

    public function clear(): void
    {
        unset(static::$registry[$this->name]);
    }

    public function __invoke(&...$args)
    {
        return ($this->callback ?? $this->name)(...$args);
    }

    public function name(): string
    {
        return $this->name;
    }

    private function template(): string
    {
        $className = self::class;

        $parameters = (new \ReflectionFunction($this->callback))
            ->getParameters();

        $signature = implode(', ', array_map(
            fn (\ReflectionParameter $p) =>
                ($p->hasType() && $p->getType()->allowsNull() ? '?' : '').
                ($p->hasType() ? $p->getType()->getName().' ' : '').
                ($p->isPassedByReference() ? '&' : '').
                ($p->isVariadic() ? '...' : '').
                '$'.
                $p->name.
                ($p->isDefaultValueAvailable() ? ' = '.var_export($p->getDefaultValue(), true) : ''),
            $parameters
        ));

        $arguments = implode(', ', array_map(
            fn (\ReflectionParameter $p) =>
                ($p->isVariadic() ? '...' : '').
                '$'.
                $p->name,
            $parameters
        ));

        return <<<PHP
function $this->name($signature)
{
    return \\$className::get('$this->name')($arguments);
}
PHP;
    }
}
