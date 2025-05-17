# 🥋 Palillo Fight Club | Fichaje PFC  
Sistema de control de asistencia para clientes del **Palillo Fight Club**, permitiendo la matriculación en clases.  
Incluye integración con un **lector RFID basado en Arduino**.  

---

## 📌 Requisitos de Software
- **Base de datos:** MariaDB 10.0.0 o MySql 9.0.0 ⬆️
- **Servidor web:** Apache 2.4.58 o ⬆️
- **PHP:** 8.0.0 o ⬆️  

## 🛠️ Entornos de desarrollo o producción: 

### 📝 Instalación en XAMPP (8.2.12 - 01-03-2025)  
Para ejecutar este proyecto, se recomienda instalar **XAMPP 8.2.12** o Superior.  
🔗 [Descargar XAMPP](https://www.apachefriends.org/es/download.html)  

### 🚀 Pasos de Instalación  
1. **Instalar XAMPP** y asegurarse de que **Apache** y **MySQL** estén en ejecución.
2. **Configuración de Apache, PHP y MySQL:**  

   - **Apache (`httpd.conf`)** Configurar las variables de entorno al final del archivo (Es necesario configurar todas las variables de entorno dentro del archivo)
     ```ini
     SetEnv MYSQL_DB_HOST "localhost"
     SetEnv MYSQL_DB_USER "root"
     SetEnv MYSQL_DB_PASSWORD "Password"
     ```

   - **PHP (`php.ini`)** – Configurar la zona horaria:  
     ```ini
     date.timezone = "America/Argentina/Buenos_Aires"
     ```
  - **Modificar la clave del Usuario root**:  
    - Ingresar a PHPMyAdmin y modificar la clave del Usuario root

  - **Crear Usuario para el sistema**:  
    - Ingresar a PHPMyAdmin creando un Usuario para el proyecto

   - **phpMyAdmin (`config.inc.php`)** – Configurar acceso:  
     ```php
     /* Tipo de autenticación e info */
     $cfg['Servers'][$i]['auth_type'] = 'cookie';
     $cfg['Servers'][$i]['user'] = 'root';
     $cfg['Servers'][$i]['password'] = 'ClaveRoot';
     $cfg['Servers'][$i]['extension'] = 'mysqli';
     $cfg['Servers'][$i]['AllowNoPassword'] = false;
     ```

3. **Clonar el repositorio** dentro de la carpeta `htdocs`.  
   ```bash
   git clone https://github.com/varucard/fichaje_pfc.git
   ```
4. **Importar la base de datos** en phpMyAdmin:  
   - Archivo: `fichaje_pfc.sql`  
   - La BD contiene:  
     - Un Administrador (🙋🏻‍♂️: 41550112 🔑: 123456789)
     - Clases  
     - Profesores  
     - Alumnos con matriculaciones, pagos y fichadas  

5. **Configurar la conexión a la base de datos** en `.env`.  
6. **Acceder al sistema** desde `http://localhost/proyecto/`.  
7. **Recorda siempre reiniciar los servicios desde el control de XAMPP**.

## 🔧 Instalar debbuger en XAMPP

1. Obtener la extensión **`PHP Debug`**: 
    ```
    PHP Debug
    ID: xdebug.php-debug
    Descripción: Debug support for PHP with Xdebug
    Versión: 1.35.0
    Editor: Xdebug
    Vínculo de VS Marketplace: https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug
    ```
2. Ejecutar un archivo la instrucción de **`phpinfo()`** para obtener toda la información de tu versión de **PHP**

3. Entrar en el siguiente enlace y seguir las instrucciones de la misma 🔗 [XDebug](https://xdebug.org/wizard) para obtener su archivo de **XDebug**

4. Una vez descargado el archivo y guardado donde indique la pagina es necesario configurar el mismo en **XAMPP**, abrir el archivo **`php.ini`** y pegar la siguiente configuración al final del archivo:
    ```ini
    [Xdebug]
    zend_extension = "C:\xampp\php\ext\php_xdebug.dll"
    xdebug.mode = debug
    xdebug.start_with_request = yes
    xdebug.client_host = 127.0.0.1
    xdebug.client_port = 9003
    xdebug.log = "C:\xampp\xdebug.log"
    ```
5. Generar un nuevo **`launch.json`** (```Ctrl + Shift + D```)
    ``` json
    {
    "version": "0.2.0",
    "configurations": [
      {
        "name": "Listen for Xdebug",
        "type": "php",
        "request": "launch",
        "port": 9003,
        "pathMappings": {
          "C:\\xampp\\htdocs": "${workspaceFolder}"
        },
        "xdebugSettings": {
          "max_children": 256,
          "max_data": 1024,
          "max_depth": 3
        },
        "log": true
      }
    ]
    }
    ```
6. **Recorda siempre reiniciar los servicios desde el control de XAMPP**.

---

## 🚢 Instalación en Docker  
🔗 [Descargar Docker Desktop](https://desktop.docker.com/win/main/amd64/Docker%20Desktop%20Installer.exe?utm_source=docker&amp;utm_medium=webreferral&amp;utm_campaign=dd-smartbutton&amp;utm_location=module&amp;_gl=1*58yczi*_gcl_au*MTU5NTI1NzI0NC4xNzQxODA1NjU0*_ga*MTI4NzU4MDE4OC4xNzQxODA1NjU0*_ga_XJWPQMJYHQ*MTc0MTgwNTY1My4xLjEuMTc0MTgwNTY2My41MC4wLjA)  

### 🚀 Pasos de Instalación  
1. **Instalar Docker**, ejecutar el archivo **`docker-compose.yml`** Con la Extensión **`Docker`** en **`VSCode`** o por la consola de **`Docker-Desktop`**.

2. **Clonar el repositorio** dentro de la carpeta de `Docker`.  
   ```bash
   git clone https://github.com/varucard/fichaje_pfc.git
   ```
3. **Importar la base de datos** en phpMyAdmin:  
   - Archivo: `fichaje_pfc.sql`  
   - La BD contiene:  
     - Un Administrador (🙋🏻‍♂️: 41550112 🔑: 123456789)
     - Clases  
     - Profesores  
     - Alumnos con matriculaciones, pagos y fichadas  

4. **Acceder al sistema** desde `http:8080//localhost/fichaje_pfc/`. 

## 🔧 Instalar debbuger en Docker

1. Obtener la extensión **`PHP Debug`**: 
    ```
    PHP Debug
    ID: xdebug.php-debug
    Descripción: Debug support for PHP with Xdebug
    Versión: 1.35.0
    Editor: Xdebug
    Vínculo de VS Marketplace: https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug
    ```
2. Generar un nuevo **`launch.json`** (```Ctrl + Shift + D```)
    ``` json
    {
    "version": "0.2.0",
    "configurations": [
      {
        "name": "Listen for Xdebug",
        "type": "php",
        "request": "launch",
        "port": 9003,
        "pathMappings": {
          "/var/www/html": "${workspaceFolder}"
        },
        "xdebugSettings": {
          "max_children": 256,
          "max_data": 1024,
          "max_depth": 3
        },
        "log": true
      }
    ]
    }
    ```
---

# 🔓 Configurar Firewall de Windows para que permita la conexión del Arduino al Equipo

1. Abrir **Firewall de Windows con seguridad avanzada** (`wf.msc`).
2. Ir a **Reglas de entrada** > **Nueva regla**.
3. Seleccionar **Puerto**, luego **TCP** y especificar el puerto (por ejemplo, `8080`).
4. Seleccionar **Permitir la conexión**.
5. Aplicar a todos los perfiles de red.
6. Asignar un nombre, por ejemplo: `Arduino HTTP Access`.
7. Editar la regla creada, ir a la pestaña **Ámbito**.
8. En **Dirección IP remota**, seleccionar **Estas direcciones IP** y agregar la IP del Arduino.

---

## 💻 Integración con Lector RFID (Arduino)  
El sistema está diseñado para funcionar con un **lector RFID basado en Arduino**, que permite registrar la asistencia de los usuarios escaneando una tarjeta o llavero RFID.  

### 🔧 Requisitos de Hardware  
- **Arduino Mega** 
- **Ethernet Shield 5100**
- **Pantalla LCD 20X4 con adaptador I2C**
- **4 Leds (Verde - Rojo - Amarillo - Azul/ Cada uno debe de llevar una resistencia => 220Ω - 330Ω)**
- **Módulo RFID RC522**  
- **Buzzer Activo**
- **Adaptador 12V 3A**
- **Step Down 1.25v - 35v 3a**
- **Cooler max 80MM a 12V - 5V**
- **Rejilla Cooler 80MM**
- **Conexión Ethernet**
- **Conexión USB a la PC** 

### 🔌 Diagrama de conexión
- **Conectar GND Arduino a GND Step Down**

- **Pantalla LCD 20X4 con adaptador I2C**
  - GND -> GND Step Down

- **Pantalla LCD 20X4 con adaptador I2C**
  - VCC -> 5V Step Down
  - GND -> GND Step Down
  - SDA -> PIN 20
  - SCL -> PIN 21

- **Leds**
  - LED VERDE -> PIN 4
  - LED ROJO -> PIN 5
  - LED AMARILLO -> PIN 6
  - LED AZUL -> PIN 7
  - GND -> GND Arduino

- **Módulo RFID RC522**
  - VCC -> 3.3V Arduino
  - GND -> GND Step Down
  - RST -> PIN 9
  - SDA -> PIN 53
  - SCK -> PIN 52
  - MOSI -> PIN 51
  - MISO -> PIN 50

- **Buzzer activo**
  - VCC -> PIN 3
  - GND -> GND Step Down

### 🖨️ El proyecto cuenta con archivos de un modelo de caja a medida para los componenetes del arduino
**Archivos** `/Arduino - Impresión 3D/ Caja Arduino PFC/`

- **CUERPO**
- **TAPA**

| 📚 Librería              | 🔗 Enlace                                                                     | 👤 Autor(es)       | 📌 Función                      | 🛠️ Versión |
| ------------------------ | ----------------------------------------------------------------------------- | ------------------ | ------------------------------- | ----------- |
| SPI                      | [Docs](https://www.arduino.cc/en/Reference/SPI)                               | Arduino Team       | Comunicación SPI                | Incluida    |
| Ethernet                 | [Docs](https://docs.arduino.cc/libraries/ethernet/)                           | Varios             | Administrar red Ethernet        | 2.0.2       |
| LiquidCrystal I2C        | [GitHub](https://github.com/johnrickman/LiquidCrystal_I2C)                    | Frank de Brabander | Control de pantalla LCD I2C     | 1.1.2       |
| MFRC522                  | [GitHub](https://github.com/miguelbalboa/rfid)                                | GitHub Community   | Control de lector RFID          | 1.4.12      |
| avr/wdt (Watchdog Timer) | [Docs](https://www.nongnu.org/avr-libc/user-manual/group__avr__watchdog.html) | Atmel / AVR Libc   | Reseteo automático del sistema  | Incluida    |
| ArduinoJson              | [Web](https://arduinojson.org/)                                               | Benoît Blanchon    | Manejo de datos en formato JSON | 6.x         |


### 🔌 Configuración del Lector RFID  
1. **Conectar el Arduino** a la PC mediante USB.  
2. **Configurar parametros** dentro (`pfc.ino`) configurar conexiónes del sistema.
3. **Subir el código de Arduino** (`pfc.ino`) al microcontrolador.  
4. **Configurar el puerto serie** en el sistema para la comunicación con el lector.  
5. **Conectar por Ethernet** el Arduino a la red del lugar.

---

## 📁 Estructura del Proyecto  
📂 **Directorio principal**  
```
📆 fichaje_pfc
 ├📂 controllers/     # Lógica del sistema
 ├📂 models/          # Acceso a la base de datos
 ├📂 views/           # Interfaz de usuario (HTML, CSS, JS)
 ├📂 public/          # Recursos estáticos (imágenes, CSS, JS)
 ├📝 .env             # Configuración del sistema
 ├📝 fichaje_pfc.sql  # Base de datos
 └📝 README.md        # Documentación
```

---

## 🔒 Licencia  
Este proyecto es **de código abierto** y está bajo la licencia **MIT**. Puedes modificarlo y distribuirlo libremente.  

---

## 📞 Contacto  

Si tienes dudas o sugerencias, puedes contactar a arielmolus25@gmail.com  

--- 
## 🕰️ Ultima modificación: 30-03-2025
