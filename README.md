# 🥋 Palillo Fight Club | Fichaje PFC  
Sistema de control de asistencia para clientes del **Palillo Fight Club**, permitiendo la matriculación en clases.  
Incluye integración con un **lector RFID basado en Arduino**.  

---

## 📌 Requisitos  
### 🛠️ Versiones para el entorno utilizadas:  
- **Base de datos:** MariaDB 10.4.32 o ⬆️
- **Servidor web:** Apache 2.4.58 o ⬆️
- **PHP:** 8.0.0 o ⬆️  

---

## 📝 Instalación en XAMPP (8.2.12)  
Para ejecutar este proyecto, se recomienda instalar **XAMPP 8.2.12** o Superior.  
🔗 [Descargar XAMPP 8.2.12](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.2.12/xampp-windows-x64-8.2.12-0-VS16-installer.exe/download)  

### 🚀 Pasos de Instalación  
1. **Instalar XAMPP** y asegurarse de que **Apache** y **MySQL** estén en ejecución.
2. **Configuración de Apache, PHP y MySQL:**  

   - **Apache (`httpd.conf`)** Configurar las variables de entorno al final del archivo
     ```ini
     SetEnv MYSQL_DB_HOST "localhost"
     SetEnv MYSQL_DB_USER "root"
     SetEnv MYSQL_DB_PASSWORD "Password"
     SetEnv MYSQL_DB_NAME "pfc"
     SetEnv MYSQL_DB_PORT "3306"
     ```

   - **PHP (`php.ini`)** – Configurar la zona horaria:  
     ```ini
     date.timezone = "America/Argentina/Buenos_Aires"
     ```

   - **phpMyAdmin (`config.inc.php`)** – Configurar acceso:  
     ```php
     /* Authentication type and info */
     $cfg['Servers'][$i]['auth_type'] = 'cookie';
     $cfg['Servers'][$i]['user'] = 'root';
     $cfg['Servers'][$i]['password'] = 'Clave';
     $cfg['Servers'][$i]['extension'] = 'mysqli';
     $cfg['Servers'][$i]['AllowNoPassword'] = false;
     ```

   - **MySQL (`my.ini`)** – Configuración de permisos para BD:  
     ```ini
      [mysqld]
      skip-grant-tables  # (Evita la comprobación de permisos)
      port=3306
      socket="C:/xampp/mysql/mysql.sock"
     ```
      O ejecutar la siguiente consulta para otorgar permisos de ingreso a usuarios
     ```sql
      GRANT ALL PRIVILEGES ON *.* TO 'root'@'192.168.X.XX' IDENTIFIED BY 'Usuario';
      FLUSH PRIVILEGES;  # (Otorga permisos de acceso)
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
6. **Acceder al sistema** desde `http://localhost/fichaje_pfc/`.  

---

## 🚢 Instalación en Docker  
🔗 [Descargar Docker Desktop](https://desktop.docker.com/win/main/amd64/Docker%20Desktop%20Installer.exe?utm_source=docker&amp;utm_medium=webreferral&amp;utm_campaign=dd-smartbutton&amp;utm_location=module&amp;_gl=1*58yczi*_gcl_au*MTU5NTI1NzI0NC4xNzQxODA1NjU0*_ga*MTI4NzU4MDE4OC4xNzQxODA1NjU0*_ga_XJWPQMJYHQ*MTc0MTgwNTY1My4xLjEuMTc0MTgwNTY2My41MC4wLjA)  

### 🚀 Pasos de Instalación  
1. **Instalar Docker**, ejecutar el archivo **`docker-compose.yml`** Con la Extensión **`Docker`** en **`VSCode`** o por la consola de **`Docker-Desktop`**.

2. **Clonar el repositorio** dentro de la carpeta de `Docker`.  
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

6. **Acceder al sistema** desde `http:8080//localhost/fichaje_pfc/`. 

## En caso de desarrollo el Docker cuenta con debbuger

1. Obtener la extensión **`PHP Debug`**: 
  ```
  PHP Debug
  ID: xdebug.php-debug
  Descripción: Debug support for PHP with Xdebug
  Versión: 1.35.0
  Editor: Xdebug
  Vínculo de VS Marketplace: https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug
  ```
2. Generar un nuevo **`launch.json`**
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

## 💻 Integración con Lector RFID (Arduino)  
El sistema está diseñado para funcionar con un **lector RFID basado en Arduino**, que permite registrar la asistencia de los usuarios escaneando una tarjeta o llavero RFID.  

### 🔧 Requisitos de Hardware  
- **Arduino Mega** 
- **Ethernet Shield 5100**
- **Pantalla LCD 20X4 con adaptador I2C**
- **4 Leds (Verde - Rojo - Amarillo - Azul)**
- **Módulo RFID RC522**  
- **Buzzer Activo**
- **Adaptador 12V 3A**
- **Step Down 1.25v - 35v 3a**
- **Cooler max 12MM a 12V - 5V**
- **Rejilla Cooler 12MM**
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

### 📆 Librerías de Software  
- Las libreria estan backapeadas dentro de (`Arduino - Impresión 3D/Arduino/Libraries/`)
  En caso de querer utilizarlas copiar las carpetas y volcarlas en la carpeta de `libraries` de su instalación de Arduino IDE

| 📚 Librería | 🔗 Enlace | 👤 Autor(es) | 📌 Funcón | 🛠️ Versión |
|----------------------|------------|-------------|-----------|------------|
| Ethernet | [Docs](https://docs.arduino.cc/libraries/ethernet/) | Varios | Administrar red Ethernet | 2.0.2 |
| ALog | [GitHub](https://github.com/NorthernWidget/ALog) | Andrew Wickert | Registro de datos | 0.3.2 |
| LiquidCrystal I2C | [GitHub](https://github.com/johnrickman/LiquidCrystal_I2C) | Frank de Brabander | Administrar pantalla LCD I2C | 1.1.2 |
| MFRC522 | [GitHub](https://github.com/miguelbalboa/rfid) | GitHub Community | Control lector RFID | 1.4.12 |
| MySQL Connector Arduino | [GitHub](https://github.com/ChuckBell/MySQL_Connector_Arduino/wiki) | Dr. Charles Bell | Conexión con MySQL | 1.2.0 |

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
## 🕰️ Ultima modificación: 12-03-2025
