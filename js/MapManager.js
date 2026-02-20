/**
 * MapManager.js
 * Purpose: Handles Leaflet map initialization, user geolocation,
 * fetching nearby pet sightings, and submitting new sightings.
 * * Maintainability focus: Centralized path management, robust error handling,
 * and clear separation of concerns.
 */
class MapManager {
    /**
     * @param {string} containerId - The HTML element ID where the map will be injected.
     */
    constructor(containerId) {
        this.containerId = containerId;
        this.map = null;
        this.userLocation = null;
    }

    /**
     * Initializes the map using OpenStreetMap tiles.
     * Sets default view to Manchester if geolocation is pending.
     */
    init() {
        // Initialize Leaflet map with zoom controls disabled for custom positioning
        this.map = L.map(this.containerId, { zoomControl: false }).setView([53.4808, -2.2426], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);

        // Position zoom controls at bottom-right for better UI accessibility
        L.control.zoom({ position: 'bottomright' }).addTo(this.map);
        this.locateUser();
    }


    //  Attempts to retrieve user's GPS coordinates via Browser API.

    locateUser() {
        if (!window.isSecureContext) {
            console.warn("HTTP context detected. Simulating location for demo purposes.");
            // since the posndion does't have http saftry protol i had to force to locate in manchester to make sure the fetch function work
            const mockLat = 53.4808;
            const mockLng = -2.2426;
            this.userLocation = [mockLat, mockLng];
            this.map.setView(this.userLocation, 14);
            this.fetchNearbyPets(mockLat, mockLng);
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                this.userLocation = [lat, lng];
                this.map.setView(this.userLocation, 14);

                // Add a distinct blue marker for the user's current location
                L.circleMarker(this.userLocation, {
                    color: '#007bff',
                    fillColor: '#007bff',
                    fillOpacity: 0.5,
                    radius: 8
                }).addTo(this.map).bindPopup("You are here");

                this.fetchNearbyPets(lat, lng);
            },
            () => {
                // Fallback to default location if user denies access
                this.fetchNearbyPets(53.4808, -2.2426);
            }
        );
    }

    /**
     * Fetches pet sighting data from the backend based on coordinates.
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     */
    async fetchNearbyPets(lat, lng) {
        try {
            // Maintainability: Determine directory depth to ensure relative paths work in all views
            const isSubFolder = window.location.pathname.includes('controllers/');
            const prefix = isSubFolder ? '../' : '';
            const browsePath = isSubFolder ? 'browse.php' : 'controllers/browse.php';
            const apiUrl = isSubFolder ? 'api_nearby_pets.php' : 'controllers/api_nearby_pets.php';

            const response = await fetch(`${apiUrl}?lat=${lat}&lng=${lng}&radius=50`);
            if (!response.ok) throw new Error("Network response was not ok");

            const pets = await response.json();

            // Custom icon configuration for pet markers
            const customIcon = L.icon({
                iconUrl: 'https://cdn0.iconfinder.com/data/icons/small-n-flat/24/678111-map-marker-512.png',
                iconSize: [35, 35],
                iconAnchor: [17, 35],
                popupAnchor: [0, -30]
            });

            pets.forEach(pet => {
                const petId = pet.id;

                // Action area logic: Show update form only if user is logged in
                let actionHTML = "";
                if (window.isUserLoggedIn && petId) {
                    actionHTML = `
                        <div style="margin-top:10px; border-top:1px solid #eee; padding-top:10px;">
                            <input type="text" id="comment-${petId}" 
                                   class="form-control input-sm" 
                                   placeholder="Describe what you see" 
                                   style="margin-bottom:5px; height:30px;">
                            <button onclick="window.myMapInstance.submitSighting(${petId})" 
                                    class="btn btn-warning btn-block" 
                                    style="padding: 5px; border-radius:4px; font-weight:bold;">
                                Update Comment
                            </button>
                        </div>`;
                }

                // Inject marker with formatted Popup UI
                L.marker([pet.latitude, pet.longitude], { icon: customIcon })
                    .addTo(this.map)
                    .bindPopup(`
                    <div style="text-align:center; width:220px; font-family: Arial, sans-serif;">
                        <img src="${prefix}images/pet-image/${pet.photo_url}" 
                             style="width:100%; height:120px; object-fit:cover; border-radius:8px; margin-bottom:8px;">
                        <b style="font-size: 18px; color: #d9534f; display: block;">${pet.name}</b>
                        
                        <div style="display: flex; gap: 5px; margin-top: 10px;">
                            <a href="http://maps.google.com/?q=${pet.latitude},${pet.longitude}" 
                               target="_blank" class="btn btn-success btn-xs" 
                               style="flex:1; color:white; text-decoration:none; background-color: #28a745; padding: 5px; border-radius:4px; text-align:center;">
                               Navigate
                            </a>
                            <a href="${browsePath}?search=${encodeURIComponent(pet.name)}" 
                               class="btn btn-primary btn-xs" 
                               style="flex:1; color:white; text-decoration:none; background-color: #007bff; padding: 5px; border-radius:4px; text-align:center;">
                               Detail
                            </a>
                        </div>
                        ${actionHTML}
                    </div>
                `);
            });
        } catch (e) {
            console.error("Fetch Nearby Pets Error:", e);
        }
    }

    /**
     * Submits a new comment for a specific pet sighting via AJAX.
     * @param {number} petId - The unique ID of the pet.
     */
    async submitSighting(petId) {
        // Validation for Maintainability: Ensure petId exists before proceeding
        if (!petId || petId === 'undefined') {
            alert("Error: Cannot identify this pet. Please try refreshing the page.");
            return;
        }

        const commentInput = document.getElementById(`comment-${petId}`);
        const comment = commentInput ? commentInput.value.trim() : "";

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
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    pet_id: Number(petId),
                    comment: comment
                })
            });

            const result = await response.json();
            if (result.success) {
                alert("Sighting report updated successfully!");
                this.map.closePopup(); // Auto-close popup on success for better UX
            } else {
                alert("Update failed: " + (result.error || "Unknown server error"));
            }
        } catch (e) {
            console.error("Submit Sighting Error:", e);
            alert("Connection error. Please try again later.");
        }
    }
}

