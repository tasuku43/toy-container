<?php
declare(strict_types = 1);

namespace ToyContainer;

use ToyContainer\Exception\NoClassDefinitionException;
use ToyContainer\Exception\NotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionException;

class Container implements ContainerInterface
{
    /**
     * @var Definition[]
     */
    private array $definitions = [];

    /**
     * @param string $id
     *
     * @return object
     * @throws NotFoundException|NoClassDefinitionException
     */
    final public function get(string $id): object
    {
        if (!$this->has($id)) {
            throw new NotFoundException("Entry '$id' not found.");
        }

        try {
            $definition      = $this->definitions[$id];
            $reflectionClass = new \ReflectionClass($definition->concrete());

            if ($reflectionClass->getConstructor() === null) {
                return $reflectionClass->newInstance();
            }

            $args = array_map(function (mixed $argument) {
                return $this->isClassOrInterface($argument) ? $this->get($argument) : $argument;
            }, $definition->arguments());

            return $reflectionClass->newInstanceArgs($args);
        } catch (ReflectionException) {
            throw new NoClassDefinitionException("'$id' is not defined.");
        }
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    final public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions);
    }

    /**
     * @param string      $id
     * @param string|null $concrete
     *
     * @return Definition
     */
    final public function add(string $id, string $concrete = null): Definition
    {
        $definition = new Definition($id, $concrete ?? $id);

        $this->definitions[$definition->id()] = $definition;

        return $definition;
    }

    private function isClassOrInterface(mixed $argument): bool
    {
        if (!is_string($argument)) {
            return false;
        }

        try {
            new \ReflectionClass($argument);
        } catch (ReflectionException) {
            return false;
        }

        return true;
    }
}
