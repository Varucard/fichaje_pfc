#include <Ethernet.h>
#include <MySQL_Connection.h>
#include <LiquidCrystal.h>

// Configuración de la pantalla LCD
const int rs = 7, en = 8, d4 = 9, d5 = 10, d6 = 11, d7 = 12;
LiquidCrystal lcd(rs, en, d4, d5, d6, d7);

// Configuración de los LEDs
const int ledVerde = 4;
const int ledRojo = 5;

// Datos de configuración de Internet y MySQL
byte mac_addr[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
IPAddress server_addr(192, 168, 1, 37); // IP del MySQL *server*
IPAddress ip(192, 168, 1, 36);          // IP de Arduino
IPAddress subnet(255, 255, 255, 0);     // Sub-Mascara Arduino
IPAddress gateway(192, 168, 1, 1);      // Puerta de acceso Arduino
unsigned int port = 3306;               // Puerto MySQL
char user[] = "root";                   // MySQL username
char password[] = "Mercedes";           // MySQL password

EthernetClient client;
MySQL_Connection conn((Client *)&client);

void setup() {
  Serial.begin(115200);
  lcd.begin(20, 4); // Iniciar la pantalla LCD con 20 columnas y 4 filas

  pinMode(ledVerde, OUTPUT);
  pinMode(ledRojo, OUTPUT);
  digitalWrite(ledVerde, LOW);
  digitalWrite(ledRojo, LOW);

  while (!Serial); // Esperar a la conexion serial

  // Mostrar mensaje inicial
  lcd.setCursor(0, 0);
  Serial.println("Iniciando conexion");
  lcd.print("Iniciando conexion");

  // Iniciar la conexión con la red
  if (Ethernet.begin(mac_addr) == 0) {
    Serial.println("Error al configurar Ethernet usando DHCP");
    lcd.setCursor(0, 1);
    lcd.print("Error Ethernet DHCP");
    digitalWrite(ledRojo, HIGH); // Encender LED rojo
    // No es posible continuar, así que no hacemos nada más
    for (;;);
  }
  
  // Mostrar IP en la LCD
  lcd.setCursor(0, 1);
  lcd.print("IP: ");
  lcd.print(Ethernet.localIP());
  Serial.print("IP del Arduino: ");
  Serial.println(Ethernet.localIP());
  
  // Intentar conectar a MySQL
  lcd.setCursor(0, 2);
  lcd.print("Conectando a MySQL");
  Serial.println("Conectando a MySQL...");
  if (conn.connect(server_addr, port, user, password)) {
    Serial.println("Conexión con la base de datos exitosa.");
    lcd.setCursor(0, 3);
    lcd.print("Conexion a MySQL OK");
    digitalWrite(ledVerde, HIGH); // Encender LED verde
    delay(1000);
    // Aquí puedes agregar tu código para ejecutar consultas a la base de datos.
  } else {
    Serial.println("Conexión con la base de datos fallida.");
    lcd.setCursor(0, 3);
    lcd.print("Conexion MySQL Fallida");
    digitalWrite(ledRojo, HIGH); // Encender LED rojo
  }
  
  conn.close();
  delay(2000);
  
  // Mostrar mensaje final en la LCD
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Palillo Fight Club");
}

void loop() {
  // Puedes agregar aquí cualquier código que necesites ejecutar en el bucle principal.
}
