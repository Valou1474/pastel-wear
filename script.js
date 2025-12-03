// === Liste de produits ===
let products = [];

// Fonction utilitaire pour construire le bon chemin d'image
function buildImagePath(raw) {
    if (!raw || raw.trim() === "") {
        return "img/hero.jpg"; // image par défaut
    }
    raw = raw.trim();

    // Si l'admin a déjà mis un chemin complet, on garde tel quel
    if (
        raw.startsWith("http://") ||
        raw.startsWith("https://") ||
        raw.startsWith("img/") ||
        raw.startsWith("/")
    ) {
        return raw;
    }

    // Sinon on considère que c'est juste le nom du fichier
    return "img/" + raw;
}

// === Construire la liste de produits depuis la BDD ===
if (typeof productsFromDb !== "undefined" && Array.isArray(productsFromDb) && productsFromDb.length > 0) {
    products = productsFromDb.map(p => ({
        id: parseInt(p.id, 10),
        name: p.name,
        desc: p.description || "",
        price: parseFloat(p.price),
        badge: "Nouvel article",
        image: buildImagePath(p.image),
        stock: typeof p.stock !== "undefined" ? parseInt(p.stock, 10) : null

    }));
} else {
    // Fallback si la BDD est vide
    products = [
        {
            id: 1,
            name: "The Work Jacket",
            desc: "100 % coton, coupe ample pour le confort.",
            price: 120,
            badge: "Best-seller",
            image: "img/work-jacket.jpg",
            stock: 2
        },
        {
            id: 2,
            name: "Ensemble en lin",
            desc: "Un ensemble en lin frais aux tons pastel.",
            price: 100,
            badge: "Nouveau",
            image: "img/ensemble-lin.jpg",
            stock: 5
        },
        {
            id: 3,
            name: "Short pastel",
            desc: "Short taille haute, parfait pour l'été.",
            price: 60,
            badge: "Collection été",
            image: "img/short-pastel.jpg",
            stock: 60
        },
        {
            id: 4,
            name: "Chemise oversize",
            desc: "Chemise oversize ultra confortable.",
            price: 80,
            badge: "Confort",
            image: "img/chemise-oversize.jpg",
            stock: 100
        }
    ];
}


// === Etat du panier ===
let cart = [];


// === Affichage des produits ===
function renderProducts() {
    const grid = document.getElementById("products-grid");
    grid.innerHTML = "";

    products.forEach((p) => {

        const stockText = p.stock > 0
            ? `En stock : ${p.stock}`
            : `Rupture de stock`;

        const disabledAttr = p.stock <= 0 ? "disabled" : "";
        const disabledClass = p.stock <= 0 ? "btn-disabled" : "";

        const card = document.createElement("article");
        card.className = "product-card";

        card.innerHTML = `
            <div class="product-image-wrap">
                <img src="${p.image}" alt="${p.name}">
            </div>

            <div class="product-info">
                <div class="product-badge">${p.badge}</div>
                <h3 class="product-title">${p.name}</h3>
                <p class="product-desc">${p.desc}</p>

                <div class="product-bottom">
                    <span class="product-price">${p.price.toFixed(2)} €</span>
                    <button class="btn-secondary ${disabledClass}" ${disabledAttr} data-id="${p.id}">
                        Ajouter
                    </button>
                </div>

                <div class="product-stock">${stockText}</div>
            </div>
        `;

        grid.appendChild(card);
    });

    // Écouteurs sur les boutons "Ajouter"
    grid.querySelectorAll("button[data-id]").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = parseInt(btn.dataset.id, 10);
            addToCart(id);
        });
    });
}


// === PANIER ===

function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    if (!product || product.stock <= 0) return;

    const existing = cart.find(item => item.id === productId);
    if (existing) {
        if (existing.qty < product.stock) {
            existing.qty++;
        }
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            qty: 1
        });
    }

    updateCartUI();
    openCart();
}

