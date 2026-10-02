<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Llavero;
use App\Domain\TipoUsuario;
use App\Exceptions\ValidacionException;
use App\Repositories\ClaseRepository;
use App\Repositories\FichajeRepository;
use App\Repositories\LiquidacionRepository;
use App\Repositories\MatriculaRepository;
use App\Repositories\PagoRepository;
use App\Repositories\UsuarioRepository;
use DateTimeImmutable;

/**
 * Alta, edición, baja y consultas de clientes (alumnos) y profesores.
 */
final class UsuarioService
{
  public function __construct(
    private readonly UsuarioRepository $usuarios,
    private readonly MatriculaRepository $matriculas,
    private readonly PagoRepository $pagos,
    private readonly ClaseRepository $clases,
    private readonly PagoService $pagoService,
    private readonly AuditoriaService $auditoria,
    private readonly DeudaService $deudas,
    private readonly FichajeRepository $fichajes,
    private readonly LiquidacionRepository $liquidaciones,
    private readonly AvisosService $avisos,
    private readonly EmailService $emails,
  ) {
  }

  /** Búsqueda libre: si es un DNI busca exacto, si no por nombre/apellido. */
  public function buscar(string $termino): array
  {
    $termino = trim($termino);
    if ($termino === '') {
      return [];
    }
    if (ctype_digit($termino)) {
      $usuario = $this->usuarios->buscarPorDni($termino);
      return $usuario ? [$usuario] : [];
    }
    return $this->usuarios->buscarPorNombre($termino);
  }

  public function listar(TipoUsuario $tipo): array
  {
    return $this->usuarios->listarPorTipo($tipo);
  }

  public function clases(): array
  {
    return $this->clases->listar();
  }

  public function profesoresActivos(): array
  {
    return $this->usuarios->listarPorTipo(TipoUsuario::Profesor, soloActivos: true);
  }

  public function cumpleanerosDeHoy(): array
  {
    return $this->usuarios->cumpleanerosDel(new DateTimeImmutable('today'));
  }

  /** Datos para la ficha de un usuario: usuario, clases y últimos pagos. */
  public function detalle(string $dni): array
  {
    $usuario = $this->usuarios->buscarPorDni($dni)
      ?? throw new ValidacionException('No se encontró el usuario.');

    $idUsuario = (int) $usuario['id_user'];
    $tipo = TipoUsuario::deUsuario($usuario);
    $clases = $tipo === TipoUsuario::Profesor
      ? $this->matriculas->clasesDeProfesor($idUsuario)
      : $this->matriculas->clasesDeAlumno($idUsuario);

    $idsClases = array_column($clases, 'id_class');
    $clasesDisponibles = array_values(array_filter(
      $this->clases->listar(),
      fn (array $clase) => !in_array($clase['id_class'], $idsClases, false)
    ));

    return [
      'usuario' => $usuario,
      'tipo' => $tipo,
      'clases' => $clases,
      'clasesDisponibles' => $clasesDisponibles,
      'pagos' => $tipo === TipoUsuario::Alumno ? $this->pagos->ultimosDeUsuario($idUsuario) : [],
      'deuda' => $tipo === TipoUsuario::Alumno ? $this->deudas->deudaDe($idUsuario) : null,
      'fichajes' => $tipo === TipoUsuario::Alumno ? $this->fichajes->deUsuario($idUsuario) : [],
      'liquidaciones' => $tipo === TipoUsuario::Profesor ? $this->liquidaciones->deProfesor($idUsuario) : [],
      'emails' => $this->emails->deUsuario($idUsuario),
      'historial' => $this->auditoria->historialDe('usuario', $usuario['dni']),
    ];
  }

