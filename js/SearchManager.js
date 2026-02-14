/**
 * SearchManager Class - 处理实时搜索建议
 * Handles live search suggestions using fetch()
 */
class SearchManager {
    constructor(inputId, resultsId) {
        this.input = document.getElementById(inputId);
        this.resultsContainer = document.getElementById(resultsId);
        this.debounceTimer = null; // 新增：计时器
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
            const response = await fetch(`api_search_pets.php?term=${encodeURIComponent(term)}`);
            const data = await response.json();

            // 把数据画出来 (Render the suggestions)
            this.renderResults(data);
        } catch (error) {
            console.error("Search failed! 搜索出错了:", error);
        }
    }

    renderResults(pets) {
        // 仿照 Workshop 15.6，生成一个简单的列表
        let html = '<ul class="list-group" style="position:absolute; z-index:1000; width:100%;">';

        pets.forEach(pet => {
            html += `
                <li class="list-group-item">
                    <a href="pet_details.php?id=${pet.id}">${pet.name} (${pet.species})</a>
                </li>`;
        });

        html += '</ul>';
        this.resultsContainer.innerHTML = html;
    }
}