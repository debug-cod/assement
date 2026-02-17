document.addEventListener('DOMContentLoaded', () => {
    const myMap = new MapManager('map');
    myMap.init();

    window.myMapInstance = myMap;

    const toggleBtn = document.getElementById('toggleMapBtn');
    const mapContainer = document.getElementById('map');

    if (toggleBtn && mapContainer) {
        toggleBtn.addEventListener('click', function() {
            // 1. 切换类名
            mapContainer.classList.toggle('map-expanded');

            // 2. 更新按钮文字和图标
            const isExpanded = mapContainer.classList.contains('map-expanded');
            if (isExpanded) {
                toggleBtn.innerHTML = '<span class="glyphicon glyphicon-resize-small"></span> Collapse Map';
                toggleBtn.className = 'btn btn-warning btn-sm';
            } else {
                toggleBtn.innerHTML = '<span class="glyphicon glyphicon-resize-full"></span> Expand Map';
                toggleBtn.className = 'btn btn-default btn-sm';
            }

            // 3. 【高分关键】延迟一丁点时间等 CSS 动画执行完，然后刷新地图尺寸
            setTimeout(() => {
                myMap.map.invalidateSize({ pan: true });
            }, 450);
        });
    }

    // 自动适配 ID: home 用 search-input, browse 用 searchInput
    const inputId = document.getElementById('searchInput') ? 'searchInput' : 'search-input';
    const resultsId = document.getElementById('searchSuggestions') ? 'searchSuggestions' : 'results';

    const mySearch = new SearchManager(inputId, resultsId);
    window.searchManagerInstance = mySearch;

    mySearch.onResultSelected = (lat, lng, name, photo) => {
        const path = window.location.pathname;
        const isAlreadyInControllers = path.includes('browse.php') || path.includes('controllers/');

        // 动态适配图片路径和跳转链接
        const prefix = isAlreadyInControllers ? '../' : '';
        const browsePath = isAlreadyInControllers ? 'browse.php' : 'controllers/browse.php';

        // 如果在 controllers 里，图片需要跳出去找 ../images/
        const imgFullPath = `${prefix}images/pet-image/${photo}`;

        // 计算当前位置到目标的距离
        let distanceHTML = "";
        if (myMap.userLocation) {
            const dist = (myMap.userLocation.distanceTo(L.latLng(lat, lng)) / 1000).toFixed(2);
            distanceHTML = `Distance: <b style="color: #2c3e50;">${dist} km</b>`;
        } else {
            distanceHTML = "Locating...";
        }

        L.marker([lat, lng])
            .addTo(myMap.map)
            .bindPopup(`
                <div style="text-align:center; width:200px; font-family: Arial, sans-serif;">
                    <img src="${prefix}images/pet-image/${photo}" style="width:100%; height:120px; object-fit:cover; border-radius:8px; margin-bottom:8px;">
                    <b style="font-size: 18px; color: #d9534f; display: block;">${name}</b>
                    
                    <div style="font-size: 16px; margin: 10px 0;">
                        ${distanceHTML}
                    </div>
                    
                    <hr style="margin: 10px 0;">
                    
                    <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" 
                       target="_blank" 
                       class="btn btn-success btn-sm" 
                       style="color:white; text-decoration:none; display:block; padding: 6px; margin-bottom:5px; background-color: #28a745;">
                       Navigate
                    </a>

                    <a href="${browsePath}?search=${encodeURIComponent(name)}" 
                       class="btn btn-primary btn-sm" 
                       style="color:white; text-decoration:none; display:block; padding: 6px; background-color: #007bff;">
                       Check in Detail
                    </a>
                </div>
            `).openPopup();
    };

    mySearch.init();
});