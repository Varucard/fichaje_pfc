<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Respuesta que recibe el lector Arduino al pasar un llavero.
 * Los valores son los que interpreta firmware/pfc/pfc.ino: no cambiarlos sin actualizar el firmware.
 */
enum EstadoLectura: string
{
  case Activo = 'activo';
  case Moroso = 'moroso';
  case Inactivo = 'inactivo';
  case SinClase = 'sinclase';
  case Admin = 'admin';
  case Desconocido = 'desconocido';
}
