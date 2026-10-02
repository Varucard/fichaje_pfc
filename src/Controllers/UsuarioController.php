<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Domain\TipoUsuario;
use App\Exceptions\ValidacionException;
use App\Services\UsuarioService;

final class UsuarioController extends Controller
{
  private const CAMPOS = ['rfid', 'dni', 'name', 'surname', 'birth_day', 'email', 'phone'];

  public function __construct(private readonly UsuarioService $usuarios)
  {
  }

  public function buscar(Request $request): void
  {
    $termino = (string) $request->query('q', '');

    $this->render('usuarios/lista', [
      'titulo' => 'Búsqueda de Usuarios',
      'termino' => $termino,
      'usuarios' => $this->usuarios->buscar($termino),
      'textoAgregar' => 'Agregar Cliente/ Profesor',
    ]);
  }

  public function clientes(Request $request): void
  {
    $this->render('usuarios/lista', [
      'titulo' => 'Lista de Clientes',
      'usuarios' => $this->usuarios->listar(TipoUsuario::Alumno),
      'textoAgregar' => 'Agregar Cliente',
      'exportar' => 'clientes',
    ]);
  }

  public function profesores(Request $request): void
  {
    $this->render('usuarios/lista', [
      'titulo' => 'Lista de Profesores',
      'usuarios' => $this->usuarios->listar(TipoUsuario::Profesor),
      'textoAgregar' => 'Agregar Profesor',
      'exportar' => 'profesores',
      'ocultarLlavero' => true,
    ]);
  }

  public function crear(Request $request): void
  {
    $this->render('usuarios/crear', [
      'titulo' => 'Registrar nuevo Cliente/ Profesor',
      'clases' => $this->usuarios->clases(),
    ]);
  }

  public function guardar(Request $request): void
  {
    $entrada = $this->entrada($request);
    $opcion = $request->input('opcion_alta', '');
    $esProfesor = $opcion === 'profesor';
    $conPago = $opcion === 'pago';

    try {
      $usuario = $this->usuarios->crear($entrada, $esProfesor, $conPago, $request->inputArray('clases'));
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/usuarios/nuevo', $entrada + ['opcion_alta' => $opcion, 'clases' => $request->inputArray('clases')]);
    }

    $mensaje = match (true) {
      $esProfesor => 'Profesor cargado exitosamente.',
      $conPago => 'Cliente cargado exitosamente con pago.',
      default => 'Cliente cargado exitosamente sin pago.',
    };

    $this->exito($mensaje, '/usuarios/' . $usuario['dni']);
  }

  public function mostrar(Request $request, string $dni): void
  {
    try {
      $datos = $this->usuarios->detalle($dni);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/usuarios/buscar');
    }

    $this->render('usuarios/detalle', $datos + [
      'titulo' => 'Detalle del ' . $datos['tipo']->etiqueta(),
    ]);
  }

  public function actualizar(Request $request, string $id): void
  {
    $entrada = $this->entrada($request);

    try {
      $usuario = $this->usuarios->actualizar((int) $id, $entrada, $request->tiene('cambiar_tipo'));
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/usuarios/' . $request->input('dni_original', ''), $entrada);
    }

    $this->exito('Usuario actualizado exitosamente.', '/usuarios/' . $usuario['dni']);
  }

  public function desactivar(Request $request, string $dni): void
  {
    try {
      $this->usuarios->desactivar($dni);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/usuarios/' . $dni);
    }

    $this->exito('Usuario desactivado exitosamente.', '/usuarios/' . $dni);
  }

  public function reactivar(Request $request, string $dni): void
  {
    try {
      $resultado = $this->usuarios->reactivar($dni);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/usuarios/' . $dni);
    }

    $mensaje = 'Usuario reactivado exitosamente.';
    if ($resultado['llavero_quitado']) {
      $mensaje .= ' Su llavero estaba asignado a otra persona, por lo que quedó SIN LLAVERO.';
    }
    $this->exito($mensaje, '/usuarios/' . $dni);
  }

  private function entrada(Request $request): array
  {
    $entrada = [];
    foreach (self::CAMPOS as $campo) {
      $entrada[$campo] = (string) $request->input($campo, '');
    }
    return $entrada;
  }
}
