# Firmware del lector de fichajes

Lector RFID que se instala en la entrada. Al pasar un llavero consulta al servidor y muestra en el LCD si la persona puede ingresar.

## Hardware

| Componente | Conexión (Arduino Mega 2560) |
|---|---|
| Shield Ethernet W5100 | Sobre el Mega (SPI, CS en pin 10) |
| Lector RFID RC522 | SDA → 53, SCK → 52, MOSI → 51, MISO → 50, RST → 9, VCC → **3.3V** |
| LCD 20x4 I2C (0x27) | SDA → 20, SCL → 21, VCC → 5V |
| LED verde / rojo / amarillo / azul | Pines 4 / 5 / 6 / 7 |
| Buzzer | Pin 3 |

Los LEDs indican: **amarillo** = encendido, **azul** = listo para leer, **verde** = acceso permitido, **rojo** = acceso denegado o error.

La carcasa para imprimir en 3D está en [`carcasa-3d/`](carcasa-3d) (`CUERPO.stl` y `TAPA.stl`), y hay fotos del equipo armado en [`imagenes/`](imagenes).

## Librerías

Instalarlas desde **Arduino IDE → Herramientas → Administrar bibliotecas**:

| Librería | Versión probada |
|---|---|
| Ethernet | 2.0.2 |
| LiquidCrystal I2C (Frank de Brabander) | 1.1.2 |
| MFRC522 (GithubCommunity) | 1.4.12 |
| ArduinoJson (Benoît Blanchon) | 7.4.x |

`SPI` y `avr/wdt` vienen incluidas con el IDE.

## Configuración

1. Copiar `pfc/config.example.h` como `pfc/config.h` (este archivo no se versiona).
2. Completar las IPs de la red, la IP del servidor y el token.
3. `PFC_API_TOKEN` debe ser **el mismo valor** que `ARDUINO_TOKEN` en el `.env` del servidor.
4. Placa: **Arduino Mega or Mega 2560**. Subir `pfc/pfc.ino`.

También se puede compilar por consola:

```bash
arduino-cli compile -b arduino:avr:mega firmware/pfc
arduino-cli upload  -b arduino:avr:mega -p /dev/ttyACM0 firmware/pfc
```

## Protocolo con el servidor

**Lectura de llavero** (Arduino → servidor):

```
GET /api/arduino/lectura?uid=3A5CF681&auth=<token>
→ {"estado": "activo", "nombre": "Jose", "apellido": "Perez", "clase": "Boxeo"}
```

| `estado` | Pantalla | Se registra la fichada |
|---|---|---|
| `activo` | Bienvenido/a + nombre | Sí (una vez cada 5 minutos como máximo) |
| `moroso` | Abonar la cuota | No |
| `sinclase` | Usuario sin clase | No |
| `inactivo` | Usuario inactivo | No |
| `admin` | Saludo (profesores y administradores) | No |
| `desconocido` | Llavero desconocido (aparece un aviso en el panel) | No |

El servidor envía los nombres sin tildes y con 20 caracteres como máximo, porque el LCD no tiene esos caracteres.
`clase` es la clase deducida por el horario. El lector la muestra en la última línea y, si viene vacía, muestra "Disfrute su clase!".

**Reinicio remoto** (panel → Arduino, puerto 8080):

```
GET /reiniciar
X-PFC-Token: <token>
```

Sin el token correcto, el pedido se rechaza con 403.

## Robustez

- Cada etapa de la consulta al servidor tiene timeout, así que el lector no se cuelga si el servidor no responde.
- La respuesta se lee con un límite de 256 bytes, para no agotar la RAM.
- Si el mismo llavero queda apoyado, se ignora durante 5 segundos.
- El watchdog reinicia la placa sola si algo se traba más de 8 segundos.
- Al arrancar avisa si no detecta el shield Ethernet o si el cable de red está desconectado.

## Pruebas de módulos

En [`pruebas-modulos/`](pruebas-modulos) hay sketches mínimos para probar cada componente por separado (LEDs, buzzer, LCD, lector RFID, shield Ethernet y conexión con el servidor).
