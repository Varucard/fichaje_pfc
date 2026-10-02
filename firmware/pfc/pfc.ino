/*
 * Lector de fichajes - Palillo Fight Club
 *
 * Hardware: Arduino Mega 2560 + Shield Ethernet W5100 + lector RFID RC522
 *           + LCD 20x4 I2C + 4 LEDs + buzzer.
 *
 * Al pasar un llavero consulta al servidor:
 *   GET PFC_API_PATH?uid=XXXXXXXX&auth=PFC_API_TOKEN
 * y muestra en pantalla la respuesta {"estado": "...", "nombre": "...", "apellido": "..."}.
 *
 * También escucha en el puerto 8080 el pedido de reinicio que envía el panel
 * (GET /reiniciar con el header X-PFC-Token).
 *
 * Configuración: copiar config.example.h como config.h y completar.
 * Librerías (Library Manager): Ethernet 2.0.2, LiquidCrystal I2C 1.1.2,
 *                              MFRC522 1.4.12, ArduinoJson 7.x
 */

#include <SPI.h>
#include <Ethernet.h>
#include <LiquidCrystal_I2C.h>
#include <MFRC522.h>
#include <avr/wdt.h>
#include <ArduinoJson.h>

#include "config.h" // Copiar config.example.h como config.h

// ---------------------------------------------------------------- Pines
const uint8_t PIN_LED_VERDE = 4;
const uint8_t PIN_LED_ROJO = 5;
const uint8_t PIN_LED_AMARILLO = 6; // Encendido = equipo con energía
const uint8_t PIN_LED_AZUL = 7;     // Encendido = listo para leer
const uint8_t PIN_BUZZER = 3;
const uint8_t PIN_RFID_RST = 9;
const uint8_t PIN_RFID_SS = 53;

// ---------------------------------------------------------------- Tiempos
const unsigned long INTERVALO_BIENVENIDA_MS = 10000; // Vuelve a la pantalla inicial
const unsigned long TIMEOUT_CONEXION_MS = 2000;      // Conectar con el servidor
const unsigned long TIMEOUT_RESPUESTA_MS = 4000;     // Cada etapa de la respuesta
const unsigned long TIMEOUT_PEDIDO_HTTP_MS = 1000;   // Leer un pedido entrante
const unsigned long ANTIRREBOTE_MS = 5000;           // Ignora el mismo llavero apoyado

const size_t MAX_RESPUESTA = 256; // El JSON esperado ocupa ~80 bytes
const uint8_t LCD_COLUMNAS = 20;

// ---------------------------------------------------------------- Periféricos
LiquidCrystal_I2C lcd(0x27, LCD_COLUMNAS, 4);
MFRC522 rfid(PIN_RFID_SS, PIN_RFID_RST);

byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
IPAddress ip(PFC_IP);
IPAddress gateway(PFC_GATEWAY);
IPAddress subnet(PFC_SUBNET);
IPAddress servidor(PFC_SERVER_IP);

EthernetServer servidorHttp(8080);

// ---------------------------------------------------------------- Estado
unsigned long ultimoMensaje = 0;
char ultimoUid[21] = "";
unsigned long momentoUltimoUid = 0;

// ================================================================ Utilidades

void beep(unsigned int duracion) {
  digitalWrite(PIN_BUZZER, HIGH);
  delay(duracion);
  digitalWrite(PIN_BUZZER, LOW);
  delay(duracion);
}

void leds(bool verde, bool rojo, bool azul) {
  digitalWrite(PIN_LED_VERDE, verde ? HIGH : LOW);
  digitalWrite(PIN_LED_ROJO, rojo ? HIGH : LOW);
  digitalWrite(PIN_LED_AZUL, azul ? HIGH : LOW);
}

// Escribe una línea completa del LCD: recorta a 20 caracteres y borra el resto.
void linea(uint8_t fila, const char *texto) {
  lcd.setCursor(0, fila);
  uint8_t escritos = 0;
  while (texto && texto[escritos] && escritos < LCD_COLUMNAS) {
    lcd.write(texto[escritos]);
    escritos++;
  }
  while (escritos < LCD_COLUMNAS) {
    lcd.write(' ');
    escritos++;
  }
}

void pantalla(const char *l0, const char *l1 = "", const char *l2 = "", const char *l3 = "") {
  linea(0, l0);
  linea(1, l1);
  linea(2, l2);
  linea(3, l3);
}

void mostrarBienvenida() {
  leds(true, false, true);
  lcd.clear();
  lcd.setCursor(0, 0);  lcd.print("Palillo");
  lcd.setCursor(8, 1);  lcd.print("Fight");
  lcd.setCursor(14, 2); lcd.print("Club!");
  lcd.setCursor(0, 3);  lcd.print("Bienvenidos!");
  ultimoMensaje = millis();
}

void mostrarError(const char *titulo, const char *detalle) {
  leds(false, true, false);
  pantalla(titulo, detalle, "", "Contactar Admin");
  beep(600);
}

void reiniciarArduino() {
  Serial.println(F("Reiniciando..."));
  pantalla("Reiniciando...", "Aguarde por favor");
  wdt_enable(WDTO_15MS);
  while (true) {}
}

