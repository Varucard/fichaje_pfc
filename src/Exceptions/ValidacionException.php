<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

/**
 * Regla de negocio no cumplida. El mensaje se muestra tal cual al usuario.
 */
final class ValidacionException extends DomainException
{
}
