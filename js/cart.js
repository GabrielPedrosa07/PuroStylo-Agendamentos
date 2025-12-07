// Cart Logic
let cart = [];
try {
    cart = JSON.parse(localStorage.getItem('minha_loja_cart')) || [];
} catch (e) {
    console.error("Cart storage corrupted, resetting:", e);
    localStorage.removeItem('minha_loja_cart');
    cart = [];
}

$(document).ready(function () {
    updateCartCount();
});

// Global handler for HTML onclick
function handleAddToCart(element, event) {
    if (event) event.preventDefault();

    let id = $(element).data('id');
    let name = $(element).data('name');
    let price = parseFloat($(element).data('price'));
    let profTel = $(element).data('prof-tel');
    let profName = $(element).data('prof-name');

    // Debug
    console.log("Handling Add to Cart:", name);

    addItemToCart(id, name, price, profTel, profName);
}

function addItemToCart(id, name, price, profTel, profName) {
    let item = {
        id: id,
        name: name,
        price: price,
        profTel: profTel,
        profName: profName,
        qty: 1
    };

    // Check if item exists (optional: increment qty)
    // For simplicity, we just push valid new items, or we can handle qty
    cart.push(item);

    saveCart();
    updateCartCount();

    // Optional: Visual feedback
    alert(name + " adicionado ao carrinho!");
}

function removeItem(index) {
    cart.splice(index, 1);
    saveCart();
    renderCart(); // Re-render modal
    updateCartCount();
}

function saveCart() {
    localStorage.setItem('minha_loja_cart', JSON.stringify(cart));
}

function updateCartCount() {
    $('#cart-count').text(cart.length);
}

function renderCart() {
    let container = $('#cart-body');
    container.empty();

    if (cart.length === 0) {
        container.html('<p class="text-center">Seu carrinho está vazio.</p>');
        return;
    }

    // Group items by Professional (using profTel as key)
    let groups = {};

    cart.forEach((item, index) => {
        let key = item.profTel + '|' + item.profName;
        if (!groups[key]) {
            groups[key] = {
                tel: item.profTel,
                name: item.profName,
                items: [],
                total: 0
            };
        }
        // Add original index to item for removal
        item.originalIndex = index;
        groups[key].items.push(item);
        groups[key].total += item.price;
    });

    // Render each group
    for (let key in groups) {
        let group = groups[key];

        let groupHtml = `
            <div class="card mb-3">
                <div class="card-header bg-dark text-white">
                    Itens de: <strong>${group.name}</strong>
                </div>
                <div class="card-body">
                    <ul class="list-group mb-3">
        `;

        group.items.forEach(item => {
            groupHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    ${item.name} - R$ ${item.price.toFixed(2).replace('.', ',')}
                    <button class="btn btn-sm btn-danger" onclick="removeItem(${item.originalIndex})">X</button>
                </li>
            `;
        });

        groupHtml += `
                    </ul>
                    <h5 class="text-right">Total: R$ ${group.total.toFixed(2).replace('.', ',')}</h5>
                    <button class="btn btn-success btn-block" onclick="checkout('${group.tel}', '${encodeURIComponent(JSON.stringify(group.items))}')">
                        <i class="fa fa-whatsapp"></i> Finalizar Pedido com ${group.name}
                    </button>
                </div>
            </div>
        `;

        container.append(groupHtml);
    }
}

function checkout(tel, itemsJson) {
    let items = JSON.parse(decodeURIComponent(itemsJson));
    let message = "Olá, gostaria de fazer o seguinte pedido:\n\n";
    let total = 0;

    items.forEach(item => {
        message += `- ${item.name}: R$ ${item.price.toFixed(2).replace('.', ',')}\n`;
        total += item.price;
    });

    message += `\n*Total: R$ ${total.toFixed(2).replace('.', ',')}*`;

    let url = `https://api.whatsapp.com/send?phone=${tel}&text=${encodeURIComponent(message)}`;
    window.open(url, '_blank');
}
