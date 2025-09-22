# Banco de hojas de vidas egresados

## Descripción
Este proyecto permite registrar egresados mediante un formulario web, almacenando sus datos y hoja de vida en una base de datos MySQL. El backend está desarrollado en PHP y el frontend en HTML/JavaScript.

## Estructura principal
- `index.html`: Formulario de registro de egresados.
- `script.js`: Lógica de validación y envío del formulario.
- `src/guardar_estudiante.php`: Recibe los datos del formulario y los guarda en la base de datos.
- `src/config.php` y `src/db.php`: Configuración y conexión a la base de datos.

## Explicación del flujo
1. El usuario llena el formulario con sus datos y adjunta su hoja de vida (PDF/DOC/DOCX).
2. Al enviar, el frontend valida los campos y envía los datos al backend (`guardar_estudiante.php`) usando POST.
3. El backend recibe los datos, valida y sube el archivo a Cloudinary (si se adjunta), luego guarda la información en la base de datos MySQL.
4. Si todo es correcto, el registro queda almacenado y el usuario recibe confirmación.

## Creación de la tabla en MySQL
Para que el sistema funcione correctamente, debes crear la tabla `estudiantes` con la siguiente estructura:

```sql
CREATE TABLE estudiantes (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre_apellido VARCHAR(150) NOT NULL,
    anio_programa INT NOT NULL,
    cedula VARCHAR(30) NOT NULL,
    referencias TEXT NOT NULL,
    url_archivo VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Puedes ejecutar este código en phpMyAdmin, MySQL Workbench o la terminal de MySQL.

## Recomendaciones
- Verifica que la configuración de la base de datos en `config.php` coincida con el nombre real de la base de datos.
- Si usas WordPress, este sistema puede adaptarse como un plugin personalizado, pero requiere integración especial.
- Si tienes errores de "columna desconocida" o "error de red", revisa que los nombres de los campos en el formulario y la base de datos coincidan.

## Contacto y soporte
Si necesitas agregar más campos o adaptar el sistema, puedes modificar el SQL y el backend según tus necesidades.
