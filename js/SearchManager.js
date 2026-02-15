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
        this.input.addEventListener('input', () => {
            const term = this.input.value;

            // 清除之前的计时器
            clearTimeout(this.debounceTimer);

            if (term.length > 2) {
                // 设置 300ms 延迟，如果用户还在打字，之前的请求会被取消
                this.debounceTimer = setTimeout(() => {
                    this.performSearch(term);
                }, 300);
            } else {
                this.resultsContainer.innerHTML = '';
            }
        });
    }

    async performSearch(term) {
        try {
            // 使用 fetch 请求我们刚写的 PHP 接口
            // Use fetch to call our new PHP API
            // 注意路径，如果在 home.phtml 调用，路径应该是 controllers/api_search_pets.php
            const response = await fetch(`controllers/api_search_pets.php?term=${encodeURIComponent(term)}`);
            const data = await response.json();

            // 把数据画出来 (Render the suggestions)
            this.renderResults(data);
        } catch (error) {
            console.error("Search failed! 搜索出错了:", error);
        }
    }

    renderResults(pets) {
        if (pets.length === 0) {
            this.resultsContainer.innerHTML = '';
            return;
        }

        // 提升层级 z-index: 2000
        let html = '<ul class="list-group" style="position:absolute; z-index:2000; width:100%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';

        pets.forEach(pet => {
            // --- 新增判断逻辑 ---
            // 检查是否有经纬度数据（只有被目击过的宠物才有坐标）
            const hasLocation = pet.latitude && pet.longitude && pet.latitude != 0;

            // 根据是否有坐标，定义不同的提示文字和颜色
            const locationText = hasLocation
                ? "Click to see sighting location"
                : "No sightings reported yet";

            // 如果没有位置，我们将文字设为红色 (text-danger)
            const textClass = hasLocation ? "text-muted" : "text-danger";
            // ------------------

            html += `
            <li class="list-group-item" style="cursor:pointer;" 
                onclick="window.searchManagerInstance.handleItemClick('${pet.name}', ${pet.latitude || 0}, ${pet.longitude || 0}, '${pet.photo_url}')">
                <div class="row">
                    <div class="col-xs-3">
                        <img src="images/pet-image/${pet.photo_url}" class="img-responsive img-rounded">
                    </div>
                    <div class="col-xs-9">
                        <strong>${pet.name}</strong> (${pet.species})<br>
                        <small class="${textClass}">${locationText}</small>
                    </div>
                </div>
            </li>`;
        });

        html += '</ul>';
        this.resultsContainer.innerHTML = html;
    }

    // 新增：处理点击建议项的方法
    handleItemClick(name, lat, lng, photo) {
        // 1. 检查坐标是否存在。
        // 如果 lat 或 lng 是 null、undefined、或者是 0 (通常代表数据库没存坐标)
        if (!lat || !lng || lat == 0) {
            alert(`Sorry, "${name}" hasn't been sighted yet, so there's no location to show on the map.`);
            this.resultsContainer.innerHTML = ''; // 关闭下拉列表
            return;
        }

        // 2. 如果坐标正常，则执行原来的逻辑
        console.log("Moving map to sighted location:", lat, lng);

        this.resultsContainer.innerHTML = '';
        this.input.value = name;

        if (this.onResultSelected) {
            this.onResultSelected(lat, lng, name, photo);
        }
    }
}