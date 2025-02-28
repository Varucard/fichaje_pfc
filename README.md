# Palillo Fight Club | fichaje_pfc  
Sistema de control de asistencia para clientes del **Palillo Fight Club**, permitiendo la matriculación en clases.  
Incluye integración con un **lector RFID basado en Arduino**.  

## 📌 Implementación en XAMPP (versión 7.4.18)  

### 🛠️ Versiones utilizadas:
- **Base de datos:** MariaDB 10.4.32  
- **Servidor web:** Apache 2.4.58  
- **PHP:** 8.0.30  

### 📥 Instalación de XAMPP  
Para ejecutar este proyecto, se recomienda instalar **XAMPP 7.4.18**. Puedes descargarlo desde el siguiente enlace:  
🔗 [Descargar XAMPP (7.4.18)](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/7.4.18/xampp-windows-x64-7.4.18-0-VC15-installer.exe/download)  

## 🚀 Instalación y Configuración  
1. Instalar XAMPP y asegurarse de que **Apache** y **MySQL** estén en ejecución.  
2. Clonar este repositorio dentro de la carpeta `htdocs`.  
3. Importar la base de datos `fichaje_pfc.sql` en **phpMyAdmin**.  
4. Configurar la conexión a la base de datos en `config/database.php`.  
5. Acceder al sistema desde el navegador en `http://localhost/fichaje_pfc/`.  

## 📡 Integración con Lector RFID (Arduino)  
El sistema está diseñado para funcionar con un **lector RFID basado en Arduino**, que permite registrar la asistencia de los usuarios escaneando una tarjeta o llavero RFID.  

### 🔧 Requisitos de Hardware  
- **Arduino Uno / Mega**  
- **Módulo RFID RC522**  
- **Conexión USB a la PC**  

### 🔌 Configuración del Lector RFID  
1. Subir el código de Arduino (`rfid_reader.ino`) al microcontrolador.  
2. Conectar el Arduino a la PC mediante USB.  
3. Configurar el puerto serie en el sistema para la comunicación con el lector.  

## 📁 Estructura del Proyecto  
- `controllers/` → Contiene los archivos de lógica para cada acción.  
- `models/` → Definición de la base de datos y acceso a datos.  
- `views/` → Archivos de interfaz de usuario (HTML, CSS, JS).  
- `public/` → Recursos estáticos como imágenes, CSS y JS.  

## 🔓 Licencia  
Este proyecto es **de código abierto** y utiliza herramientas de la comunidad. Puedes modificarlo y distribuirlo libremente bajo la licencia **MIT**.  

---

