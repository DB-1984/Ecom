(function () {
  function initProductSearch() {
    const blocks = document.querySelectorAll(".ecom-header-search");

    blocks.forEach(function (block) {
      const form = block.matches("form")
        ? block
        : block.querySelector("form");

      const input = form?.querySelector('input[type="search"]');

      if (!input || block.dataset.liveSearchReady) return;

      block.dataset.liveSearchReady = "true";
      block.classList.add("ecom-live-search");

      const results = document.createElement("div");
      results.className = "ecom-live-results";
      results.hidden = true;
      results.setAttribute("aria-label", "Product suggestions");
      results.setAttribute("role", "region");

      form.append(results);

      let debounceTimer;
      let requestId = 0;

      function showMessage(message) {
        const paragraph = document.createElement("p");
        paragraph.className = "ecom-live-status";
        paragraph.setAttribute("role", "status");
        paragraph.textContent = message;

        results.replaceChildren(paragraph);
      }

      input.addEventListener("input", function () {
        clearTimeout(debounceTimer);

        const query = input.value.trim();
        const currentRequest = ++requestId;

        results.hidden = true;

        if (query.length < 2) return;

        debounceTimer = setTimeout(function () {
          fetchResults(query, currentRequest);
        }, 300);
      });

      async function fetchResults(query, currentRequest) {
        results.hidden = false;
        showMessage("Searching…");

        try {
          const url = new URL(form.action, window.location.href);

          url.search = "";
          url.hash = "";

          url.searchParams.set("rest_route", "/wc/store/v1/products");
          url.searchParams.set("search", query);
          url.searchParams.set("per_page", "6");
          url.searchParams.set("catalog_visibility", "search");

          const response = await fetch(url);

          if (!response.ok) {
            throw new Error("Search failed");
          }

          const products = await response.json();

          // Ignore results from an outdated request.
          if (currentRequest !== requestId) return;

          if (!Array.isArray(products)) {
            throw new Error("Invalid search response");
          }

          results.replaceChildren();

          if (!products.length) {
            showMessage("No products found.");
            return;
          }

          products.forEach(function (product) {
            const link = document.createElement("a");
            link.className = "ecom-live-product";
            link.href = product.permalink;

            // Decode HTML entities in the product name.
            const title = new DOMParser().parseFromString(
              product.name,
              "text/html"
            );

            link.textContent = title.body.textContent;
            results.append(link);
          });
        } catch (error) {
          if (currentRequest !== requestId) return;

          showMessage("Search unavailable. Press Enter to search.");
        }
      }

      function closeResults() {
        clearTimeout(debounceTimer);
        requestId++;
        results.hidden = true;
      }

      document.addEventListener("click", function (event) {
        if (!form.contains(event.target)) {
          closeResults();
        }
      });

      form.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
          closeResults();
          input.focus();
        }
      });

      form.addEventListener("submit", closeResults);
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initProductSearch);
  } else {
    initProductSearch();
  }
})();