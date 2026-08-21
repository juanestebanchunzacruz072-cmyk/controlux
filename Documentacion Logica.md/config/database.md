# Documentación: `config/database.php`

**Propósito Principal:** 
Establecer la conexión única y centralizada con la base de datos MySQL de JC URBAN.

**¿Qué hace este archivo?**
- Define las credenciales de acceso (localhost, usuario root, etc.).
- Utiliza **PDO** para crear la conexión, lo cual protege a la tienda contra inyecciones SQL usando consultas preparadas.
- Configura el juego de caracteres a `UTF-8` para que no haya problemas con tildes o caracteres especiales en los nombres de los productos.
- Configura el sistema de errores para que lance "Excepciones" (`ERRMODE_EXCEPTION`) si alguna consulta SQL falla, permitiendo que el resto del sistema pueda atrapar el error sin mostrar pantallas rotas al cliente.
