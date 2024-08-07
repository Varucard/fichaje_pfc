#include <Ethernet.h>
#include <MySQL_Connection.h>
#include <LiquidCrystal_I2C.h>
#include <MySQL_Cursor.h>
#include <SPI.h>
#include <MFRC522.h>
#include <avr/wdt.h>  // Librería para el Watchdog Timer

// Configuración de la pantalla LCD I2C
LiquidCrystal_I2C lcd(0x27, 20, 4);

// Configuración de los LEDs
const int ledVerde = 4;
const int ledRojo = 5;
const int ledAmarillo = 6;
const int ledAzul = 7;

// Configuración del buzzer
const int buzzerPin = 2;

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

// Configuración del servidor HTTP
EthernetServer httpServer(80);

void setup() {
  Serial.begin(9600);

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
  beep(1000);

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
  
  // Iniciar el servidor HTTP
  httpServer.begin();

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
  handleHTTPRequests(); // Manejar solicitudes HTTP

  // Verificar si hay una nueva tarjeta presente
  if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
    digitalWrite(ledAzul, LOW);

    // Leer el UID de la tarjeta
    String uid = "";
    for (byte i = 0; i < mfrc522.uid.size; i++) {
      uid += String(mfrc522.uid.uidByte[i] < 0x10 ? "0" : "");
      uid += String(mfrc522.uid.uidByte[i], HEX);
    }
    uid.toUpperCase();  // Convertir a mayúsculas para consistencia

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
      // Consulta SQL para verificar si el UID existe y obtener estado de activo/inactivo
      String query = "SELECT u.id_user, u.name, u.surname, u.asset, "
                     "COALESCE( "
                     "(SELECT date_of_renovation "
                     " FROM pfc.payments p "
                     " WHERE p.id_user = u.id_user "
                     " ORDER BY p.date_of_renovation DESC "
                     " LIMIT 1), "
                     "'2010-01-01 00:00:00') AS last_payment_date, "
                     "NOW() AS current_date_time "
                     "FROM pfc.users u "
                     "WHERE u.rfid = '" + uid + "'";

      cursor->execute(query.c_str());

      // Obtener el resultado de la consulta
      column_names *cols = cursor->get_columns();
      row_values *row = NULL;
      if ((row = cursor->get_next_row()) != NULL) {
        // UID encontrado, obtener id_user, nombre y apellido
        int userId = atoi(row->values[0]);
        String nombre = row->values[1];
        String apellido = row->values[2];
        int asset = atoi(row->values[3]);
        String fechaRenovacion = row->values[4];
        String fechaActual = row->values[5];

        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Bienvenido/a!");
        lcd.setCursor(0, 1);
        lcd.print(nombre);
        lcd.setCursor(0, 2);
        lcd.print(apellido);
        delay(2000);

        // Verificar el estado de activo/inactivo del usuario
        if (asset == 1) {
          // Usuario activo, comparar la fecha de renovación con la fecha actual
          if (fechaActual < fechaRenovacion) {
            // La fecha de renovación es mayor o igual a la fecha actual
            lcd.setCursor(0, 3);
            lcd.print("Disfrute su clase!");
            beep(200);

            // Registrar la fichada en la tabla incomes
            String insertQuery = "INSERT INTO pfc.incomes (id_user, addmission_date) VALUES (" + String(userId) + ", NOW())";
            cursor->execute(insertQuery.c_str());
          } else {
            // La fecha de renovación es menor que la fecha actual
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("Por favor");
            lcd.setCursor(0, 1);
            lcd.print("Abone la cuota!");
            lcd.setCursor(0, 3);
            lcd.print("Gracias! PFC");
            beep(600);
            beep(600);
            beep(600);

            // Registrar el UID del deudor en la BD
            String insertQueryUIDDeudor = "INSERT INTO pfc.uid_incomes (uid) VALUES ('" + uid + "')";
            cursor->execute(insertQueryUIDDeudor.c_str());
          }
        } else {
          // Usuario inactivo, mostrar mensaje y registrar UID en pfc.uid_incomes
          lcd.clear();
          lcd.setCursor(0, 0);
          lcd.print("Usuario inactivo");
          lcd.setCursor(0, 1);
          lcd.print("Contactar Admin");
          lcd.setCursor(0, 3);
          lcd.print("Gracias! PFC");
          beep(600);

          // Registrar el UID del usuario inactivo en la BD
          String insertQueryUIDInactivo = "INSERT INTO pfc.uid_incomes (uid) VALUES ('" + uid + "')";
          cursor->execute(insertQueryUIDInactivo.c_str());
        }

      } else {
        // Registrar el UID desconocido en la BD
        String insertQueryUID = "INSERT INTO pfc.uid_incomes (uid) VALUES ('" + uid + "')";
        cursor->execute(insertQueryUID.c_str());

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

// Función para reiniciar el Arduino
void reiniciarArduino() {
  Serial.println("Reiniciando...");
  wdt_enable(WDTO_15MS); // Habilitar el Watchdog Timer con un tiempo de espera de 15ms
  while (1) {} // Esperar a que el Watchdog Timer reinicie el Arduino
}

// Función para manejar solicitudes HTTP
void handleHTTPRequests() {
  EthernetClient client = httpServer.available(); // Escuchar clientes entrantes

  if (client) {
    boolean currentLineIsBlank = true;
    String request = "";

    while (client.available()) {
      char c = client.read();
      request += c;

      if (c == '\n' && currentLineIsBlank) {
        if (request.indexOf("GET /reiniciar HTTP/1.1") >= 0) {
          reiniciarArduino(); // Llama a la función para reiniciar el Arduino
        }
      }

      if (c == '\n') {
        currentLineIsBlank = true;
      } else if (c != '\r') {
        currentLineIsBlank = false;
      }
    }

    delay(1);
    client.stop(); // Cerrar la conexión
  }
}
