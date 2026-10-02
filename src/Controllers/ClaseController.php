<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Exceptions\ValidacionException;
use App\Services\ClaseService;
use App\Services\UsuarioService;

final class ClaseController extends Controller
{
  public function __construct(
    private readonly ClaseService $clases,
    private readonly UsuarioService $usuarios,
  ) {
  }

  public function index(Request $request): void
  {
    $this->render('clases/lista', [
      'titulo' => 'Lista de Clases',
      'clases' => $this->clases->listar(),
    ]);
  }

  public function buscar(Request $request): void
  {
    $termino = (string) $request->query('q', '');

    $this->render('clases/lista', [
      'titulo' => 'Búsqueda de Clases',
      'termino' => $termino,
      'clases' => $this->clases->buscar($termino),
    ]);
  }

  public function crear(Request $request): void
  {
    $this->render('clases/crear', [
      'titulo' => 'Registrar nueva clase',
      'profesores' => $this->usuarios->profesoresActivos(),
    ]);
  }

  public function guardar(Request $request): void
  {
    $nombre = (string) $request->input('nombre_clase', '');
    $precio = (string) $request->input('precio', '');

    try {
      $resultado = $this->clases->crear($nombre, $precio, $request->inputArray('profesores'));
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/clases/nueva', ['nombre_clase' => $nombre, 'precio' => $precio]);
    }

    $mensaje = $resultado['omitidos']
      ? 'Clase creada, pero no se asignaron: ' . implode(', ', $resultado['omitidos']) . '.'
      : 'Clase creada exitosamente.';
    $this->exito($mensaje, '/clases/' . $resultado['id']);
  }

  public function mostrar(Request $request, string $id): void
  {
    try {
      $datos = $this->clases->detalle((int) $id);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/clases');
    }

    $this->render('clases/detalle', $datos + ['titulo' => 'Detalles de la Clase']);
  }

  public function actualizar(Request $request, string $id): void
  {
    try {
      $this->clases->actualizar((int) $id, (string) $request->input('name_class', ''), (string) $request->input('precio', ''));
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/clases/' . $id);
    }

    $this->exito('Clase actualizada exitosamente.', '/clases/' . $id);
  }

  public function eliminar(Request $request, string $id): void
  {
    try {
      $this->clases->eliminar((int) $id, $request->tiene('incluir_matriculaciones'));
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/clases/' . $id);
    }

    $this->exito('Clase eliminada exitosamente.', '/clases');
  }

  public function agregarMiembro(Request $request, string $id): void
  {
    $volverA = $request->input('volver_a') === 'usuario'
      ? '/usuarios/' . $request->input('dni', '')
      : '/clases/' . $id;

    try {
      $usuario = $this->clases->agregarMiembro((int) $id, (string) $request->input('dni', ''));
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), $volverA);
    }

    $this->exito("{$usuario['user_name']} fue agregado/a a la clase.", $volverA);
  }

  /** Matricula al usuario en la clase elegida desde su ficha. */
  public function matricular(Request $request, string $dni): void
  {
    try {
      $this->clases->agregarMiembro((int) $request->input('id_clase', '0'), $dni);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/usuarios/' . $dni);
    }

    $this->exito('Matriculación registrada.', '/usuarios/' . $dni);
  }

  public function quitarMiembro(Request $request, string $id, string $idUsuario): void
  {
    $volverA = $request->input('volver_a') === 'usuario'
      ? '/usuarios/' . $request->input('dni', '')
      : '/clases/' . $id;

    try {
      $this->clases->quitarMiembro((int) $id, (int) $idUsuario);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), $volverA);
    }

    $this->exito('Usuario quitado de la clase.', $volverA);
  }
}
