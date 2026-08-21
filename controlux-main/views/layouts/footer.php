<!-- Custom Footer CSS -->
<link href="/controlux/public/css/style_footer.css" rel="stylesheet">

<!-- Footer -->
<footer class="bg-black text-white pt-5 pb-4 mt-5 footer-bg">
    <div class="container text-center text-md-start">
        <div class="row text-center text-md-start">
            <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 font-weight-bold footer-heading">JC URBAN</h5>
                <p>Somos una tienda 100% colombiana y confiable, dedicada a la venta de relojes, perfumes y accesorios de alta calidad.</p>
            </div>

            <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 font-weight-bold footer-heading">Enlaces</h5>
                <p><a href="https://maps.app.goo.gl/gvGxG5dyW6M7PeZR6" class="text-white text-decoration-none footer-link">Ubicacion</a></p>
                <p><a href="mailto:jc.urban.2007@gmail.com" class="text-white text-decoration-none footer-link">Correo</a></p>
                <p><a href="https://wa.me/573212327275" class="text-white text-decoration-none footer-link">Contacto</a></p>
            </div>

            <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 font-weight-bold footer-heading">Contacto</h5>
                <p><i class="bi bi-house-door-fill me-3"></i> Neiva - Huila, Colombia</p>
                <p><i class="bi bi-envelope-fill me-3"></i> jc.urban.2007@gmail.com</p>
                <p><i class="bi bi-telephone-fill me-3"></i> +57 3212327275</p>
            </div>
        </div>
        <hr class="mb-4 footer-divider">
        <div class="row align-items-center">
            <div class="col-md-7 col-lg-8">
                <p> Copyright ©2026 Todos los derechos reservados por:
                    <a href="#" style="text-decoration: none;">
                        <strong class="footer-brand">Juan Esteban Chunza Cruz</strong>
                    </a>
                </p>
            </div>
            <div class="col-md-5 col-lg-4">
                <div class="text-center text-md-end">
                    <ul class="list-unstyled list-inline">
                        <li class="list-inline-item">
                            <a href="https://www.instagram.com/juan_chunza07?igsh=MTJpYTh0cjM2dTY3MQ==" class="text-white fs-4 footer-link"><i class="bi bi-instagram"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="https://www.tiktok.com/@juanchunza07?_r=1&_t=ZS-98v2uDPsasm" class="text-white fs-4 footer-link"><i class="bi bi-tiktok"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Modal Detalles del Producto -->
<div class="modal fade" id="productoModal" tabindex="-1" aria-labelledby="productoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius: 12px; border: 2px solid var(--gold-premium);">
      <div class="modal-header bg-black text-white" style="border-bottom: 1px solid var(--gold-premium);">
        <h5 class="modal-title fw-bold" id="productoModalLabel" style="color: var(--gold-premium); font-family: 'Montserrat', sans-serif;">Detalles del Producto</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-5 d-flex justify-content-center align-items-center mb-3 mb-md-0">
            <img src="" id="modalProdImg" class="img-fluid rounded" alt="Producto" style="max-height: 350px; object-fit: contain;">
          </div>
          <div class="col-md-7">
            <h3 id="modalProdName" class="fw-bold mb-3" style="font-family: 'Montserrat', sans-serif;">Nombre</h3>
            <h4 id="modalProdPrice" class="mb-3 fw-bold" style="color: var(--gold-premium); font-family: 'Montserrat', sans-serif;">$0</h4>
            <div class="mb-3">
                <span class="badge bg-dark me-2">Marca: <span id="modalProdBrand"></span></span>
                <span class="badge bg-secondary">Stock: <span id="modalProdStock"></span></span>
            </div>
            <p id="modalProdDesc" class="text-muted" style="font-size: 0.95rem; line-height: 1.6;"></p>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="border-top: none;">
        <button type="button" class="btn btn-outline-dark fw-bold" data-bs-dismiss="modal">Volver</button>
        <button type="button" class="btn btn-gold fw-bold" id="modalAddToCartBtn">
            <i class="bi bi-cart-plus me-2"></i> Agregar al carrito
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar apertura del modal
    document.querySelectorAll('.open-product-modal').forEach(function(element) {
        element.addEventListener('click', function(e) {
            // Evitar si se hizo clic directamente en el botón de agregar al carrito
            if (e.target.closest('.add-to-cart-btn')) return;
            
            const card = this.closest('.product-card');
            if(!card) return;
            
            const btn = card.querySelector('.add-to-cart-btn');
            if(!btn && parseInt(this.getAttribute('data-stock') || '0') > 0) return;
            
            // Extraer info
            const id = btn ? btn.getAttribute('data-id') : '';
            const name = btn ? btn.getAttribute('data-name') : this.querySelector('.card-title').textContent;
            const price = btn ? btn.getAttribute('data-price') : 0;
            const stock = btn ? btn.getAttribute('data-stock') : 0;
            const img = btn ? btn.getAttribute('data-img') : this.querySelector('img').src;
            const desc = this.getAttribute('data-desc') || 'Sin descripción';
            const brand = this.getAttribute('data-brand') || 'Desconocida';
            
            // Llenar modal
            document.getElementById('modalProdName').textContent = name;
            document.getElementById('modalProdPrice').textContent = '$ ' + new Intl.NumberFormat('es-CO').format(price);
            document.getElementById('modalProdImg').src = img;
            document.getElementById('modalProdDesc').textContent = desc;
            document.getElementById('modalProdBrand').textContent = brand;
            document.getElementById('modalProdStock').textContent = stock;
            
            // Configurar botón de carrito del modal
            const modalBtn = document.getElementById('modalAddToCartBtn');
            if(btn) {
                modalBtn.setAttribute('data-id', id);
                modalBtn.setAttribute('data-name', name);
                modalBtn.setAttribute('data-price', price);
                modalBtn.setAttribute('data-stock', stock);
                modalBtn.setAttribute('data-img', img);
                modalBtn.classList.add('add-to-cart-btn');
                
                if(parseInt(stock) <= 0) {
                    modalBtn.disabled = true;
                    modalBtn.classList.remove('btn-gold');
                    modalBtn.classList.add('btn-secondary');
                    modalBtn.innerHTML = '<i class="bi bi-x-circle me-2"></i>Agotado';
                } else {
                    modalBtn.disabled = false;
                    modalBtn.classList.remove('btn-secondary');
                    modalBtn.classList.add('btn-gold');
                    modalBtn.innerHTML = '<i class="bi bi-cart-plus me-2"></i>Agregar al carrito';
                }
            } else {
                modalBtn.style.display = 'none';
            }
            
            // Mostrar modal
            const myModal = new bootstrap.Modal(document.getElementById('productoModal'));
            myModal.show();
        });
    });
});
</script>

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