function changeQty(productId, delta) {
    const item = cart.find(i => i.id === productId);
    if (!item) return;

    item.qty += delta;

    if (item.qty <= 0) {
        cart = cart.filter(i => i.id !== productId);
    }

    updateCartUI();
}

function updateCartUI() {
    const countSpan = document.getElementById("cart-count");
    const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
    const totalItemsSpan = document.getElementById("cart-total-items");
if (totalItemsSpan) totalItemsSpan.textContent = totalItems;

    countSpan.textContent = totalItems;

    const itemsContainer = document.getElementById("cart-items");
    itemsContainer.innerHTML = "";

    if (cart.length === 0) {
        itemsContainer.innerHTML = "<p>Votre panier est vide.</p>";
    } else {
        cart.forEach(item => {
            const div = document.createElement("div");
            div.className = "cart-item";
           div.innerHTML = `
    <span>${item.name}</span>

    <div class="qty-control">
        <button class="qty-btn minus" data-id="${item.id}">−</button>
        <span class="qty-number">${item.qty}</span>
        <button class="qty-btn plus" data-id="${item.id}">+</button>
    </div>

    <span>${(item.qty * item.price).toFixed(2)} €</span>
`;
            itemsContainer.appendChild(div);
        });

        // Boutons +
itemsContainer.querySelectorAll(".qty-btn.plus").forEach(btn => {
    btn.addEventListener("click", () => {
        const id = parseInt(btn.dataset.id, 10);
        changeQty(id, +1);
    });
});

// Boutons -
itemsContainer.querySelectorAll(".qty-btn.minus").forEach(btn => {
    btn.addEventListener("click", () => {
        const id = parseInt(btn.dataset.id, 10);
        changeQty(id, -1);
    });
});

    }

    const total = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
    document.getElementById("cart-total").textContent = total.toFixed(2) + " €";
}

// Panneau panier
function openCart() {
    document.getElementById("cart-panel").classList.remove("hidden");
    document.getElementById("overlay").classList.remove("hidden");
}

function closeCart() {
    document.getElementById("cart-panel").classList.add("hidden");
    document.getElementById("overlay").classList.add("hidden");
}


// === Gestion du formulaire de commande ===
function setupOrderForm() {
    const form = document.getElementById("order-form");
    if (!form) return;

    const message = document.getElementById("order-message");

    form.addEventListener("submit", (e) => {
        e.preventDefault();
        message.textContent = "";

        if (cart.length === 0) {
            message.style.color = "#dc2626";
            message.textContent = "Votre panier est vide.";
            return;
        }

        if (!isLoggedIn) {
            message.style.color = "#dc2626";
            message.innerHTML = "Vous devez être connecté pour commander. <a href='login.php'>Connexion</a>";
            return;
        }

        const name = document.getElementById("name").value.trim();
        const email = document.getElementById("email").value.trim();
        const address = document.getElementById("address").value.trim();
        const notes = document.getElementById("notes").value.trim();

        const itemsPayload = cart.map(item => ({
            product_id: item.id,
            quantity: item.qty
        }));

        fetch("place_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                name,
                email,
                address,
                notes,
                items: itemsPayload
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                message.style.color = "#16a34a";
                message.textContent = "Commande #" + data.order_id + " enregistrée !";
                cart = [];
                updateCartUI();
                form.reset();
            } else {
                message.style.color = "#dc2626";
                message.textContent = data.message || "Erreur lors de la commande.";
            }
        })
        .catch(() => {
            message.style.color = "#dc2626";
            message.textContent = "Erreur serveur.";
        });
    });
}


// === INIT ===
document.addEventListener("DOMContentLoaded", () => {
    renderProducts();
    updateCartUI();
    setupOrderForm();

    document.getElementById("cart-button").addEventListener("click", openCart);
    document.getElementById("close-cart").addEventListener("click", closeCart);
    document.getElementById("overlay").addEventListener("click", closeCart);

    document.getElementById("go-checkout").addEventListener("click", () => {
        closeCart();
        document.getElementById("checkout").scrollIntoView({ behavior: "smooth" });
    });
});