// ================================================================ Red

void iniciarRed() {
  pantalla("Conectando Red...", "Aguarde por favor...");
  Ethernet.begin(mac, ip, gateway, gateway, subnet); // DNS = gateway

  if (Ethernet.hardwareStatus() == EthernetNoHardware) {
    pantalla("ERROR: sin shield", "Ethernet detectado", "", "Revisar conexion");
    leds(false, true, false);
    while (true) { beep(1000); } // Sin red el lector no puede funcionar
  }

  if (Ethernet.linkStatus() == LinkOFF) {
    pantalla("ATENCION:", "Cable de red", "desconectado");
    beep(300); beep(300);
    delay(3000);
  }

  char textoIp[LCD_COLUMNAS + 1];
  IPAddress local = Ethernet.localIP();
  snprintf(textoIp, sizeof(textoIp), "%u.%u.%u.%u", local[0], local[1], local[2], local[3]);
  pantalla("Conectado:", textoIp);
  digitalWrite(PIN_LED_VERDE, HIGH);
  delay(2000);

  servidorHttp.begin();
}

/*
 * Consulta al servidor por un llavero.
 * Devuelve el código HTTP (0 si no se pudo conectar) y deja el cuerpo en `cuerpo`.
 * Cada etapa tiene timeout, así el lector nunca queda colgado esperando al servidor.
 */
int consultarServidor(const char *uid, char *cuerpo, size_t tamanio) {
  EthernetClient cliente;
  cliente.setConnectionTimeout(TIMEOUT_CONEXION_MS);
  cliente.setTimeout(TIMEOUT_RESPUESTA_MS);
  cuerpo[0] = '\0';

  wdt_reset();
  if (!cliente.connect(servidor, PFC_SERVER_PORT)) {
    return 0;
  }

  // HTTP/1.0: el servidor responde sin "chunked encoding" y cierra la conexión al terminar.
  cliente.print(F("GET " PFC_API_PATH "?uid="));
  cliente.print(uid);
  cliente.print(F("&auth=" PFC_API_TOKEN " HTTP/1.0\r\n"
                  "Host: " PFC_SERVER_HOST "\r\n"
                  "Connection: close\r\n\r\n"));

  // Línea de estado: "HTTP/1.1 200 OK"
  wdt_reset();
  char estado[32];
  size_t n = cliente.readBytesUntil('\n', estado, sizeof(estado) - 1);
  estado[n] = '\0';
  int codigo = 0;
  const char *espacio = strchr(estado, ' ');
  if (espacio) {
    codigo = atoi(espacio + 1);
  }

  // Saltear encabezados y leer el cuerpo con límite de tamaño y de tiempo.
  wdt_reset();
  if (codigo > 0 && cliente.find((char *)"\r\n\r\n")) {
    wdt_reset();
    size_t largo = 0;
    unsigned long inicio = millis();
    while ((cliente.connected() || cliente.available()) && millis() - inicio < TIMEOUT_RESPUESTA_MS) {
      if (cliente.available()) {
        char c = cliente.read();
        if (largo < tamanio - 1) {
          cuerpo[largo++] = c;
        }
      }
    }
    cuerpo[largo] = '\0';
  }

  cliente.stop();
  wdt_reset();
  return codigo;
}

/*
 * Atiende pedidos HTTP entrantes. Solo existe GET /reiniciar, que requiere
 * el header "X-PFC-Token: <PFC_API_TOKEN>".
 */
void atenderPedidosHttp() {
  EthernetClient cliente = servidorHttp.available();
  if (!cliente) {
    return;
  }

  bool pideReinicio = false;
  bool tokenValido = false;
  char renglon[96];
  unsigned long inicio = millis();
  cliente.setTimeout(TIMEOUT_PEDIDO_HTTP_MS);

  // Lee la línea del pedido y los encabezados hasta la línea vacía.
  for (uint8_t i = 0; i < 20 && millis() - inicio < TIMEOUT_PEDIDO_HTTP_MS; i++) {
    size_t n = cliente.readBytesUntil('\n', renglon, sizeof(renglon) - 1);
    if (n > 0 && renglon[n - 1] == '\r') n--;
    renglon[n] = '\0';
    if (n == 0) break;

    if (i == 0) {
      pideReinicio = strncmp(renglon, "GET /reiniciar", 14) == 0;
    } else if (strncasecmp(renglon, "X-PFC-Token:", 12) == 0) {
      const char *valor = renglon + 12;
      while (*valor == ' ') valor++;
      tokenValido = strcmp(valor, PFC_API_TOKEN) == 0;
    }
  }

  bool reiniciar = pideReinicio && tokenValido;
  if (reiniciar) {
    cliente.print(F("HTTP/1.0 200 OK\r\n"));
  } else if (pideReinicio) {
    cliente.print(F("HTTP/1.0 403 Forbidden\r\n"));
  } else {
    cliente.print(F("HTTP/1.0 404 Not Found\r\n"));
  }
  cliente.print(F("Content-Type: text/plain\r\nConnection: close\r\n\r\n"));
  cliente.print(reiniciar ? F("Reiniciando") : F("Rechazado"));
  delay(1);
  cliente.stop();

  if (reiniciar) {
    reiniciarArduino();
  }
}

