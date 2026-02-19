
//  Handles live search suggestions with debouncing and dynamic result injection.


class SearchManager {
    /**
     * @param {string} inputId - ID of the search input field.
     * @param {string} resultsId - ID of the container where suggestions will be rendered.
     */
    constructor(inputId, resultsId) {
        this.input = document.getElementById(inputId);
        this.resultsContainer = document.getElementById(resultsId);
        this.debounceTimer = null;
        this.onResultSelected = null; // Callback triggered when a pet is selected

    }


    //Binds input events and handles search clearing logic.


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
                this.resultsContainer.innerHTML = '';

                // if user clean the search bar, users current location and nearby pet gonna reappear
                if (term.length === 0) {
                    console.log("Search cleared, resetting to nearby pets...");
                    // Trigger a custom callback, or directly call the global map instance.
                    if (window.myMapInstance) {
                        window.myMapInstance.locateUser(); // Reset map if search is cleared
                    }
                }
            }
        });
    }

    /**
     * Performs an asynchronous API call to search for pets by name/species/breed.
     * @param {string} term - The search keyword entered by the user.
     * @returns {Promise<void>}
     */

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

    /**
     * Renders pet data into a Bootstrap list-group with relative path handling.
     * @param {Array<Object>} data - Array of pet objects returned from the API.
     */
    renderResults(data) {
        if (!data || data.length === 0) {
            this.resultsContainer.innerHTML = '<ul class="list-group"><li class="list-group-item">No pets found</li></ul>';
            return;
        }

        const path = window.location.pathname;
        const isSubFolder = path.includes('browse.php') || path.includes('controllers/');
        const imgPrefix = isSubFolder ? '../' : '';

        let html = '<ul class="list-group" style="position:absolute; z-index:1000; width:100%;">';

        data.forEach(pet => {
            const locationText = pet.latitude ? `Sighted at: ${pet.latitude.toFixed(2)}, ${pet.longitude.toFixed(2)}` : 'No location reported';
            const textClass = pet.latitude ? 'text-success' : 'text-danger';

            const gender = pet.gender || 'Unknown';
            const age = pet.age || 'Unknown';
            const breed = pet.breed || 'Unknown';
            // Pass pet details including ID to the handler
            html += `
    <li onclick="window.searchManagerInstance.handleItemClick('${pet.name}', ${pet.latitude}, ${pet.longitude}, '${pet.photo_url}', ${pet.id})" 
        class="list-group-item">
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

    /**
     * Handles selection of a suggestion item, moving the map or alerting the user.
     * @param {string} name - Pet name.
     * @param {number} lat - Sighting latitude.
     * @param {number} lng - Sighting longitude.
     * @param {string} photo - Filename of the pet's photo.
     */

    // this is for if the pet hasn't got any sighting is will pop this message to alert user no location available for 0 sighting pet
    handleItemClick(name, lat, lng, photo, petId) {
        // Validation for pets without GPS coordinates
        if (!lat || !lng || lat == 0) {
            alert(`Sorry, "${name}" hasn't been sighted yet...`);
            this.resultsContainer.innerHTML = '';
            return;
        }
        // Trigger the callback defined in main.js
        if (this.onResultSelected) {
            this.onResultSelected(lat, lng, name, photo, petId);
        }

        console.log("Selected Pet ID:", petId); // 调试用
        this.resultsContainer.innerHTML = '';
        this.input.value = name;

        if (this.onResultSelected) {
            // 关键：这里必须把 petId 传给 main.js
            this.onResultSelected(lat, lng, name, photo, petId);
        }
    }
}