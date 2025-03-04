# 🥋 Palillo Fight Club | Fichaje PFC  
Sistema de control de asistencia para clientes del **Palillo Fight Club**, permitiendo la matriculación en clases.  
Incluye integración con un **lector RFID basado en Arduino**.  

---

## 📌 Requisitos  
### 🛠️ Versiones para el entorno utilizadas:  
- **Base de datos:** MariaDB 10.4.32  
- **Servidor web:** Apache 2.4.58  
- **PHP:** 8.0.30  

---

## 📝 Instalación en XAMPP (7.4.18)  
Para ejecutar este proyecto, se recomienda instalar **XAMPP 7.4.18**.  
🔗 [Descargar XAMPP 7.4.18](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/7.4.18/xampp-windows-x64-7.4.18-0-VC15-installer.exe/download)  

### 🚀 Pasos de Instalación  
1. **Instalar XAMPP** y asegurarse de que **Apache** y **MySQL** estén en ejecución.
2. **Configuración de Apache, PHP y MySQL:**  

   - **Apache (`httpd.conf`)** Configurar variables de entorno 
     ```ini
     SetEnv MYSQL_DB_HOST "localhost"
     SetEnv MYSQL_DB_USER "root"
     SetEnv MYSQL_DB_PASSWORD "Mercedes"
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

   - **MySQL (`my.ini`)** – Configuración de permisos:  
     ```ini
     skip-grant-tables  # (Evita la comprobación de permisos)
     ```
      O ejecutar la siguiente consulta para otorgar permisos de ingreso a usuarios
     ```sql
      GRANT ALL PRIVILEGES ON *.* TO 'root'@'192.168.1.XX' IDENTIFIED BY 'Usuario';
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

## 💻 Integración con Lector RFID (Arduino)  
El sistema está diseñado para funcionar con un **lector RFID basado en Arduino**, que permite registrar la asistencia de los usuarios escaneando una tarjeta o llavero RFID.  

### 🔧 Requisitos de Hardware  
- **Arduino Uno / Mega** 
- **Pantalla LCD 20X4 con adaptador I2C**
- **Ethernet Shield 5100**
- **Leds**
- **Módulo RFID RC522**  
- **Conexión USB a la PC** 
- **Cable Ethernet**

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