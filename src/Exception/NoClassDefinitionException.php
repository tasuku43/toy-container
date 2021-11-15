<?php
declare(strict_types = 1);

namespace ToyContainer\Exception;

use Psr\Container\ContainerExceptionInterface;

class NoClassDefinitionException extends \Exception implements ContainerExceptionInterface
{
}
