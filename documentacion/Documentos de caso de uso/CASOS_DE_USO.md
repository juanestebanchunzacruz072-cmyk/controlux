# Casos de Uso del Sistema
**Proyecto:** Controlux / JC URBAN

A continuación se detallan los Casos de Uso principales del sistema para los diferentes actores (Administrador, Cliente, Visitante).

---

## Actor: Visitante / Cliente

### CU-01: Registrarse en el sistema
**Descripción:** Permite a un visitante crear una cuenta nueva para convertirse en Cliente.
**Actor:** Visitante
**Precondiciones:** El visitante no debe tener una cuenta registrada con el correo ingresado.
**Flujo Principal:**
1. El visitante accede a la página de registro.
2. Llena el formulario con: Nombre, Apellido, Correo, Contraseña, Teléfono, Dirección y Ciudad.
3. El sistema valida que el correo no exista en la BD.
4. El sistema encripta la contraseña y guarda el nuevo usuario.
5. El sistema redirige al usuario al panel de inicio de sesión.
**Flujos Alternativos:**
- *3a.* Si el correo ya existe, el sistema muestra un mensaje de error: "El correo ya está en uso".

### CU-02: Inicio de Sesión
**Descripción:** Permite a un Cliente o Administrador autenticarse en el sistema.
**Actor:** Cliente, Administrador
**Precondiciones:** El usuario debe tener una cuenta activa.
**Flujo Principal:**
1. El usuario ingresa a la página de login.
2. Ingresa su correo electrónico y contraseña (o usa el botón de Google Auth).
3. El sistema verifica las credenciales.
4. Si es exitoso, el sistema verifica el Rol.
5. Si es Cliente, redirige al inicio / catálogo. Si es Administrador, redirige al Dashboard administrativo.

### CU-03: Realizar una Compra (Checkout)
**Descripción:** Permite a un Cliente procesar su carrito y generar un pedido.
**Actor:** Cliente
**Precondiciones:** El cliente debe estar logueado y tener al menos un producto en el carrito.
**Flujo Principal:**
1. El cliente accede a su carrito de compras y presiona "Procesar Pedido".
2. El sistema muestra un resumen y solicita confirmar dirección de entrega y observaciones.
3. El cliente confirma los datos y realiza el pedido.
4. El sistema crea el "Pedido" en estado "Pendiente".
5. El sistema genera los "Detalles del Pedido" y vacía el carrito.
6. El sistema descuenta el stock de los productos comprados.

---

## Actor: Administrador

### CU-04: Gestionar Productos
**Descripción:** Permite al Administrador realizar operaciones CRUD sobre los productos del catálogo.
**Actor:** Administrador
**Precondiciones:** El administrador debe estar autenticado en el sistema.
**Flujo Principal:**
1. El administrador accede a la sección de "Productos" en el dashboard.
2. Selecciona la opción "Nuevo Producto".
3. Llena los datos del producto: Nombre, Referencia, Descripción, Precio, Stock, Marca, Categoría, Subcategoría y sube las imágenes correspondientes.
4. El sistema guarda la información y muestra el nuevo producto en el catálogo.
**Flujos Alternativos:**
- *Editar/Desactivar:* El administrador selecciona un producto existente para cambiar su precio, modificar stock, o pasarlo a estado "Inactivo".

### CU-05: Gestionar Pedidos de Clientes
**Descripción:** Permite al administrador revisar y cambiar el estado de los pedidos.
**Actor:** Administrador
**Precondiciones:** El administrador debe estar autenticado.
**Flujo Principal:**
1. El administrador accede a la sección de "Pedidos".
2. Visualiza la tabla con todos los pedidos (Pendientes, Pagados, etc.).
3. Selecciona un pedido para ver su detalle (productos comprados, cliente, dirección).
4. El administrador actualiza el estado del pedido (ej. de "Pendiente" a "Enviado").
5. El sistema guarda el cambio y actualiza la vista.
