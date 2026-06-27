(function () {
    const DEBOUNCE_MS = 300;
    const MIN_CHARS = 2;
    const API_URL_BASE = '/api/slopes/search/';



    // déterminer si on travaille avec le Input mobile ou screen
    const inputScreen = document.getElementById('slopeSearchInput');
    let isVisible = inputScreen.checkVisibility({
        opacityProperty: true,   // Check CSS opacity property too
        visibilityProperty: true // Check CSS visibility property too
    });
    var input = document.getElementById('slopeSearchInput');
    var dropdown = document.getElementById('slopeSearchResults');
    if (!isVisible)
    {
        input = document.getElementById('slopeSearchInputMobile');
        dropdown = document.getElementById('slopeSearchResultsMobile');
    }

    if (!input || !dropdown) return;

    let debounceTimer = null;
    let currentRequestId = 0; // ignore les réponses obsolètes (requêtes parties dans le désordre)
    let activeIndex = -1;     // pour la navigation clavier
    let currentResults = [];

    input.addEventListener('input', function () {
        const query = input.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < MIN_CHARS) {
            closeDropdown();
            return;
        }

        debounceTimer = setTimeout(function () {
            fetchResults(query);
        }, DEBOUNCE_MS);
    });

    input.addEventListener('keydown', function (e) {
        if (!isDropdownOpen()) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            moveActive(1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            moveActive(-1);
        } else if (e.key === 'Enter') {
            if (activeIndex >= 0 && currentResults[activeIndex]) {
                e.preventDefault();
                selectSlope(currentResults[activeIndex]);
            }
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });

    // Ferme le dropdown si on clique ailleurs sur la page
    document.addEventListener('click', function (e) {
        if (!input.closest('.rc-slope-search').contains(e.target)) {
            closeDropdown();
        }
    });

    function fetchResults(query) {
        const requestId = ++currentRequestId;
        showLoading();

        fetch(API_URL_BASE + encodeURIComponent(query), {
            headers: { 'Accept': 'application/json' },
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Erreur serveur (' + response.status + ')');
                return response.json();
            })
            .then(function (data) {
                // Si une requête plus récente est partie depuis, on jette ce résultat obsolète
                if (requestId !== currentRequestId) return;
                renderResults(Array.isArray(data.data) ? data.data : (data.results || []));
            })
            .catch(function () {
                if (requestId !== currentRequestId) return;
                showError();
            });

    }

    function renderResults(results) {
        currentResults = results;
        activeIndex = -1;
        dropdown.innerHTML = '';

        if (results.length === 0) {
            dropdown.innerHTML = '<li class="rc-search-empty">Aucun site trouvé</li>';
            openDropdown();
            return;
        }
        results.forEach(function (slope, index) {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.className = 'dropdown-item';
            a.href = '#';
            a.textContent = slope.name;
            a.dataset.index = index;

            a.addEventListener('click', function (e) {
                e.preventDefault();
                selectSlope(slope);
            });
            a.addEventListener('mouseenter', function () {
                setActiveIndex(index);
            });

            li.appendChild(a);
            dropdown.appendChild(li);
        });

        openDropdown();
    }

    function selectSlope(slope) {
        input.value = slope.name;
        closeDropdown();

        // Redirige vers la fiche du site. Adapte cette URL à ta structure réelle.
        //window.location.href = '/slopes/' + slope.slopeId;

        map.flyTo([slope.lat, slope.lng],14);
    }

    function moveActive(direction) {
        if (currentResults.length === 0) return;
        let newIndex = activeIndex + direction;
        if (newIndex < 0) newIndex = currentResults.length - 1;
        if (newIndex >= currentResults.length) newIndex = 0;
        setActiveIndex(newIndex);
    }

    function setActiveIndex(index) {
        const items = dropdown.querySelectorAll('.dropdown-item');
        items.forEach(function (item) { item.classList.remove('active'); });
        if (items[index]) {
            items[index].classList.add('active');
            items[index].scrollIntoView({ block: 'nearest' });
        }
        activeIndex = index;
    }

    function showLoading() {
        dropdown.innerHTML = '<li class="rc-search-loading">Recherche…</li>';
        openDropdown();
    }

    function showError() {
        dropdown.innerHTML = '<li class="rc-search-empty">Erreur lors de la recherche</li>';
        openDropdown();
    }

    function openDropdown() {
        dropdown.classList.add('show');
        input.setAttribute('aria-expanded', 'true');
    }

    function closeDropdown() {
        dropdown.classList.remove('show');
        dropdown.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
        activeIndex = -1;
        currentResults = [];
    }

    function isDropdownOpen() {
        return dropdown.classList.contains('show');
    }
})();