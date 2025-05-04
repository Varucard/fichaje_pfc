#include <SPI.h>
#include <Ethernet.h>
#include <LiquidCrystal_I2C.h>
#include <MFRC522.h>
#include <avr/wdt.h>
#include <ArduinoJson.h>

// LCD I2C
LiquidCrystal_I2C lcd(0x27, 20, 4);

// LEDs y buzzer
const int ledVerde = 4, ledRojo = 5, ledAmarillo = 6, ledAzul = 7;
const int buzzerPin = 13; //MODIFICAR A FUTURO

// RFID
#define RST_PIN 9
#define SS_PIN 53
MFRC522 mfrc522(SS_PIN, RST_PIN);

// Red
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
IPAddress ip(192, 168, 0, 36);
IPAddress gateway(192, 168, 0, 1);
IPAddress subnet(255, 255, 255, 0);
IPAddress server(192, 168, 0, 245); // IP de tu servidor PHP

EthernetClient client;
EthernetServer httpServer(8080);

unsigned long lastMessageTime = 0;
const unsigned long messageInterval = 10000;

void setup() {
  Serial.begin(9600);
  lcd.init();
  lcd.backlight();

  pinMode(ledVerde, OUTPUT);
  pinMode(ledRojo, OUTPUT);
  pinMode(ledAmarillo, OUTPUT);
  pinMode(ledAzul, OUTPUT);
  pinMode(buzzerPin, OUTPUT);

  digitalWrite(ledAmarillo, HIGH);
  beep(1000);

  lcd.setCursor(0, 0); lcd.print("Palillo");
  lcd.setCursor(8, 1); lcd.print("Fight");
  lcd.setCursor(14, 2); lcd.print("Club!");
  lcd.setCursor(0, 3); lcd.print("Bienvenido/a Admin!");
  delay(3000);

  lcd.clear();
  lcd.setCursor(0, 0); lcd.print("Conectando Red...");
  lcd.setCursor(0, 1); lcd.print("Aguarde por favor...");
  Ethernet.begin(mac, ip, gateway, gateway, subnet);
  delay(2000);

  lcd.clear();
  lcd.setCursor(0, 0); lcd.print("Conectado:");
  lcd.setCursor(0, 1); lcd.print(Ethernet.localIP());
  digitalWrite(ledVerde, HIGH);
  delay(3000);

  httpServer.begin();
  SPI.begin();
  mfrc522.PCD_Init();

  showWelcomeMessage();
  lastMessageTime = millis();
}

void loop() {
  handleHTTPRequests();

  if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
    digitalWrite(ledAzul, LOW);
    String uid = "";
    for (byte i = 0; i < mfrc522.uid.size; i++) {
      uid += (mfrc522.uid.uidByte[i] < 0x10 ? "0" : "");
      uid += String(mfrc522.uid.uidByte[i], HEX);
    }
    uid.toUpperCase();

    lcd.clear();
    lcd.setCursor(0, 0); lcd.print("Leyendo...");
    lcd.setCursor(0, 2); lcd.print("Llavero: ");
    lcd.setCursor(0, 3); lcd.print(uid);
    delay(2000);

    consultarServidor(uid);

    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();
    lastMessageTime = millis();
  }

  if (millis() - lastMessageTime >= messageInterval) {
    showWelcomeMessage();
    lastMessageTime = millis();
  }
}

void beep(int duration) {
  digitalWrite(buzzerPin, HIGH);
  delay(duration);
  digitalWrite(buzzerPin, LOW);
  delay(duration);
}

void showWelcomeMessage() {
  digitalWrite(ledRojo, LOW);
  digitalWrite(ledAzul, HIGH);
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print("Palillo");
  lcd.setCursor(8, 1); lcd.print("Fight");
  lcd.setCursor(14, 2); lcd.print("Club!");
  lcd.setCursor(0, 3); lcd.print("Bienvenidos!");
}

void reiniciarArduino() {
  Serial.println("Reiniciando...");
  wdt_enable(WDTO_15MS);
  while (1) {}
}