  /**
   * Da de alta un cliente o profesor. Opcionalmente registra el primer pago.
   */
  public function crear(array $entrada, bool $esProfesor, bool $conPago, array $idsClases = []): array
  {
    $idsClases = array_values(array_unique(array_filter(array_map('intval', $idsClases))));
    if ($conPago && !$esProfesor && !$idsClases) {
      throw new ValidacionException('Para registrar el pago elegí al menos una clase: la cuota es la suma de sus clases.');
    }
    $datos = $this->normalizar($entrada, $esProfesor ? TipoUsuario::Profesor : TipoUsuario::Alumno);

    $existente = $this->usuarios->buscarPorDni($datos['dni']);
    if ($existente) {
      throw new ValidacionException((int) $existente['asset']
        ? 'El cliente/profesor ya se encuentra registrado.'
        : 'El cliente/profesor ya se encuentra registrado, pero está inactivo.');
    }
    $this->validarLlaveroLibre($datos['rfid']);

    return $this->usuarios->transaccion(function () use ($datos, $esProfesor, $conPago, $idsClases): array {
      $id = $this->usuarios->crear($datos);
      $this->auditoria->registrar(
        'usuario.alta',
        sprintf('Alta de %s %s %s (DNI %s)', mb_strtolower($datos['tipo']->etiqueta()), $datos['nombre'], $datos['apellido'], $datos['dni']),
        'usuario',
        $datos['dni'],
      );
      foreach ($idsClases as $idClase) {
        $clase = $this->clases->buscarPorId($idClase)
          ?? throw new ValidacionException('Una de las clases elegidas no existe.');
        $esProfesor
          ? $this->matriculas->asignarProfesor($id, $idClase)
          : $this->matriculas->inscribirAlumno($id, $idClase);
        $this->auditoria->registrar('matricula.alta', "{$datos['nombre']} {$datos['apellido']} agregado/a a {$clase['name_class']} en el alta", 'usuario', $datos['dni'], ['id_clase' => $idClase]);
      }
      $usuario = $this->usuarios->buscarPorId($id);
      $this->avisos->bienvenida($usuario);
      if ($conPago && !$esProfesor) {
        $this->pagoService->registrar($id);
      }
      return $usuario;
    });
  }

  /**
   * Actualiza los datos de un usuario. $cambiarTipo alterna entre cliente y profesor.
   */
  public function actualizar(int $id, array $entrada, bool $cambiarTipo): array
  {
    $usuario = $this->usuarios->buscarPorId($id)
      ?? throw new ValidacionException('El usuario no existe.');

    $tipo = TipoUsuario::deUsuario($usuario);
    if ($cambiarTipo) {
      if ($this->matriculas->tieneMatriculaciones($id)) {
        throw new ValidacionException('El usuario tiene matriculaciones activas. Quitelo de sus clases antes de cambiar su tipo.');
      }
      $tipo = match ($tipo) {
        TipoUsuario::Profesor => TipoUsuario::Alumno,
        TipoUsuario::Alumno => TipoUsuario::Profesor,
        TipoUsuario::Administrador => throw new ValidacionException('No se puede cambiar el tipo de un administrador.'),
      };
    }

    $datos = $this->normalizar($entrada, $tipo);

    $otro = $this->usuarios->buscarPorDni($datos['dni']);
    if ($otro && (int) $otro['id_user'] !== $id) {
      throw new ValidacionException('El N° de documento ya está registrado en otro usuario.');
    }
    $this->validarLlaveroLibre($datos['rfid'], $id);

    $this->usuarios->actualizar($id, $datos);
    $actualizado = $this->usuarios->buscarPorId($id);

    $cambios = AuditoriaService::cambios($usuario, $actualizado, [
      'rfid', 'dni', 'user_name', 'user_surname', 'birth_day', 'email', 'phone_number', 'type_user',
    ]);
    if ($cambios) {
      $this->auditoria->registrar(
        'usuario.actualizacion',
        sprintf('Actualización de %s %s (DNI %s): %s', $actualizado['user_name'], $actualizado['user_surname'], $actualizado['dni'], implode(', ', array_keys($cambios))),
        'usuario',
        $actualizado['dni'],
        $cambios,
      );
    }
    return $actualizado;
  }

