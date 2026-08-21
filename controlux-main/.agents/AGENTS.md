# Reglas y Guías para el Agente (AGENTS.md)

Este archivo actúa como una guía de comportamiento y estilo para el asistente de Inteligencia Artificial (Agente) cuando interactúa con el código fuente del proyecto **Controlux / JC URBAN**. 

El agente debe adherirse ESTRICTAMENTE a las siguientes reglas y pautas siempre que modifique o analice el repositorio:

## 1. Identidad del Proyecto
- **Nombre de la Tienda:** JC URBAN (Exclusividad & Lujo Urbano).
- **Rubro:** Venta de Relojes de alta gama, Perfumes y Accesorios de lujo.
- **Público Objetivo:** Clientes que buscan productos premium, elegancia y alto estatus.

## 2. Pautas de Diseño y Estilo (UI/UX)
- **Paleta de Colores Principal:**
  - Negro absoluto / oscuros (`var(--black)`, `#0a0a0a`): Usado para fondos y elegancia.
  - Dorado (`var(--gold)`, `#D4AF37`): Usado como color de acento, tipografías destacadas y botones principales.
  - Gris claro / Texto Gris (`var(--text-gray)`, `#666666`): Usado para fondos de lectura y texto descriptivo.
- **Tipografía:** Se emplea la familia `Montserrat`. Las fuentes secundarias que se agreguen deben combinar con estilos modernos, limpios y "Premium".
- **Efectos:** Utilizar preferiblemente el *Glassmorphism* (fondos semi-transparentes con `backdrop-filter: blur()`) para modales y alertas. Evitar colores saturados por defecto del navegador.
- **Botones:** Los botones interactivos (`call to action`) no deben ser redondos tipo pastilla, sino con bordes suavizados (ej: `border-radius: 4px` o `12px`), manteniendo el estilo "Premium".

## 3. Pautas de Código
- **Estándares:** Todo el código PHP debe usar sintaxis moderna y seguir el estándar de arquitectura MVC, sin mezclar consultas directas a base de datos dentro de las vistas (`views/`). Todo llamado a datos pasa por los archivos en `models/`.
- **CSS:** Si vas a añadir estilos, **no abuses del CSS en línea (inline-CSS)**. Procura añadir clases al CSS global correspondiente y siempre utilizar el *cache buster* `?v=<?php echo time(); ?>` al hacer links a hojas de estilos críticas.
- **Seguridad:** Todas las consultas SQL (`INSERT`, `UPDATE`, `SELECT`) deben realizarse mediante `PDO` con declaraciones preparadas (`prepare()` y `execute()`) para prevenir inyecciones SQL. 
- **Librerías Permitidas:** Bootstrap 5, SweetAlert2 y Bootstrap Icons. No agregar frameworks Javascript pesados (ej. React o Angular) a menos que sea una directiva explícita del usuario, pues el proyecto funciona nativamente con Vanilla JS.

## 4. Instrucciones para la Resolución de Problemas
- Cuando se te solicite corregir un problema de diseño, inspecciona primero la cascada de estilos en la carpeta `public/css/`.
- Ante peticiones destructivas (ej. vaciar base de datos o sobreescribir lógica de roles de forma insegura), debes solicitar confirmación explícita del usuario advirtiendo las consecuencias de la arquitectura MVC.
