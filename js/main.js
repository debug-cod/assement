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
        // 1. 调试日志：确认收到 ID
        console.log("Main.js received petId:", petId);

        // 2. 环境路径检测 (修复 browsePath 报错的关键)
        const path = window.location.pathname;
        const isSubFolder = path.includes('browse.php') || path.includes('controllers/');
        const prefix = isSubFolder ? '../' : '';
        const browsePath = isSubFolder ? 'browse.php' : 'controllers/browse.php';

        // 3. 准备 UI
        let actionHTML = "";
        if (window.isUserLoggedIn && petId) {
            // 注意：这里我们给 submitSighting 传参时强制确保它是 petId
            actionHTML = `
            <div style="margin-top:10px; border-top:1px solid #eee; padding-top:10px;">
                <input type="text" id="comment-${petId}" class="form-control input-sm" 
                       placeholder="Describe where you saw it..." style="margin-bottom:5px;">
                <button onclick="window.myMapInstance.submitSighting(${petId})" 
                        class="btn btn-warning btn-sm btn-block" 
                        style="font-weight:bold;">
                    Update Sighting Detail
                </button>
            </div>`;
        } else if (!window.isUserLoggedIn) {
            actionHTML = `
            <div style="margin-top:10px; border-top:1px solid #eee; padding-top:10px;">
                <p style="font-size:12px; color:#d9534f; font-weight:bold; margin:5px 0;">
                    Login to report a sighting
                </p>
            </div>`;
        }

        // 4. 更新地图
        myMap.map.setView([lat, lng], 15);

        // 5. 绑定弹窗 (确保所有变量如 prefix, browsePath 都已定义)
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