<!-- Cart Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel">
    <div class="offcanvas-header d-flex justify-content-between align-items-center">
        <h6 class="offcanvas-title mb-0" id="cartOffcanvasLabel" style="font-weight: 800; letter-spacing: 1px;">
            <i class="bi bi-cart3 me-2"></i>TU CARRITO
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column bg-white">
        <!-- Contenedor de items del carrito -->
        <div id="cart-items" class="flex-grow-1 overflow-auto pe-2">
            <!-- Los items del carrito se cargarán aquí dinámicamente -->
        </div>

        <!-- Sección También te puede gustar -->
        <?php
        require_once __DIR__ . '/../../models/Producto.php';
        try {
            $modeloProdCart = new Producto();
            $recomendaciones = $modeloProdCart->obtenerRecomendaciones(4);
        } catch (Exception $e) {
            $recomendaciones = [];
        }
        ?>
        <?php if (!empty($recomendaciones)): ?>
        <div class="mt-4 pt-3 border-top border-secondary-subtle">
            <h6 class="recommendations-title">TAMBIÉN TE PUEDE GUSTAR</h6>
            <div class="d-flex overflow-auto pb-2 recommendations-scroll" style="-webkit-overflow-scrolling: touch;">
                <?php foreach($recomendaciones as $rec): ?>
                <div class="recommendation-card">
                    <img src="/controlux/<?php echo htmlspecialchars(ltrim($rec['img'] ?? 'img/accesorio.png', '/')); ?>" alt="<?php echo htmlspecialchars($rec['nombre']); ?>" class="recommendation-img">
                    <div class="recommendation-name"><?php echo htmlspecialchars($rec['nombre']); ?></div>
                    <button class="btn-add-recommendation add-to-cart-btn" 
                            data-id="<?php echo $rec['id_producto']; ?>" 
                            data-name="<?php echo htmlspecialchars($rec['nombre']); ?>" 
                            data-price="<?php echo $rec['precio']; ?>" 
                            data-stock="<?php echo $rec['stock']; ?>" 
                            data-img="/controlux/<?php echo htmlspecialchars(ltrim($rec['img'] ?? 'img/accesorio.png', '/')); ?>">
                        Añadir
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Resumen del carrito -->
        <div class="cart-footer-summary mt-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="cart-total-label">Total:</span>
                <span class="cart-total-amount">$ <span id="cart-total-final">8.680.000</span></span>
            </div>
            <button id="btn-comprar" class="btn btn-checkout-cart"> 
                COMPRAR
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadCartFromStorage();
        updateCartTotal();
        updateCartBadge();

        const cartItemsContainer = document.getElementById('cart-items');
        
        if(cartItemsContainer) {
            cartItemsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.btn-increase')) {
                    const item = e.target.closest('.cart-item');
                    const input = item.querySelector('.item-quantity');
                    const maxStock = parseInt(item.getAttribute('data-stock')) || Infinity;
                    let qty = parseInt(input.value);
                    if (qty < maxStock) {
                        input.value = qty + 1;
                        updateItemTotal(item);
                        updateCartTotal();
                    } else {
                        alert('No hay más stock disponible de este producto.');
                    }
                }
                
                // Botón decrementar
                if (e.target.closest('.btn-decrease')) {
                    const item = e.target.closest('.cart-item');
                    const input = item.querySelector('.item-quantity');
                    let qty = parseInt(input.value);
                    if (qty > 1) {
                        input.value = qty - 1;
                        updateItemTotal(item);
                        updateCartTotal();
                    }
                }
                
                // Botón eliminar
                if (e.target.closest('.btn-remove-item')) {
                    const item = e.target.closest('.cart-item');
                    item.style.transition = "opacity 0.3s ease, height 0.3s ease, padding 0.3s ease, margin 0.3s ease";
                    item.style.opacity = "0";
                    item.style.height = "0px";
                    item.style.paddingTop = "0";
                    item.style.paddingBottom = "0";
                    item.style.marginTop = "0";
                    item.style.marginBottom = "0";
                    item.style.overflow = "hidden";
                    
                    setTimeout(() => {
                        item.remove();
                        updateCartTotal();
                        updateCartBadge();
                        saveCartToStorage();
                    }, 300);
                }
            });
        }

        function saveCartToStorage() {
            const items = [];
            document.querySelectorAll('.cart-item').forEach(item => {
                items.push({
                    id: parseInt(item.getAttribute('data-id')),
                    name: item.querySelector('.cart-item-title').innerText,
                    price: parseFloat(item.getAttribute('data-price')),
                    quantity: parseInt(item.querySelector('.item-quantity').value),
                    stock: parseInt(item.getAttribute('data-stock')) || Infinity,
                    img: item.querySelector('img').getAttribute('src')
                });
            });
            localStorage.setItem('controlux_cart', JSON.stringify(items));
        }

        function loadCartFromStorage() {
            const stored = localStorage.getItem('controlux_cart');
            if (stored) {
                const items = JSON.parse(stored);
                const cartItems = document.getElementById('cart-items');
                if (!cartItems) return;
                
                cartItems.innerHTML = '';
                items.forEach(item => {
                    const itemHTML = `
                        <div class="cart-item d-flex align-items-center mb-3 border-bottom pb-2" data-id="${item.id}" data-price="${item.price}" data-stock="${item.stock}">
                            <img src="${item.img}" alt="${item.name}" style="width: 60px; height: 60px; object-fit: contain;" class="me-3">
                            <div class="flex-grow-1">
                                <div class="cart-item-title fw-bold" style="font-size: 0.9rem;">${item.name}</div>
                                <div class="cart-item-price" style="color: var(--gold-premium, #D4AF37); font-weight: 600;">$ <span class="item-total-display">${formatCurrency(item.price * item.quantity)}</span></div>
                            </div>
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-decrease">-</button>
                                <input type="number" class="form-control form-control-sm text-center item-quantity mx-1" value="${item.quantity}" min="1" style="width: 45px;" readonly>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-increase">+</button>
                            </div>
                            <button type="button" class="btn btn-sm text-danger btn-remove-item ms-2"><i class="bi bi-trash"></i></button>
                        </div>
                    `;
                    cartItems.insertAdjacentHTML('beforeend', itemHTML);
                });
            }
        }

        function formatCurrency(number) {
            return new Intl.NumberFormat('es-CO').format(number);
        }

        function updateItemTotal(item) {
            const price = parseFloat(item.getAttribute('data-price'));
            const quantity = parseInt(item.querySelector('.item-quantity').value);
            const totalDisplay = item.querySelector('.item-total-display');
            if(totalDisplay) {
                totalDisplay.textContent = formatCurrency(price * quantity);
            }
        }

        function updateCartTotal() {
            let total = 0;
            const items = document.querySelectorAll('.cart-item');
            
            items.forEach(item => {
                const price = parseFloat(item.getAttribute('data-price'));
                const quantity = parseInt(item.querySelector('.item-quantity').value);
                total += price * quantity;
                updateItemTotal(item);
            });
            
            const totalFinal = document.getElementById('cart-total-final');
            if(totalFinal) {
                totalFinal.textContent = formatCurrency(total);
            }
            saveCartToStorage();
        }

        function updateCartBadge() {
            const itemsCount = document.querySelectorAll('.cart-item').length;
            const badge = document.getElementById('cart-badge-count');
            if(badge) {
                badge.textContent = itemsCount;
                if(itemsCount === 0) {
                    badge.style.display = 'none';
                } else {
                    badge.style.display = 'inline-block';
                }
            }
        }

        const btnComprar = document.getElementById('btn-comprar');
        
        // --- NUEVA LÓGICA: Añadir producto al carrito desde los botones ---
        document.body.addEventListener('click', function(e) {
            if (e.target.closest('.add-to-cart-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.add-to-cart-btn');
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const price = parseFloat(btn.getAttribute('data-price'));
                const stock = parseInt(btn.getAttribute('data-stock')) || Infinity;
                const img = btn.getAttribute('data-img');

                // Verificar si ya está en el carrito
                const cartItems = document.getElementById('cart-items');
                const existingItem = cartItems.querySelector(`.cart-item[data-id="${id}"]`);
                
                if (existingItem) {
                    const qtyInput = existingItem.querySelector('.item-quantity');
                    let newQty = parseInt(qtyInput.value) + 1;
                    if (newQty <= stock) {
                        qtyInput.value = newQty;
                        updateItemTotal(existingItem);
                    } else {
                        alert('No hay más stock disponible de este producto.');
                        return; // Opcional: no abrir el carrito si no se pudo añadir
                    }
                } else {
                    // Agregar nuevo item
                    const itemHTML = `
                        <div class="cart-item d-flex align-items-center mb-3 border-bottom pb-2" data-id="${id}" data-price="${price}" data-stock="${stock}">
                            <img src="${img}" alt="${name}" style="width: 60px; height: 60px; object-fit: contain;" class="me-3">
                            <div class="flex-grow-1">
                                <div class="cart-item-title fw-bold" style="font-size: 0.9rem;">${name}</div>
                                <div class="cart-item-price" style="color: var(--gold-premium, #D4AF37); font-weight: 600;">$ <span class="item-total-display">${formatCurrency(price)}</span></div>
                            </div>
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-decrease">-</button>
                                <input type="number" class="form-control form-control-sm text-center item-quantity mx-1" value="1" min="1" style="width: 45px;" readonly>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-increase">+</button>
                            </div>
                            <button type="button" class="btn btn-sm text-danger btn-remove-item ms-2"><i class="bi bi-trash"></i></button>
                        </div>
                    `;
                    cartItems.insertAdjacentHTML('beforeend', itemHTML);
                }
                
                updateCartTotal();
                updateCartBadge();
                
                // Abrir el carrito
                const cartEl = document.getElementById('cartOffcanvas');
                const cartOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(cartEl);
                cartOffcanvas.show();
            }
        });
        
        if(btnComprar) {
            btnComprar.addEventListener('click', function(e) {
                e.preventDefault();
                
                const items = [];
                document.querySelectorAll('.cart-item').forEach(item => {
                    items.push({
                        id: parseInt(item.getAttribute('data-id')),
                        name: item.querySelector('.cart-item-title').innerText,
                        price: parseFloat(item.getAttribute('data-price')),
                        quantity: parseInt(item.querySelector('.item-quantity').value),
                        img: item.querySelector('img').getAttribute('src')
                    });
                });
                
                if(items.length === 0) {
                    alert('El carrito está vacío.');
                    return;
                }

                const originalText = btnComprar.innerText;
                btnComprar.innerText = 'PROCESANDO...';
                btnComprar.disabled = true;

                fetch('/controlux/controllers/cliente/CarritoController.php?accion=guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(items)
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'logged_in' || data.status === 'not_logged') {
                        window.location.href = data.redirect;
                    } else {
                        alert('Error al procesar el carrito.');
                        btnComprar.innerText = originalText;
                        btnComprar.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Ocurrió un error.');
                    btnComprar.innerText = originalText;
                    btnComprar.disabled = false;
                });
            });
        }
    });
</script>
