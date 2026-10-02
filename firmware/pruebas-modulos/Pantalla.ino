// Codigo basico para conexión y prueba de pantalla LCD 4*20
// Arduino MEGA
// VCC -> 5V
// GND -> GND
// SDA -> PIN 20
// SCL -> PIN 21
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// Dirección I2C (puede ser 0x27 o 0x3F)
LiquidCrystal_I2C lcd(0x27, 20, 4);  

void setup() {
  lcd.init();        // Inicializar LCD
  lcd.backlight();   // Encender luz de fondo

  // Mostrar mensajes en cada línea
  lcd.setCursor(0, 0);
  lcd.print("Prueba LCD 20x4");

  lcd.setCursor(0, 1);
  lcd.print("Arduino Mega OK!");

  lcd.setCursor(0, 2);
  lcd.print("Linea 3 funcionando");

  lcd.setCursor(0, 3);
  lcd.print("Linea 4 lista!");
}

void loop() {
  // No se necesita código en loop()
}
