#include <Ethernet.h>
#include <MySQL_Connection.h>
#include <LiquidCrystal_I2C.h>

// Configuración de la pantalla LCD I2C
LiquidCrystal_I2C lcd(0x27, 20, 4);

// Configuración de los LEDs
const int ledVerde = 4;    // LED Indicador de que esta conectado a Internet y MySql
const int ledRojo = 5;     // LED algo fallo
const int ledAmarillo = 6; // LED el Equipo esta andando
const int ledAzul = 7;     // LED el Equipo esta leyendo

// Datos de configuración de Internet y MySQL
byte mac_addr[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
IPAddress server_addr(192, 168, 1, 40); // IP del MySQL *server*
IPAddress ip(192, 168, 1, 36);          // IP de Arduino
IPAddress subnet(255, 255, 255, 0);     // Sub-Mascara Arduino
IPAddress gateway(192, 168, 1, 1);      // Puerta de acceso Arduino
unsigned int port = 3306;               // Puerto MySQL
char user[] = "root";                   // MySQL username
char password[] = "Mercedes";           // MySQL password

EthernetClient client;
MySQL_Connection conn((Client *)&client);

void setup() {
  // Iniciar la pantalla LCD
  lcd.init(); 
  lcd.backlight();

  // Configurar LEDs
  pinMode(ledVerde, OUTPUT);
  pinMode(ledRojo, OUTPUT);
  pinMode(ledAmarillo, OUTPUT);
  pinMode(ledAzul, OUTPUT);
  digitalWrite(ledVerde, LOW);
  digitalWrite(ledRojo, LOW);
  digitalWrite(ledAmarillo, HIGH);
  digitalWrite(ledAzul, LOW);

  // Mostrar mensaje inicial
  lcd.setCursor(0, 0);
  lcd.print("Palillo");
  lcd.setCursor(8, 1);
  lcd.print("Fight");
  lcd.setCursor(14, 2);
  lcd.print("Club!");
  lcd.setCursor(0, 3);
  lcd.print("Bienvenido Admin!");
  delay(5000);

  // Iniciar la conexión con la red
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Intentando");
  lcd.setCursor(0, 1);
  lcd.print("Conectar a la Red");
  delay(5000);

  Ethernet.begin(mac_addr, ip, gateway, gateway, subnet); // Configurar Ethernet manualmente
  delay(2000);

  // Mostrar datos conexión a la red
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Conectado a la red");
  lcd.setCursor(0, 1);
  lcd.print("IP Arduino: ");
  lcd.setCursor(0, 2);
  lcd.print(Ethernet.localIP());
  delay(5000);
  
  // Intentar conectar a MySQL
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Conectando a MySQL");
  lcd.setCursor(0, 1);
  lcd.print("Aguarde por favor");
  delay(5000);
  
  if (conn.connect(server_addr, port, user, password)) {
    lcd.setCursor(0, 3);
    lcd.print("Conexion a MySQL OK");
    digitalWrite(ledVerde, HIGH);
    delay(5000);
  } else {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Error conexion MySQL");
    lcd.setCursor(0, 2);
    lcd.print("Por favor, reinicie");
    digitalWrite(ledRojo, HIGH);
    delay(60000);
  }
  
  conn.close();
  delay(5000);
  
  // Mostrar mensaje sistema
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Palillo");
  lcd.setCursor(8, 1);
  lcd.print("Fight");
  lcd.setCursor(14, 2);
  lcd.print("Club!");
  lcd.setCursor(0, 3);
  lcd.print("Bienvenidos!");
}

void loop() {
  // Puedes agregar aquí cualquier código que necesites ejecutar en el bucle principal.
}
