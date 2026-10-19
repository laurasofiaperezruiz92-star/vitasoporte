/**
 * Filtros del catálogo VitaSoporte.
 * Los productos viven en el HTML con data-* (listos para reemplazar por BD).
 */
(function () {
  const grid = document.getElementById("productGrid");
  const countEl = document.getElementById("resultsCount");
  const emptyState = document.getElementById("emptyState");
  const searchInput = document.getElementById("searchInput");
  const sortSelect = document.getElementById("sortSelect");
  const clearBtn = document.getElementById("clearFilters");
  const filterForm = document.querySelector(".catalog-filters");

  if (!grid || !filterForm) return;

  const cards = Array.from(grid.querySelectorAll(".catalog-card"));

  function selectedValues(name) {
    return Array.from(
      filterForm.querySelectorAll(`input[name="${name}"]:checked`)
    ).map((input) => input.value);
  }

  function matchesGroup(cardValue, selected) {
    if (!selected.length) return true;
    return selected.includes(cardValue);
  }

  function applyFilters() {
    const categorias = selectedValues("categoria");
    const generos = selectedValues("genero");
    const usos = selectedValues("uso");
    const query = (searchInput?.value || "").trim().toLowerCase();

    let visible = 0;

    cards.forEach((card) => {
      const nombre = (card.dataset.nombre || "").toLowerCase();
      const okCategoria = matchesGroup(card.dataset.categoria, categorias);
      const okGenero = matchesGroup(card.dataset.genero, generos);
      const okUso = matchesGroup(card.dataset.uso, usos);
      const okSearch = !query || nombre.includes(query);
      const show = okCategoria && okGenero && okUso && okSearch;

      card.hidden = !show;
      if (show) visible += 1;
    });

    sortCards();
    updateCount(visible);
  }

  function sortCards() {
    const mode = sortSelect?.value || "nombre-asc";
    const visibleCards = cards.filter((card) => !card.hidden);

    visibleCards.sort((a, b) => {
      const nameA = a.dataset.nombre || "";
      const nameB = b.dataset.nombre || "";
      const priceA = Number(a.dataset.precio || 0);
      const priceB = Number(b.dataset.precio || 0);

      switch (mode) {
        case "nombre-desc":
          return nameB.localeCompare(nameA, "es");
        case "precio-asc":
          return priceA - priceB;
        case "precio-desc":
          return priceB - priceA;
        case "nombre-asc":
        default:
          return nameA.localeCompare(nameB, "es");
      }
    });

    visibleCards.forEach((card) => grid.appendChild(card));
  }

  function updateCount(visible) {
    if (countEl) {
      countEl.textContent =
        visible === 1
          ? "Mostrando 1 producto"
          : `Mostrando ${visible} productos`;
    }
    if (emptyState) {
      emptyState.hidden = visible > 0;
    }
  }

  function clearFilters() {
    filterForm
      .querySelectorAll('input[type="checkbox"]')
      .forEach((input) => {
        input.checked = false;
      });
    if (searchInput) searchInput.value = "";
    if (sortSelect) sortSelect.value = "nombre-asc";
    applyFilters();
  }

  filterForm.addEventListener("change", applyFilters);
  searchInput?.addEventListener("input", applyFilters);
  sortSelect?.addEventListener("change", applyFilters);
  clearBtn?.addEventListener("click", clearFilters);

  applyFilters();
})();
