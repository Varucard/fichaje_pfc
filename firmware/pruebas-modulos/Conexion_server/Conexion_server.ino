// Prueba de conexión con el servidor de fichajes.
// Envía un UID de prueba al endpoint del lector y muestra la respuesta por el monitor serie.
// Ajustar IPs y token antes de subirlo (deben coincidir con el .env del servidor).
//
// Si no conecta: verificar que el firewall de la PC servidor permita conexiones
// entrantes al puerto 8080 desde la IP del Arduino (mejor una regla puntual que desactivarlo).
#include <SPI.h>
#include <Ethernet.h>

byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
IPAddress ip(192, 168, 0, 36);      // IP fija del Arduino
IPAddress server(192, 168, 0, 245); // IP del servidor PHP
const int PUERTO = 8080;
const char *TOKEN = "cambiar-este-token";

void setup() {
  Serial.begin(9600);
  Ethernet.begin(mac, ip);
  delay(1000);
  Serial.println("Iniciando conexion...");
  consultarServidor("A1B2C3D4");
}

void loop() {
}

void consultarServidor(const char *uid) {
  EthernetClient client;
  client.setConnectionTimeout(2000);

  if (!client.connect(server, PUERTO)) {
    Serial.println("No se pudo conectar al servidor");
    return;
  }

  Serial.println("Conexion exitosa");
  client.print("GET /api/arduino/lectura?uid=");
  client.print(uid);
  client.print("&auth=");
  client.print(TOKEN);
  client.println(" HTTP/1.0");
  client.println("Connection: close");
  client.println();

  unsigned long inicio = millis();
  while ((client.connected() || client.available()) && millis() - inicio < 5000) {
    if (client.available()) {
      Serial.write(client.read()); // Muestra encabezados y cuerpo tal cual
    }
  }
  client.stop();
  Serial.println();
  Serial.println("Fin de la respuesta");
}
