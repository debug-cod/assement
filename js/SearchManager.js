/**
 * SearchManager Class - 处理实时搜索建议
 * Handles live search suggestions using fetch()
 */

class SearchManager {
    constructor(inputId, resultsId) {
        this.input = document.getElementById(inputId);
        this.resultsContainer = document.getElementById(resultsId);
        this.debounceTimer = null;
        // 新增：用于存储选中后的回调函数
        this.onResultSelected = null;
    }

    init() {
        if (!this.input) return;

        this.input.addEventListener('input', () => {
            const term = this.input.value;

            clearTimeout(this.debounceTimer);

            if (term.length > 2) {
                this.debounceTimer = setTimeout(() => {
                    this.performSearch(term);
                }, 300);
            } else {
                // --- 核心修复：当输入框内容太少或被清空时 ---
                this.resultsContainer.innerHTML = '';

                // 如果用户完全清空了搜索框，通知地图重置
                if (term.length === 0) {
                    console.log("Search cleared, resetting to nearby pets...");
                    // 触发一个自定义回调，或者直接调用全局地图实例
                    if (window.myMapInstance) {
                        window.myMapInstance.locateUser();
                    }
                }
            }
        });
    }

    async performSearch(term) {

        const loader = document.getElementById('searchLoader');
        try {
            const path = window.location.pathname;
            const isSubFolder = path.includes('browse.php') || path.includes('controllers/');
            const apiUrl = isSubFolder ? 'api_search_pets.php' : 'controllers/api_search_pets.php';

            const response = await fetch(`${apiUrl}?term=${encodeURIComponent(term)}`);
            const data = await response.json();

            // 【关键修复点 1】：必须把 data 传进去，否则 renderResults 内部找不到数据
            this.renderResults(data);

        } catch (e) {
            console.error("Search API Error:", e);
        }
    }

    renderResults(data) {
        // 【关键修复点 2】：确保这里接收参数 data
        if (!data || data.length === 0) {
            this.resultsContainer.innerHTML = '<ul class="list-group"><li class="list-group-item">No pets found</li></ul>';
            return;
        }

        const path = window.location.pathname;
        const isSubFolder = path.includes('browse.php') || path.includes('controllers/');
        // 【关键修复点 3】：修正图片路径，确保 browse 页面能跳出子目录
        const imgPrefix = isSubFolder ? '../' : '';

        let html = '<ul class="list-group" style="position:absolute; z-index:1000; width:100%;">';

        data.forEach(pet => {
            const locationText = pet.latitude ? `Sighted at: ${pet.latitude.toFixed(2)}, ${pet.longitude.toFixed(2)}` : 'No location reported';
            const textClass = pet.latitude ? 'text-success' : 'text-danger';

            const gender = pet.gender || 'Unknown';
            const age = pet.age || 'Unknown';
            const breed = pet.breed || 'Unknown';

            html += `
                <li class="list-group-item" style="cursor:pointer;" 
                    onclick="window.searchManagerInstance.handleItemClick('${pet.name}', ${pet.latitude || 0}, ${pet.longitude || 0}, '${pet.photo_url}')">
                    <div class="row">
                        <div class="col-xs-3">
                            <img src="${imgPrefix}images/pet-image/${pet.photo_url}" class="img-responsive img-rounded">
                        </div>
                        <div class="col-xs-9">
                            <strong style="font-size: 16px;">${pet.name}</strong> 
                            <span class="label label-default">${pet.species}</span><br>
                            
                            <div style="font-size: 12px; color: #666; margin: 3px 0;">
                                <span><b>Gender:</b> ${gender}</span> | 
                                <span><b>Age:</b> ${age}</span> | 
                                <span><b>Breed:</b> ${breed}</span>
                            </div>
                            
                            <small class="${textClass}">${locationText}</small>
                        </div>
                    </div>
                </li>`;
        });

        html += '</ul>';
        this.resultsContainer.innerHTML = html;
    }

    // 处理点击建议项的方法
    handleItemClick(name, lat, lng, photo) {
        if (!lat || !lng || lat == 0) {
            alert(`Sorry, "${name}" hasn't been sighted yet, so there's no location to show on the map.`);
            this.resultsContainer.innerHTML = '';
            return;
        }

        console.log("Moving map to sighted location:", lat, lng);
        this.resultsContainer.innerHTML = '';
        this.input.value = name;

        if (this.onResultSelected) {
            this.onResultSelected(lat, lng, name, photo);
        }
    }
}