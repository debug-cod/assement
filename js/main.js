document.addEventListener('DOMContentLoaded', () => {
    // 初始化地图，假设你的 div id 是 'map'
    const myMap = new MapManager('map');
    myMap.init();

    // 初始化搜索，假设输入框 id 是 'search-input'，结果列表 id 是 'results'
    const mySearch = new SearchManager('search-input', 'results');
    mySearch.init();
});