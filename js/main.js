document.addEventListener('DOMContentLoaded', () => {
    const myMap = new MapManager('map');
    myMap.init();

    // 自动适配 ID: home 用 search-input, browse 用 searchInput
    const inputId = document.getElementById('searchInput') ? 'searchInput' : 'search-input';
    const resultsId = document.getElementById('searchSuggestions') ? 'searchSuggestions' : 'results';

    const mySearch = new SearchManager(inputId, resultsId);
    window.searchManagerInstance = mySearch;

    mySearch.onResultSelected = (lat, lng, name, photo) => {
        myMap.map.setView([lat, lng], 16);

        const isViewsFolder = window.location.pathname.includes('views');
        const prefix = isViewsFolder ? '../' : '';
        const browsePath = isViewsFolder ? 'browse.php' : 'controllers/browse.php';

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