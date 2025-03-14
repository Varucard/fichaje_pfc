// Codigo basico para conexión y prueba de lector RFID
// Arduino MEGA
// VCC -> 3.3V
// GND -> GND
// RST -> PIN 9
// SDA -> PIN 53
// SCK -> PIN 52
// MOSI -> PIN 51
// MISO -> PIN 50
#include <SPI.h>
#include <MFRC522.h>

#define SS_PIN 53   // Pin SDA para Arduino Mega
#define RST_PIN 9   // Pin de Reset

MFRC522 mfrc522(SS_PIN, RST_PIN); // Crear instancia del módulo RFID

void setup() {
  Serial.begin(9600); // Iniciar comunicación serie
  SPI.begin();        // Iniciar bus SPI
  mfrc522.PCD_Init(); // Iniciar el RC522
  Serial.println("Acerque una tarjeta...");
}

void loop() {
  // Revisar si hay una tarjeta presente
  if (!mfrc522.PICC_IsNewCardPresent()) {
    return;
  }

  // Intentar leer la tarjeta
  if (!mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  Serial.print("UID de la tarjeta: ");
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    Serial.print(mfrc522.uid.uidByte[i] < 0x10 ? " 0" : " ");
    Serial.print(mfrc522.uid.uidByte[i], HEX);
  }
  Serial.println();

  mfrc522.PICC_HaltA(); // Detener comunicación con la tarjeta
}
