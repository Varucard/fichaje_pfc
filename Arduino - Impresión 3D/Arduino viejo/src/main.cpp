/**
 * @file main.cpp
 * @author Matias S. Ávalos (msavalos@gmail.com)
 * @brief Acceso a base de datos con Arduino.
 * @version 0.1
 * @date 2021-02-03
 * 
 * @copyright Copyright (c) 2021
 *  
 * This file is part of Arduino MySQL & RFID Example.
 * 
 * Arduino MySQL & RFID Example is free software: you can redistribute it 
 * and/or modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 3 of the 
 * License, or (at your option) any later version.
 * 
 * Arduino MySQL & RFID Example is distributed in the hope that it will 
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty 
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Arduino MySQL & RFID Example. If not, 
 * see <http://www.gnu.org/licenses/>.
 * 
 */
#include <Arduino.h>
#include <MySQL_Connection.h>
#include <MySQL_Cursor.h>
#include <SoftwareSerial.h>

#define STX  2
#define ETX  3

const int led_error = 7, led_ok = 6, pulsador = 8;
String tarjeta_leida = "";

byte mac_addr[] = {0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED};

/* Configuración para la Base de Datos */
IPAddress server_addr(192, 168, 1, 105); // IP del MySQL *server*
const char user[]     = "root";          // MySQL login username
const char password[] = "admin";         // MySQL login password

/* Conector MySQL y Ethernet Client */
EthernetClient client;
MySQL_Connection conn((Client *)&client);

/* RDM6300 */
SoftwareSerial RFID(2,3);

void setup() {
  // Configuración de pines:
  pinMode(led_error, OUTPUT);
  pinMode(led_ok, OUTPUT);
  pinMode(pulsador, INPUT);
  // Configuración de los Serials:
  Serial.begin(115200);
  RFID.begin(9600);
  // Configuración del Ethernet (DHCP)
  Ethernet.begin(mac_addr);

  // Conección a la base de datos:
  Serial.println(F("Conectando..."));
  if (conn.connect(server_addr, 3306, user, password)) {
    delay(1000);
  } else { // Si no se puede conectar a la base muere ahí
    Serial.println(F("No se pudo conectar"));
    while(true); // halt
  }
}
/** @brief Titila 2 vece el LED rojo
 */
void show_error() {
  for(int i=0; i < 2; ++i) {
    digitalWrite(led_error, HIGH);
    delay(300);
    digitalWrite(led_error, LOW);
    delay(150);
  }
}
/** @brief Se prende el LED verde
 */
void show_ok() {
  digitalWrite(led_ok, HIGH);
  delay(1000);
  digitalWrite(led_ok, LOW);
  delay(500);
}
/** @brief Se revisa si llegó una tarjeta válida
 * 
 * @return true si se leyó una tarjeta.
 * @return false si no hubo tarjeta.
 */
bool check_rfid() {
  bool result = false;
  if(RFID.available() >= 14) {
    char c = RFID.read();
    if(c == STX) {
      tarjeta_leida = "";
      byte tag[6] ={0};
      byte chksum = 0;
      for(int i=0; i < 12  && (c = RFID.read()) ;i++) {
        // Se guarda el caracter de la tarjeta:
        if(i < 10) tarjeta_leida += c;
        // Se pasa a byte:
        c = (c >= 'A' && c <= 'F')? c-'A'+10 : c-'0';
        if(!(i%2))            // ¿es la parte alta?
          tag[i/2] = c << 4;
        else {                // es la parte baja:
          tag[i/2] |= c; 
          chksum ^= tag[i/2]; // se calcula el checksum
        }
      }
      if(!chksum && (RFID.read() == ETX)) {
        result = true;
        RFID.stopListening(); // Se detiene el serial para evitar lecturas dobles.
      } else {
        show_error();
      }
    }
  }
  return result;
}

/** @brief loop principal
 * 
 * Básicamente hay 2 estados:
 *    1. Registrar tarjeta
 *    2. Esperando tarjeta
 * 
 * Si se detecta un LOW en el pulsador se entra al estado 1.
 * sino se está siempre en el estado 2.
 */
void loop() {
  static byte puls_state = 0xFF; 
  static uint32_t last_ms =  millis();
  uint32_t now = millis();

  // lectura del pulsador
  if((now - last_ms) > 15) {
    puls_state <<= 1;
    puls_state |= digitalRead(pulsador);
    last_ms = now;
  }

  /************************ Si el pulsador está presionado ******************************/
  if(!puls_state) {
    // espero 5 segundos a que pasen una tarjeta para registrarla en la DB
    uint32_t time_out = now+5000;
    digitalWrite(led_error, HIGH); // se mantiene prendido el led rojo...
    while(now < time_out) {
      // Si se leyó una tarjeta:
      if(check_rfid()) {
        digitalWrite(led_error, LOW);
        MySQL_Cursor *cur_mem = new MySQL_Cursor(&conn);
        String query = "INSERT INTO arduino.personas (id) VALUES ('"+tarjeta_leida+"')";
        // Ejecuto el INSERT: 
        if(cur_mem->execute(query.c_str())) 
          show_ok(); // si se ingresó a la DB se prende el verde.
        else 
          show_error(); // sino titila el rojo.
        
        delete cur_mem;
        RFID.listen(); // se vuelve a habilitar el Serial para leer otra tarjeta
        break;
      }
      now = millis();
    }
    digitalWrite(led_error, LOW); // si se llegó al timeout se apaga el led rojo

  /*********** Si no se presionó el pulsador, simplemente espero una tarjeta ************/
  } else { 
    // verifico si se leyó una tarjeta:
    if(check_rfid()) {
      MySQL_Cursor *cur_mem = new MySQL_Cursor(&conn);
      String query = "SELECT apellido,nombre FROM arduino.personas WHERE id='"+tarjeta_leida+"'";
      // Busco en la base de datos:
      if(cur_mem->execute(query.c_str())) {
        row_values *row = NULL;
        column_names *columns = cur_mem->get_columns();
        row = cur_mem->get_next_row();
        // si la fila no está vacía es porque la tarjeta está registrada:
        if (row != NULL) { 
          Serial.print(row->values[0]);
          Serial.print(", ");
          Serial.println(row->values[1]);
          show_ok();
          while((row = cur_mem->get_next_row()) != NULL); // Consumo las demás filas
        } else { // sino la tarjeta no estaba registrada:
          show_error();
        }
      } else { // si hubo un error en la query:
        show_error();
      }
      delete cur_mem;
      RFID.listen(); // se vuelve a habilitar el Serial para leer otra tarjeta
    }
  }
}
