<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Exportación a CSV pensada para abrir con Excel en español: separador ";",
 * decimales con coma y BOM UTF-8 para que respete tildes y ñ.
 */
final class Csv
{
  /**
   * Convierte un valor para una celda: los números con coma decimal y los textos
   * neutralizados contra inyección de fórmulas (=, +, -, @ al inicio).
   */
  public static function celda(mixed $valor): string
  {
    if ($valor === null) {
      return '';
    }
    if (is_float($valor)) {
      return number_format($valor, 2, ',', '');
    }
    if (is_int($valor)) {
      return (string) $valor;
    }
    $texto = (string) $valor;
    if ($texto !== '' && in_array($texto[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
      $texto = "'" . $texto;
    }
    return $texto;
  }

  /** Genera el contenido CSV completo. */
  public static function generar(array $encabezados, iterable $filas): string
  {
    $salida = fopen('php://temp', 'r+');
    fwrite($salida, "\xEF\xBB\xBF");
    fputcsv($salida, $encabezados, ';', '"', '');
    foreach ($filas as $fila) {
      fputcsv($salida, array_map([self::class, 'celda'], $fila), ';', '"', '');
    }
    rewind($salida);
    $contenido = (string) stream_get_contents($salida);
    fclose($salida);
    return $contenido;
  }

  public static function enviar(string $archivo, array $encabezados, iterable $filas): void
  {
    $contenido = self::generar($encabezados, $filas);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $archivo . '"');
    header('Content-Length: ' . strlen($contenido));
    echo $contenido;
  }
}
