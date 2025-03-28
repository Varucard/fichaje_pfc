// Codigo basico para conexión y prueba de Shield W5100
#include <SPI.h>
#include <Ethernet.h>

// Dirección MAC (cambia si es necesario)
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };

// Objeto para manejar la conexión Ethernet
EthernetClient client;

void setup() {
  Serial.begin(9600);
  while (!Serial);  // Espera a que el puerto serie esté listo

  Serial.println("Iniciando Ethernet...");

  // Iniciar Ethernet con DHCP
  if (Ethernet.begin(mac) == 0) {
    Serial.println("Error: No se pudo obtener IP por DHCP");
    Serial.println("Intentando IP manual...");

    // Configurar IP manualmente
    IPAddress ip(192, 168, 0, 177);
    Ethernet.begin(mac, ip);
  }

  Serial.print("IP asignada: ");
  Serial.println(Ethernet.localIP());
}

void loop() {
  // Mantiene activa la conexión DHCP
}
