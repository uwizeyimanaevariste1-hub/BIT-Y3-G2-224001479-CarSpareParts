console.log("AutoParts Store JavaScript loaded successfully!");

// Wait until the page is ready
document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("partSearch");
    const productResults = document.getElementById("productResults");
    const searchMessage = document.getElementById("searchMessage");

    // Stop if this page does not contain the search feature
    if (!searchInput || !productResults) {
        return;
    }

    let searchTimer;

    // Create a product card safely
    function createProductCard(part) {

        const card = document.createElement("div");
        card.className = "product-card";

        const title = document.createElement("h3");
        title.textContent = part.name;

        const category = document.createElement("p");
        category.textContent = "Category: " + part.category;

        const brand = document.createElement("p");
        brand.textContent = "Brand: " + part.brand;

        const price = document.createElement("p");
        price.className = "product-price";
        price.textContent =
            Number(part.price).toLocaleString() + " RWF";

        const stock = document.createElement("p");
        stock.textContent = "Stock: " + part.stock;

        card.appendChild(title);
        card.appendChild(category);
        card.appendChild(brand);
        card.appendChild(price);
        card.appendChild(stock);

        if (Number(part.stock) > 0) {

            if (window.storeSearchConfig.isCustomer) {

                const form = document.createElement("form");
                form.method = "POST";
                form.action = "add_to_cart.php";

                const partId = document.createElement("input");
                partId.type = "hidden";
                partId.name = "part_id";
                partId.value = part.part_id;

                const csrf = document.createElement("input");
                csrf.type = "hidden";
                csrf.name = "csrf_token";
                csrf.value = window.storeSearchConfig.csrfToken;

                const button = document.createElement("button");
                button.type = "submit";
                button.className = "add-cart-button";
                button.textContent = "Add to Cart";

                form.appendChild(partId);
                form.appendChild(csrf);
                form.appendChild(button);

                card.appendChild(form);

            } else {

                const link = document.createElement("a");

                link.href = "login.php";
                link.className = "add-cart-button";

                link.textContent = window.storeSearchConfig.isLoggedIn
                    ? "Login as Customer to Order"
                    : "Login to Order";

                card.appendChild(link);
            }

        } else {

            const button = document.createElement("button");

            button.disabled = true;
            button.textContent = "Out of Stock";

            card.appendChild(button);
        }

        return card;
    }

    // Search database using Fetch API
    async function searchParts(keyword) {

        searchMessage.textContent = "Searching...";

        try {

            const response = await fetch(
                window.storeSearchConfig.apiUrl +
                "?q=" + encodeURIComponent(keyword)
            );

            if (!response.ok) {
                throw new Error("Server error: " + response.status);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message);
            }

            // Remove previous results
            productResults.replaceChildren();

            if (data.parts.length === 0) {

                searchMessage.textContent =
                    "No matching spare parts found.";

                return;
            }

            // Display new results
            data.parts.forEach(function (part) {

                const card = createProductCard(part);

                productResults.appendChild(card);

            });

            searchMessage.textContent =
                data.count + " spare part(s) found.";

        } catch (error) {

            console.error("Search error:", error);

            searchMessage.textContent =
                "Unable to search right now. Please try again.";
        }
    }

    // Detect typing and wait briefly before searching
    searchInput.addEventListener("input", function () {

        const keyword = searchInput.value.trim();

        clearTimeout(searchTimer);

        searchTimer = setTimeout(function () {

            searchParts(keyword);

        }, 300);

    });

});