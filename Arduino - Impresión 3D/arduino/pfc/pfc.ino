#include <Ethernet.h>
#include <MySQL_Connection.h>
#include <LiquidCrystal_I2C.h>
#include <MySQL_Cursor.h>
#include <SPI.h>
#include <MFRC522.h>

// Configuración de la pantalla LCD I2C
LiquidCrystal_I2C lcd(0x27, 20, 4);

// Configuración de los LEDs
const int ledVerde = 4;
const int ledRojo = 5;
const int ledAmarillo = 6;
const int ledAzul = 7;

// Configuración del buzzer
const int buzzerPin = 3;

// Configuración del lector RFID
#define RST_PIN         9  // Configurable, ver el esquema de pines
#define SS_PIN          53 // Configurable, ver el esquema de pines
MFRC522 mfrc522(SS_PIN, RST_PIN);   // Crear instancia del MFRC522

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
MySQL_Cursor* cursor;

unsigned long lastMessageTime = 0;  // Último tiempo en que se mostró el mensaje de bienvenida
const unsigned long messageInterval = 10000;  // Intervalo para mostrar el mensaje de bienvenida en milisegundos

void setup() {
  // Iniciar la pantalla LCD
  lcd.init();
  lcd.backlight();

  // Configurar LEDs y buzzer
  pinMode(ledVerde, OUTPUT);
  pinMode(ledRojo, OUTPUT);
  pinMode(ledAmarillo, OUTPUT);
  pinMode(ledAzul, OUTPUT);
  pinMode(buzzerPin, OUTPUT);
  
  digitalWrite(ledVerde, LOW);
  digitalWrite(ledRojo, LOW);
  digitalWrite(ledAmarillo, HIGH);
  digitalWrite(ledAzul, LOW);

  // Emitir pitido inicial largo
  beep(100);

  // Mostrar mensaje inicial
  lcd.setCursor(0, 0);
  lcd.print("Palillo");
  lcd.setCursor(8, 1);
  lcd.print("Fight");
  lcd.setCursor(14, 2);
  lcd.print("Club!");
  lcd.setCursor(0, 3);
  lcd.print("Bienvenido/a Admin!");
  delay(3000);

  // Iniciar la conexión con la red
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Intentando");
  lcd.setCursor(0, 1);
  lcd.print("Conectar a la Red");
  delay(3000);

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
  delay(3000);
  
  // Intentar conectar a MySQL
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Conectando a MySQL");
  lcd.setCursor(0, 1);
  lcd.print("Aguarde por favor");
  delay(300);
  
  if (conn.connect(server_addr, port, user, password)) {
    lcd.setCursor(0, 3);
    lcd.print("Conexion a MySQL OK");
    digitalWrite(ledVerde, HIGH);
    // Emitir dos pitidos cortos
    beep(200);
    beep(200);
  } else {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Error conexion MySQL");
    lcd.setCursor(0, 2);
    lcd.print("Por favor, reinicie");
    digitalWrite(ledRojo, HIGH);
    // Emitir tres pitidos medios largos
    beep(600);
    beep(600);
    beep(600);
    delay(60000);
  }
  
  conn.close();
  delay(3000);

  // Iniciar SPI y el lector RFID
  SPI.begin();
  mfrc522.PCD_Init();

  cursor = new MySQL_Cursor(&conn);

  // Mostrar mensaje de bienvenida inicial
  showWelcomeMessage();
  lastMessageTime = millis();  // Guardar el tiempo actual
}

void loop() {
  // Verificar si hay una nueva tarjeta presente
  if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
    digitalWrite(ledAzul, LOW);

    // Leer el UID de la tarjeta
    String uid = "";
    for (byte i = 0; i < mfrc522.uid.size; i++) {
      uid += String(mfrc522.uid.uidByte[i] < 0x10 ? "0" : "");
      uid += String(mfrc522.uid.uidByte[i], HEX);
    }

    // Mostrar el UID en la pantalla LCD
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Leyendo, aguarde...");
    lcd.setCursor(0, 2);
    lcd.print("Llavero: ");
    lcd.setCursor(0, 3);
    lcd.print(uid);
    delay(3000);

    // Conectar a MySQL
    if (conn.connect(server_addr, port, user, password)) {
      // Consulta SQL para verificar si el UID existe
      String query = "SELECT name, surname FROM pfc.users WHERE rfid='" + uid + "'";
      
      // Imprimir la consulta SQL en el monitor serial
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print(query);

      cursor->execute(query.c_str());
      
      // Obtener el resultado de la consulta
      column_names *cols = cursor->get_columns();
      row_values *row = NULL;
      if ((row = cursor->get_next_row()) != NULL) {
        // UID encontrado, mostrar nombre y apellido
        String nombre = row->values[0];
        String apellido = row->values[1];
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Bienvenido/a!");
        lcd.setCursor(0, 1);
        lcd.print(nombre);
        lcd.setCursor(0, 2);
        lcd.print(apellido);
        lcd.setCursor(0, 3);
        lcd.print("Disfrute su clase!");
        beep(200);
      } else {
        // UID no encontrado
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Llavero desconocido");
        lcd.setCursor(0, 1);
        lcd.print("Contactar Admin");
        lcd.setCursor(0, 3);
        lcd.print("Gracias! PFC");
        beep(600);
      }
      conn.close();
    } else {
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Error MySQL");
      lcd.setCursor(0, 2);
      lcd.print("Por favor, reinicie");
      digitalWrite(ledRojo, HIGH);
      beep(600);
    }

    // Detener la tarjeta
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();

    // Actualizar el tiempo de la última muestra del mensaje de bienvenida
    lastMessageTime = millis();
  }

  // Mostrar el mensaje de bienvenida si ha pasado el intervalo de tiempo
  if (millis() - lastMessageTime >= messageInterval) {
    showWelcomeMessage();
    lastMessageTime = millis();  // Actualizar el tiempo de la última muestra del mensaje de bienvenida
  }
}

// Función para emitir un pitido
void beep(int duration) {
  digitalWrite(buzzerPin, HIGH);
  delay(duration);
  digitalWrite(buzzerPin, LOW);
  delay(duration);
}

// Función para mostrar el mensaje de Sistema
void showWelcomeMessage() {
  digitalWrite(ledAzul, HIGH);
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