void handleHTTPRequests() {
  EthernetClient client = httpServer.available();
  if (client) {
    boolean currentLineIsBlank = true;
    String request = "";

    while (client.available()) {
      char c = client.read();
      request += c;

      if (c == '\n' && currentLineIsBlank) {
        if (request.indexOf("GET /reiniciar HTTP/1.1") >= 0) {
          reiniciarArduino();
        }
      }

      if (c == '\n') {
        currentLineIsBlank = true;
      } else if (c != '\r') {
        currentLineIsBlank = false;
      }
    }
    delay(1);
    client.stop();
  }
}

void consultarServidor(String uid) {
  if (client.connect(server, 8080)) {
    String url = "/config/get_uid.php?uid=" + uid + "&auth=ABC123";
    client.println("GET " + url + " HTTP/1.1");
    client.println("Host: 192.168.0.245");
    client.println("Connection: close");
    client.println();

    boolean headersEnded = false;
    String payload = "";

    delay(500);
    while (client.connected()) {
      while (client.available()) {
        String line = client.readStringUntil('\n');
        if (!headersEnded && line == "\r") {
          headersEnded = true;
        } else if (headersEnded) {
          payload += line;
        }
      }
    }
    client.stop();

    parsearJSON(payload);
  } else {
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print("Error servidor");
    beep(600);
  }
}

void parsearJSON(String json) {
  // Crea un objeto para almacenar los datos del JSON
  StaticJsonDocument<200> doc;  // Ajusta el tamaño según el tamaño del JSON

  // Deserializar el JSON
  DeserializationError error = deserializeJson(doc, json);

  // Verifica si ocurrió un error
  if (error) {
    Serial.println("Error al parsear JSON");
    return;
  }
  
  // Extraer los valores del JSON
  const char* estado = doc["estado"];
  const char* nombre = doc["nombre"];
  const char* apellido = doc["apellido"];

  // Convierto el tipo de dato para la comparación
  String estadoString = String(estado);

  lcd.clear();
  lcd.setCursor(0, 0);
  if (estadoString == "activo") {
    lcd.print("Bienvenido/a!");
    lcd.setCursor(0, 1); lcd.print(nombre);
    lcd.setCursor(0, 2); lcd.print(apellido);
    lcd.setCursor(0, 3); lcd.print("Disfrute su clase!");
    digitalWrite(ledVerde, HIGH);
    beep(200);
  } else if (estadoString == "moroso") {
    lcd.print("POR FAVOR");
    lcd.setCursor(0, 1); lcd.print("Abonar la cuota");
    lcd.setCursor(0, 3); lcd.print("Gracias! PFC");
    digitalWrite(ledVerde, LOW);
    digitalWrite(ledRojo, HIGH);
    beep(600); beep(600);
  } else if (estadoString == "inactivo") {
    lcd.print("Usuario inactivo");
    lcd.setCursor(0, 1); lcd.print("Contactar Admin");
    lcd.setCursor(0, 3); lcd.print("Gracias! PFC");
    digitalWrite(ledVerde, LOW);
    digitalWrite(ledRojo, HIGH);
    beep(600);
  } else if (estadoString == "sinclase") {
    lcd.print("Usuario sin clase");
    lcd.setCursor(0, 1); lcd.print("Contactar Admin");
    lcd.setCursor(0, 3); lcd.print("Gracias! PFC");
    digitalWrite(ledVerde, LOW);
    digitalWrite(ledRojo, HIGH);
    beep(600);
  } else if (estadoString == "admin") {
    lcd.print("Hola Administrador");
    lcd.setCursor(0, 3); lcd.print("Gracias! PFC");
    digitalWrite(ledVerde, HIGH);
    beep(100); beep(100); beep(100); 
  }
  else if (estadoString == "desconocido") {
    lcd.print("Llavero desconocido");
    lcd.setCursor(0, 1); lcd.print("Contactar Admin");
    lcd.setCursor(0, 3); lcd.print("Gracias! PFC");
    digitalWrite(ledVerde, LOW);
    digitalWrite(ledRojo, HIGH);
    beep(600);
  }
}

String obtenerValor(String json, String clave) {
  int i = json.indexOf(clave + ":");
  if (i == -1) return "";
  int inicio = json.indexOf(":", i) + 1;
  int fin = json.indexOf(",", inicio);
  if (fin == -1) fin = json.length();
  return json.substring(inicio, fin);
}
