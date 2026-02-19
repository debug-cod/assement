
 // Manages Leaflet map: initialization, user geolocation, and nearby pet visualization.

class MapManager {
    /**
     * @param {string} containerId - The HTML ID of the map div.
     */
    constructor(containerId) {
        this.containerId = containerId;
        this.map = null;
        this.userLocation = null;
    }

    //  Initializes the map with OpenStreetMap tiles and custom zoom controls.

    init() {
        this.map = L.map(this.containerId, {zoomControl: false}).setView([53.4808, -2.2426], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);
        L.control.zoom({position: 'bottomright'}).addTo(this.map);
        this.locateUser();
    }

    /**
     * Attempts to get the user's current GPS coordinates.
     * Falls back to a default location (Manchester) if access is denied.
     */
    locateUser() {
        if (!navigator.geolocation)
            return;
        if (!navigator.geolocation) {
            console.log("Geolocation is not supported by this browser.");
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                this.userLocation = L.latLng(lat, lng);
                this.map.setView([lat, lng], 13);

                // ur location blue dot
                L.circleMarker([lat, lng], {
                    color: '#007bff',
                    fillColor: '#007bff',
                    fillOpacity: 0.8,
                    radius: 10
                })
                    .addTo(this.map)
                    .bindPopup("<b style='font-size:14px;'>You are here!</b>")
                    .openPopup(); // auto open up popup
                // ------------------------------------------

                this.fetchNearbyPets(lat, lng);
            },
            (error) => {
                console.warn("Location access denied.");
                // if user denied use this logic so call user denied logic apply
                this.handleLocationError();
                {
                    // 设置一个默认坐标（例如曼彻斯特坐标）
                    const defaultLat = 53.4808;
                    const defaultLng = -2.2426;

                    this.map.setView([defaultLat, defaultLng], 12);

                    // pop up a suggestion braket
                    L.popup()
                        .setLatLng([defaultLat, defaultLng])
                        .setContent(`
                <div style="text-align:center; padding:5px;">
                    <b style="color:#d9534f;">Location Access Denied</b><br>
                    You can still browse the map manually <br>
                    or use the search bar to find pets.
                </div>
            `)
                        .openOn(this.map);

                    // optional choices even the user denied to share their location still should show up neaby pet
                    this.fetchNearbyPets(defaultLat, defaultLng);
                }
            },
            {enableHighAccuracy: true}
        );
    }

    /**
     * Fetches up to 5 nearby pets from the server based on coordinates.
     * @param {number} lat - Latitude of the search center.
     * @param {number} lng - Longitude of the search center.
     * @returns {Promise<void>}
     */
    async fetchNearbyPets(lat, lng) {
        try {
            // --- 核心修复：定义所有路径变量 ---
            const path = window.location.pathname;
            const isSubFolder = path.includes('browse.php') || path.includes('controllers/');
            const apiUrl = isSubFolder ? 'api_nearby_pets.php' : 'controllers/api_nearby_pets.php';
            const prefix = isSubFolder ? '../' : '';
            const browsePath = isSubFolder ? 'browse.php' : 'controllers/browse.php';
            // ------------------------------

            const response = await fetch(`${apiUrl}?lat=${lat}&lng=${lng}&radius=50`);
            const data = await response.json();

            if (!Array.isArray(data)) return;

            data.slice(0, 5).forEach(pet => {
                const imgPath = `${prefix}images/pet-image/${pet.photo_url}`;

                // Prepare the footer content based on the login status.
                let actionHTML = "";
                if (window.isUserLoggedIn) {
                    actionHTML = `
                        <div style="margin-top:10px; border-top:1px solid #eee; padding-top:10px;">
                            <input type="text" id="comment-${pet.id}" class="form-control input-sm" 
                                   placeholder="what stuff did you saw" style="margin-bottom:5px;">
                            <button onclick="window.myMapInstance.submitSighting(${pet.id})" 
                                    class="btn btn-warning btn-sm btn-block" 
                                    style="font-weight:bold;">
                                update sighting comment detail
                            </button>
                        </div>`;
                } else {
                    actionHTML = `
                        <div style="margin-top:10px; border-top:1px solid #eee; padding-top:10px;">
                            <p style="font-size:16px; color:#d9534f; font-weight:bold; margin:5px 0;">
                                Login to report a sighting
                            </p>
                        </div>`;
                }

                // Bind a Popup with a complete UI (using circleMarker)
                L.circleMarker([pet.latitude, pet.longitude], {color: 'green', radius: 8})
                    .addTo(this.map)
                    .bindPopup(`
                        <div style="text-align:center; width:220px; font-family: Arial, sans-serif;">
                            <img src="${imgPath}" style="width:100%; height:120px; object-fit:cover; border-radius:8px; margin-bottom:8px;">
                            <b style="font-size: 18px; color: #d9534f; display: block;">${pet.name}</b>
                            
                            <div style="font-size: 14px; margin: 5px 0; color: #666;">
                                Distance: <b>${parseFloat(pet.distance).toFixed(2)} km</b>
                            </div>
                            
                            <div style="display: flex; gap: 5px; margin-top: 10px;">
                                <a href="https://www.google.com/maps/search/?api=1&query=${pet.latitude},${pet.longitude}" 
                                   target="_blank" class="btn btn-success btn-xs" 
                                   style="flex:1; color:white; text-decoration:none; background-color: #28a745; padding: 5px; border-radius:4px;">
                                   Navigate
                                </a>
                                <a href="${browsePath}?search=${encodeURIComponent(pet.name)}" 
                                   class="btn btn-primary btn-xs" 
                                   style="flex:1; color:white; text-decoration:none; background-color: #007bff; padding: 5px; border-radius:4px;">
                                   Detail
                                </a>
                            </div>

                            ${actionHTML}
                        </div>
                    `);
            });
        } catch (e) {
            console.error("Nearby Pets Error:", e);
        }
    }

    async submitSighting(petId) {
        // 检查 petId 是否有效
        if (!petId || petId === 'null' || petId === 'undefined') {
            console.error("Critical Error: petId is invalid!", petId);
            alert("Error: Cannot identify this pet. Please re-search and try again.");
            return;
        }

        const commentInput = document.getElementById(`comment-${petId}`);
        if (!commentInput) {
            alert("System error: Input field not found.");
            return;
        }

        const comment = commentInput.value.trim();
        if (!comment) {
            alert("Please enter a description!");
            return;
        }

        try {
            const apiUrl = window.location.pathname.includes('controllers/')
                ? 'api_add_sighting.php'
                : 'controllers/api_add_sighting.php';

            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    pet_id: Number(petId), // 强制转为数字类型
                    comment: comment
                })
            });

            const result = await response.json();
            if (result.success) {
                alert("Sighting report updated!");
                commentInput.value = '';
                this.map.closePopup();
            } else {
                alert("Update failed: " + result.error);
            }
        } catch (e) {
            console.error("Submit Error:", e);
            alert("Connection error. Check console.");
        }
    }
}





