<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class HttpException extends RuntimeException
{
  public function __construct(public readonly int $status, string $mensaje)
  {
    parent::__construct($mensaje, $status);
  }
}
