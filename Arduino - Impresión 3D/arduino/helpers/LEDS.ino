// Codigo basico para conexión y prueba de LEDS
// Arduino UNO - MEGA
// LED VERDE -> PIN 4
// LED ROJO -> PIN 5
// LED AMARILLO -> PIN 6
// LED AZUL -> PIN 7
// GND GENERAL -> GND
// Definir pines de los LEDs
#define LED_VERDE 4
#define LED_ROJO 5
#define LED_AMARILLO 6
#define LED_AZUL 7

void setup() {
  // Configurar pines como salidas
  pinMode(LED_VERDE, OUTPUT);
  pinMode(LED_ROJO, OUTPUT);
  pinMode(LED_AMARILLO, OUTPUT);
  pinMode(LED_AZUL, OUTPUT);
}

void loop() {
  digitalWrite(LED_VERDE, HIGH);  // Encender LED verde
  delay(500);                     // Esperar 500ms
  digitalWrite(LED_VERDE, LOW);   // Apagar LED verde

  digitalWrite(LED_ROJO, HIGH);   // Encender LED rojo
  delay(500);
  digitalWrite(LED_ROJO, LOW);   

  digitalWrite(LED_AMARILLO, HIGH); // Encender LED amarillo
  delay(500);
  digitalWrite(LED_AMARILLO, LOW);  

  digitalWrite(LED_AZUL, HIGH);   // Encender LED azul
  delay(500);
  digitalWrite(LED_AZUL, LOW);    
}
