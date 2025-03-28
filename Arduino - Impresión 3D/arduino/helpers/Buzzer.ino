// Codigo basico para conectar y probar Buzzer
// Arduino UNO - MEGA
// VCC -> PIN 3
// GND -> GND
#define BUZZER_PIN 3  // Pin donde está conectado el buzzer

void setup() {
  pinMode(BUZZER_PIN, OUTPUT); // Configurar el pin como salida
}

void loop() {
  digitalWrite(BUZZER_PIN, HIGH); // Encender el buzzer
  delay(1000); // Esperar 1 segundo
  digitalWrite(BUZZER_PIN, LOW);  // Apagar el buzzer
  delay(1000); // Esperar 1 segundo
}
