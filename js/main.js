/**
 * Main application entry point.
 * Orchestrates MapManager and SearchManager initialization and cross-component communication.
 */
document.addEventListener('DOMContentLoaded', () => {
    const myMap = new MapManager('map');
    myMap.init();

    window.myMapInstance = myMap;

    const toggleBtn = document.getElementById('toggleMapBtn');
    const mapContainer = document.getElementById('map');

    if (toggleBtn && mapContainer) {
        toggleBtn.addEventListener('click', function() {
            mapContainer.classList.toggle('map-expanded');
            const isExpanded = mapContainer.classList.contains('map-expanded');
            if (isExpanded) {
                toggleBtn.innerHTML = '<span class="glyphicon glyphicon-resize-small"></span> Collapse Map';
                toggleBtn.className = 'btn btn-warning btn-sm';
            } else {
                toggleBtn.innerHTML = '<span class="glyphicon glyphicon-resize-full"></span> Expand Map';
                toggleBtn.className = 'btn btn-default btn-sm';
            }
            setTimeout(() => {
                myMap.map.invalidateSize({ pan: true });
            }, 450);
        });
    }

    const inputId = document.getElementById('searchInput') ? 'searchInput' : 'search-input';
    const resultsId = document.getElementById('searchSuggestions') ? 'searchSuggestions' : 'results';
    const mySearch = new SearchManager(inputId, resultsId);
    mySearch.init();

    window.searchManagerInstance = mySearch;


     // Triggered when a user selects a pet from search suggestions.

    mySearch.onResultSelected = (lat, lng, name, photo, petId) => {
        // 【调试代码】如果你在控制台看到 petId 是 undefined，说明第一步没改对
        console.log("Main.js received petId:", petId); // 检查这里是否还是 undefined

        const path = window.location.pathname;
        const isSubFolder = path.includes('browse.php') || path.includes('controllers/');
        const prefix = isSubFolder ? '../' : '';

        let actionHTML = "";
        // 只有 petId 有值时才生成按钮
        if (window.isUserLoggedIn && petId) {
            actionHTML = `
            <div style="margin-top:10px; border-top:1px solid #eee; padding-top:10px;">
                <input type="text" id="comment-${petId}" class="form-control input-sm" 
                       placeholder="Describe where you saw it..." style="margin-bottom:5px;">
                <button onclick="window.myMapInstance.submitSighting('${petId}')" 
                        class="btn btn-warning btn-sm btn-block">
                    Update Sighting Detail
                </button>
            </div>`;
        } else if (!window.isUserLoggedIn) {
            actionHTML = `
            <div style="margin-top:10px;">
            <input type="text" id="comment-${petId}" class="form-control" placeholder="Describe...">
            <button onclick="window.myMapInstance.submitSighting('${petId}')" 
                class="btn btn-warning btn-block">
                Update
            </button>
    </div>`;
        }

        myMap.map.setView([lat, lng], 15);

        L.marker([lat, lng])
            .addTo(myMap.map)
            .bindPopup(`
            <div style="text-align:center; width:220px; font-family: Arial, sans-serif;">
                <img src="${prefix}images/pet-image/${photo}" 
                     style="width:100%; height:120px; object-fit:cover; border-radius:8px; margin-bottom:8px;">
                <b style="font-size: 18px; color: #d9534f; display: block;">${name}</b>
                <div style="display: flex; gap: 5px; margin-top: 10px;">
                    <a href="https://www.google.com/maps?q=${lat},${lng}" 
                       target="_blank" class="btn btn-success btn-xs" 
                       style="flex:1; color:white; text-decoration:none; background-color: #28a745; padding: 5px; border-radius:4px;">
                       Navigate
                    </a>
                    <a href="${browsePath}?search=${encodeURIComponent(name)}" 
                       class="btn btn-primary btn-xs" 
                       style="flex:1; color:white; text-decoration:none; background-color: #007bff; padding: 5px; border-radius:4px;">
                       Detail
                    </a>
                </div>
                ${actionHTML}
            </div>
        `)
            .openPopup();
    };
});