  public function desactivar(string $dni): array
  {
    $usuario = $this->usuarios->buscarPorDni($dni)
      ?? throw new ValidacionException('El usuario no existe.');

    if (TipoUsuario::deUsuario($usuario) === TipoUsuario::Alumno) {
      $deuda = $this->deudas->deudaDe((int) $usuario['id_user']);
      if ($deuda['total'] > 0) {
        throw new ValidacionException('El alumno tiene una deuda de ' . dinero($deuda['total']) . '. Debe saldarla antes de desactivarlo.');
      }
    }
    if ($this->matriculas->tieneMatriculaciones((int) $usuario['id_user'])) {
      throw new ValidacionException('El usuario tiene matriculaciones activas. Quitelo de sus clases antes de desactivarlo.');
    }

    $this->usuarios->cambiarEstado((int) $usuario['id_user'], false);
    $this->auditoria->registrar('usuario.desactivacion', "Desactivación de {$usuario['user_name']} {$usuario['user_surname']} (DNI {$dni})", 'usuario', $dni);
    return $usuario;
  }

  /**
   * Reactiva un usuario. Si mientras estuvo inactivo su llavero se asignó a otra
   * persona activa, se le quita el llavero para no duplicarlo.
   */
  public function reactivar(string $dni): array
  {
    $usuario = $this->usuarios->buscarPorDni($dni)
      ?? throw new ValidacionException('El usuario no existe.');

    $id = (int) $usuario['id_user'];
    $llaveroQuitado = false;

    if (Llavero::asignado($usuario['rfid'])) {
      $duenio = $this->usuarios->buscarActivoPorRfid($usuario['rfid']);
      if ($duenio && (int) $duenio['id_user'] !== $id) {
        $this->usuarios->asignarLlavero($id, Llavero::SIN_LLAVERO);
        $llaveroQuitado = true;
      }
    }

    $this->usuarios->cambiarEstado($id, true);
    $this->auditoria->registrar(
      'usuario.reactivacion',
      "Reactivación de {$usuario['user_name']} {$usuario['user_surname']} (DNI {$dni})" . ($llaveroQuitado ? ' — se le quitó el llavero por estar asignado a otra persona' : ''),
      'usuario',
      $dni,
    );
    return ['usuario' => $usuario, 'llavero_quitado' => $llaveroQuitado];
  }

  private function validarLlaveroLibre(string $rfid, ?int $idPropio = null): void
  {
    if (!Llavero::asignado($rfid)) {
      return;
    }
    $duenio = $this->usuarios->buscarActivoPorRfid($rfid);
    if ($duenio && (int) $duenio['id_user'] !== $idPropio) {
      throw new ValidacionException(sprintf(
        'El llavero ya está asignado a %s %s (DNI %s).',
        $duenio['user_name'],
        $duenio['user_surname'] ?? '',
        $duenio['dni']
      ));
    }
  }

  /** Valida y normaliza los datos de un formulario de usuario. */
  private function normalizar(array $entrada, TipoUsuario $tipo): array
  {
    $dni = preg_replace('/\D/', '', (string) ($entrada['dni'] ?? ''));
    $nombre = self::capitalizar($entrada['name'] ?? '');

    if ($dni === '' || $nombre === '') {
      throw new ValidacionException('El N° de documento y el nombre son obligatorios.');
    }
    if (strlen($dni) < 7 || strlen($dni) > 8) {
      throw new ValidacionException('El N° de documento debe tener 7 u 8 dígitos.');
    }

    $email = trim((string) ($entrada['email'] ?? ''));
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new ValidacionException('El email no es válido.');
    }

    $nacimiento = trim((string) ($entrada['birth_day'] ?? ''));
    if ($nacimiento !== '' && DateTimeImmutable::createFromFormat('!Y-m-d', $nacimiento) === false) {
      throw new ValidacionException('La fecha de nacimiento no es válida.');
    }

    return [
      'rfid' => Llavero::normalizar($entrada['rfid'] ?? ''),
      'dni' => $dni,
      'nombre' => $nombre,
      'apellido' => self::capitalizar($entrada['surname'] ?? ''),
      'nacimiento' => $nacimiento,
      'email' => $email,
      'telefono' => preg_replace('/\D/', '', (string) ($entrada['phone'] ?? '')),
      'tipo' => $tipo,
    ];
  }

  /** "juan PÉREZ" => "Juan Pérez" (respetando tildes y ñ). */
  public static function capitalizar(?string $texto): string
  {
    $texto = preg_replace('/\s+/u', ' ', trim((string) $texto));
    return mb_convert_case(mb_strtolower($texto, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
  }
}
