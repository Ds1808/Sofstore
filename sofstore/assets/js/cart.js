// Obtener carrito del localStorage
function getCart() {
    return JSON.parse(localStorage.getItem('sofstore_cart')) || [];
}

// Guardar carrito
function saveCart(cart) {
    localStorage.setItem('sofstore_cart', JSON.stringify(cart));
    updateCartCounter();
}

// Actualizar el contador del carrito en el footer
function updateCartCounter() {
    const cart = getCart();
    const totalItems = cart.reduce((acc, item) => acc + item.cantidad, 0);
    const counterBadge = document.getElementById('cart-counter');

    if (counterBadge) {
        if (totalItems > 0) {
            counterBadge.textContent = totalItems;
            counterBadge.style.display = 'inline-block';
        } else {
            counterBadge.style.display = 'none';
        }
    }
}

// Evento para añadir al carrito desde el catálogo
document.addEventListener('DOMContentLoaded', () => {
    updateCartCounter();

    document.querySelectorAll('.btn-add-cart').forEach(button => {
        button.addEventListener('click', function() {
            const id = parseInt(this.dataset.id);
            const nombre = this.dataset.nombre;
            const precio = parseFloat(this.dataset.precio);

            let cart = getCart();
            const existingIndex = cart.findIndex(item => item.id === id);

            if (existingIndex > -1) {
                cart[existingIndex].cantidad += 1;
            } else {
                cart.push({ id, nombre, precio, cantidad: 1 });
            }

            saveCart(cart);

            // Animación visual rápida en el botón
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fa-solid fa-check"></i> ¡Añadido!';
            this.style.backgroundColor = '#1D70B8';
            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.backgroundColor = '';
            }, 1200);
        });
    });
});