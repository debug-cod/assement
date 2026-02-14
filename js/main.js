// 在 main.js 中修改
document.addEventListener('DOMContentLoaded', () => {
    const myMap = new MapManager('map');
    myMap.init();

    const mySearch = new SearchManager('search-input', 'results');

    // 关键：给 searchManager 一个“当选中结果时”的回调函数
    mySearch.onResultSelected = (lat, lng) => {
        myMap.map.setView([lat, lng], 15); // 地图平滑移动到宠物位置
        L.marker([lat, lng]).addTo(myMap.map).bindPopup("the pet last seem spot").openPopup();
    };

    mySearch.init();
});