// Автодополнение для поиска
const mainSearch = document.getElementById('mainSearch');
const searchResults = document.getElementById('searchResults');
const searchBtn = document.getElementById('searchBtn');

let searchTimeout;

if (mainSearch) {
    mainSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            searchResults.innerHTML = '';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    searchBtn.addEventListener('click', function() {
        const query = mainSearch.value.trim();
        if (query) {
            performSearch(query);
        }
    });
}

function performSearch(query) {
    fetch(`search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data.results || []);
        })
        .catch(error => {
            console.error('Ошибка поиска:', error);
        });
}

function displaySearchResults(results) {
    if (!results || results.length === 0) {
        searchResults.innerHTML = '<p style="padding: 1rem;">Ничего не найдено</p>';
        return;
    }
    
    let html = '';
    results.forEach(result => {
        html += `
            <div class="search-result-item">
                <div class="hotkey-keys">${formatKeys(result.key_combination)}</div>
                <div class="hotkey-action">${escapeHtml(result.action_description)}</div>
                <div class="hotkey-meta">
                    ${escapeHtml(result.product_name)} (v${escapeHtml(result.version)})
                    <span class="popularity">👁️ ${result.popularity}</span>
                </div>
            </div>
        `;
    });
    searchResults.innerHTML = html;
}

function formatKeys(keys) {
    const parts = keys.split('+');
    return parts.map(part => `<kbd>${escapeHtml(part)}</kbd>`).join('<span class="plus">+</span>');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Фильтры
const productFilter = document.getElementById('productFilter');
const versionFilter = document.getElementById('versionFilter');
const groupFilter = document.getElementById('groupFilter');
const applyFilters = document.getElementById('applyFilters');
const resetFilters = document.getElementById('resetFilters');
const filteredResults = document.getElementById('filteredResults');

if (productFilter) {
    productFilter.addEventListener('change', function() {
        const productId = this.value;
        if (productId) {
            loadVersions(productId);
        } else {
            versionFilter.innerHTML = '<option value="">Все версии</option>';
        }
    });
}

function loadVersions(productId) {
    fetch(`api.php?action=getVersions&product_id=${productId}`)
        .then(response => response.json())
        .then(versions => {
            versionFilter.innerHTML = '<option value="">Все версии</option>';
            versions.forEach(version => {
                versionFilter.innerHTML += `<option value="${version.id}">v${version.version}</option>`;
            });
        })
        .catch(error => {
            console.error('Ошибка загрузки версий:', error);
        });
}

if (applyFilters) {
    applyFilters.addEventListener('click', function() {
        const productId = productFilter.value;
        const versionId = versionFilter.value;
        const groupId = groupFilter.value;
        
        if (!productId) {
            alert('Выберите программу');
            return;
        }
        
        let url = `api.php?action=getHotkeys&product_id=${productId}`;
        if (versionId) url += `&version_id=${versionId}`;
        if (groupId) url += `&group_id=${groupId}`;
        
        fetch(url)
            .then(response => response.json())
            .then(hotkeys => {
                displayFilteredResults(hotkeys);
            })
            .catch(error => {
                console.error('Ошибка применения фильтров:', error);
            });
    });
}

if (resetFilters) {
    resetFilters.addEventListener('click', function() {
        productFilter.value = '';
        versionFilter.innerHTML = '<option value="">Все версии</option>';
        groupFilter.value = '';
        filteredResults.innerHTML = '';
    });
}

function displayFilteredResults(hotkeys) {
    if (!hotkeys || hotkeys.length === 0) {
        filteredResults.innerHTML = '<p>Ничего не найдено с заданными фильтрами</p>';
        return;
    }
    
    // Группировка по группам функций
    const grouped = {};
    hotkeys.forEach(hotkey => {
        if (!grouped[hotkey.group_name]) {
            grouped[hotkey.group_name] = [];
        }
        grouped[hotkey.group_name].push(hotkey);
    });
    
    let html = '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">';
    html += '<h2>Результаты фильтрации</h2>';
    
    for (const groupName in grouped) {
        html += `<h3 style="margin-top: 1.5rem; color: #667eea;">${escapeHtml(groupName)}</h3>`;
        html += '<table class="hotkeys-table"><thead><tr><th>Клавиши</th><th>Действие</th><th>Популярность</th></tr></thead><tbody>';
        
        grouped[groupName].forEach(hotkey => {
            html += `
                <tr>
                    <td class="keys-cell">${formatKeys(hotkey.key_combination)}</td>
                    <td>${escapeHtml(hotkey.action_description)}</td>
                    <td><span class="popularity-badge">👁️ ${hotkey.popularity}</span></td>
                </tr>
            `;
        });
        
        html += '</tbody></table>';
    }
    
    html += '</div>';
    filteredResults.innerHTML = html;
}

// Генерация PDF
const generatePDF = document.getElementById('generatePDF');
if (generatePDF) {
    generatePDF.addEventListener('click', function() {
        const productId = productFilter.value;
        const versionId = versionFilter.value;
        
        let url = 'generate_pdf.php?';
        if (productId) url += `product_id=${productId}&`;
        if (versionId) url += `version_id=${versionId}&`;
        
        window.open(url, '_blank');
    });
}