// ================================================================ Lector RFID

// Arma el UID en hexadecimal mayúsculas (ej: "3A5CF681").
void uidComoTexto(char *destino, size_t tamanio) {
  size_t pos = 0;
  for (byte i = 0; i < rfid.uid.size && pos + 2 < tamanio; i++) {
    snprintf(destino + pos, tamanio - pos, "%02X", rfid.uid.uidByte[i]);
    pos += 2;
  }
  destino[pos] = '\0';
}

void mostrarRespuesta(const char *json) {
  JsonDocument doc;
  if (deserializeJson(doc, json)) {
    Serial.print(F("JSON invalido: "));
    Serial.println(json);
    mostrarError("Respuesta invalida", "del servidor");
    return;
  }

  const char *estado = doc["estado"] | "";
  const char *nombre = doc["nombre"] | "";
  const char *apellido = doc["apellido"] | "";

  if (strcmp(estado, "activo") == 0) {
    leds(true, false, false);
    pantalla("Bienvenido/a!", nombre, apellido, "Disfrute su clase!");
    beep(200);
  } else if (strcmp(estado, "moroso") == 0) {
    leds(false, true, false);
    pantalla("POR FAVOR", "Abonar la cuota", nombre, "Gracias! PFC");
    beep(600); beep(600);
  } else if (strcmp(estado, "inactivo") == 0) {
    leds(false, true, false);
    pantalla("Usuario inactivo", "Contactar Admin", "", "Gracias! PFC");
    beep(600);
  } else if (strcmp(estado, "sinclase") == 0) {
    leds(false, true, false);
    pantalla("Usuario sin clase", "Contactar Admin", nombre, "Gracias! PFC");
    beep(600);
  } else if (strcmp(estado, "admin") == 0) {
    leds(true, false, false);
    pantalla("Hola!", nombre, apellido, "Gracias! PFC");
    beep(100); beep(100); beep(100);
  } else if (strcmp(estado, "desconocido") == 0) {
    leds(false, true, false);
    pantalla("Llavero desconocido", "Contactar Admin", "", "Gracias! PFC");
    beep(600);
  } else {
    mostrarError("Respuesta invalida", estado);
  }
}

void procesarLlavero() {
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    return;
  }

  char uid[21];
  uidComoTexto(uid, sizeof(uid));
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  // Si el llavero quedó apoyado, no se vuelve a fichar.
  if (strcmp(uid, ultimoUid) == 0 && millis() - momentoUltimoUid < ANTIRREBOTE_MS) {
    return;
  }
  strncpy(ultimoUid, uid, sizeof(ultimoUid) - 1);
  momentoUltimoUid = millis();

  Serial.print(F("Llavero: "));
  Serial.println(uid);
  leds(false, false, false);
  pantalla("Leyendo...", "", "Llavero:", uid);

  char cuerpo[MAX_RESPUESTA];
  int codigo = consultarServidor(uid, cuerpo, sizeof(cuerpo));

  if (codigo == 0) {
    mostrarError("Error servidor", "Sin conexion");
  } else if (codigo == 403) {
    mostrarError("Error servidor", "Token invalido");
  } else if (codigo != 200) {
    char detalle[LCD_COLUMNAS + 1];
    snprintf(detalle, sizeof(detalle), "Codigo HTTP %d", codigo);
    mostrarError("Error servidor", detalle);
  } else {
    mostrarRespuesta(cuerpo);
  }

  ultimoMensaje = millis();
}

// ================================================================ Programa

void setup() {
  // Si el watchdog reinició la placa queda activo: desactivarlo antes de los delays de arranque.
  MCUSR = 0;
  wdt_disable();

  Serial.begin(9600);
  lcd.init();
  lcd.backlight();

  pinMode(PIN_LED_VERDE, OUTPUT);
  pinMode(PIN_LED_ROJO, OUTPUT);
  pinMode(PIN_LED_AMARILLO, OUTPUT);
  pinMode(PIN_LED_AZUL, OUTPUT);
  pinMode(PIN_BUZZER, OUTPUT);

  digitalWrite(PIN_LED_AMARILLO, HIGH);
  beep(1000);

  lcd.setCursor(0, 0);  lcd.print("Palillo");
  lcd.setCursor(8, 1);  lcd.print("Fight");
  lcd.setCursor(14, 2); lcd.print("Club!");
  lcd.setCursor(0, 3);  lcd.print("Iniciando...");
  delay(2000);

  iniciarRed();

  SPI.begin();
  rfid.PCD_Init();

  // Si algo se traba más de 8 segundos, la placa se reinicia sola.
  wdt_enable(WDTO_8S);
  mostrarBienvenida();
}

void loop() {
  wdt_reset();

  atenderPedidosHttp();
  procesarLlavero();

  if (millis() - ultimoMensaje >= INTERVALO_BIENVENIDA_MS) {
    mostrarBienvenida();
  }
}
