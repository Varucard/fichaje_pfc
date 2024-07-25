// Codigo basico para conexión de Arduino con MySql con muestreo de error al conectar
#include <SPI.h>
#include <Ethernet.h>
#include <MySQL_Connection.h>
#include <MySQL_Cursor.h>

byte mac_addr[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
IPAddress server_addr(192, 168, 1, 37);
char user[] = "root";
char password[] = "Mercedes";

EthernetClient client;
MySQL_Connection conn((Client *)&client);

void setup() {
  Serial.begin(115200);
  Ethernet.begin(mac_addr);
  Serial.println("Connecting...");
  if (conn.connect(server_addr, 3306, user, password)) {
    Serial.println("Connected.");
    delay(1000);
    MySQL_Cursor cur = MySQL_Cursor(&conn);
    cur.execute("SELECT DATABASE()");
    column_names *cols = cur.get_columns();
    row_values *row = NULL;
    while ((row = cur.get_next_row())) {
      for (int i = 0; i < cols->num_fields; i++) {
        Serial.print(row->values[i]);
        Serial.print(" ");
      }
      Serial.println();
    }
  } else {
    Serial.println("Connection failed.");
  }
  conn.close();
}

void loop() {
}
