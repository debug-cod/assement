document.addEventListener('DOMContentLoaded', () => {
    const myMap = new MapManager('map');
    myMap.init();

    const mySearch = new SearchManager('search-input', 'results');

    // 为了让 HTML 里的 onclick 能找到这个实例
    window.searchManagerInstance = mySearch;

    // 关键步骤：定义“当选中结果时”该做什么
    // 关键步骤：定义“当选中结果时”该做什么
    mySearch.onResultSelected = (lat, lng, name, photo) => {
        // 1. 地图平滑移动到宠物坐标
        myMap.map.setView([lat, lng], 16);

        // 2. 计算距离（如果用户位置已获取）
        let distanceInfo = "";
        if (myMap.userLocation) {
            const petPos = L.latLng(lat, lng);
            const meters = myMap.userLocation.distanceTo(petPos);
            const km = (meters / 1000).toFixed(2);
            distanceInfo = `<div style="margin-top:5px; font-size: 14px; color: #666;><span class="label label-info">距离你约 ${km} km</span></div>`;
        } else {
            distanceInfo = `<div style="margin-top:5px;"><small class="text-muted">等待定位以显示距离...</small></div>`;
        }

        // 3. 弹出一个漂亮的气泡
        L.marker([lat, lng])
            .addTo(myMap.map)
            .bindPopup(`
                <div style="text-align:center; width:180px; font-family: 'Helvetica Neue', Arial, sans-serif; padding: 5px;">
                    <img src="images/pet-image/${photo}" style="width:100%; height:120px; object-fit:cover; border-radius:8px; margin-bottom:8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <b style="font-size: 18px; color: #d9534f; display: block;">${name}</b>
                    <div style="font-size: 13px; color: #666; margin-top: 3px;">been seem in here</div>
                    ${distanceInfo}
                    <hr style="margin: 12px 0;">
                    <a href="controllers/browse.php?search=${encodeURIComponent(name)}" 
                       class="btn btn-primary btn-sm" 
                       style="color:white; text-decoration:none; display:block; padding: 8px; font-weight: bold; border-radius: 4px; background-color: #337ab7; border: none;">
                       check in detail
                    </a>
                </div>
            `)
            .openPopup();
    };

    mySearch.init();
});