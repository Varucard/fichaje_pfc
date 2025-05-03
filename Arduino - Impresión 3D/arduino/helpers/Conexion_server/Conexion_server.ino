#include <SPI.h>
#include <Ethernet.h>

// Configuración de red
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
IPAddress ip(192, 168, 0, 36);          // IP fija del Arduino
IPAddress server(192, 168, 0, 245);      // IP del servidor PHP

EthernetClient client;

void setup() {
  Serial.begin(9600);
  Ethernet.begin(mac, ip);
  delay(1000);
  Serial.println("Iniciando conexión...");

  String uid = "1234567890";
  consultarServidor(uid);
}

void loop() {
  // Nada en loop para esta demo
}

// Es posible que al conectarse y tirar error sea necesario o desactivar el Firewall (No aconsejado)
// O crear una regla de entrada que solo permita el acceso de la IP del Arduino
void consultarServidor(String uid) {
  Serial.println("Conectando al servidor...");

  if (client.connect(server, 8080)) {
    Serial.println("Conexión exitosa");

    String url = "/config/get_uid.php?uid=" + uid;

    client.println("GET " + url + " HTTP/1.1");
    client.println("Host: 192.168.0.245:8080");
    client.println("Connection: close");
    client.println();

    String response = "";
    bool headersEnded = false;

    while (client.connected()) {
      while (client.available()) {
        String line = client.readStringUntil('\n');
        if (!headersEnded) {
          if (line == "\r") {
            headersEnded = true; // fin de los headers HTTP
          }
        } else {
          response += line;
        }
      }
    }
    client.stop();

    Serial.println("Respuesta recibida:");
    Serial.println(response);
    // Aquí podrías parsear el JSON si querés
  } else {
    Serial.println("No se pudo conectar al servidor");
  }
}
