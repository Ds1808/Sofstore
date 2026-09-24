// Funciones globales de ayuda visual y formateo
function formatCOP(monto) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0
    }).format(monto);
}