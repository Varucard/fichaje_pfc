// Configuración del lector de fichajes.
// Copiar este archivo como "config.h" (no se versiona) y ajustar los valores.
#pragma once

// Red del Arduino
#define PFC_IP          192, 168, 0, 36
#define PFC_GATEWAY     192, 168, 0, 1
#define PFC_SUBNET      255, 255, 255, 0

// Servidor PHP del sistema de fichajes
#define PFC_SERVER_IP   192, 168, 0, 245
#define PFC_SERVER_HOST "192.168.0.245"
// Docker: 8080 | XAMPP: 80
#define PFC_SERVER_PORT 8080

// Endpoint y token. El token debe coincidir con ARDUINO_TOKEN del .env del servidor.
// Docker: "/api/arduino/lectura"
// XAMPP (app en http://servidor/fichaje_pfc): "/fichaje_pfc/api/arduino/lectura"
#define PFC_API_PATH    "/api/arduino/lectura"
#define PFC_API_TOKEN   "cambiar-este-token"

// Puerto en el que el lector escucha el pedido de reinicio del panel.
// Debe coincidir con ARDUINO_PORT del .env del servidor.
#define PFC_HTTP_PORT   8080
