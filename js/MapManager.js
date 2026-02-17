/**
 * MapManager Class - 修复了 lat undefined 和路径问题
 */
class MapManager {
    constructor(containerId) {
        this.containerId = containerId;
        this.map = null;
        this.userLocation = null;
    }

    init() {
        this.map = L.map(this.containerId, {zoomControl: false}).setView([53.4808, -2.2426], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);
        L.control.zoom({position: 'bottomright'}).addTo(this.map);
        this.locateUser();
    }

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

                // --- 修复点：确保这里有蓝色的“你在位置”标记 ---
                L.circleMarker([lat, lng], {
                    color: '#007bff', // 蓝色边缘
                    fillColor: '#007bff',
                    fillOpacity: 0.8,
                    radius: 10
                })
                    .addTo(this.map)
                    .bindPopup("<b style='font-size:14px;'>You are here!</b>")
                    .openPopup(); // 自动打开气泡提示用户
                // ------------------------------------------

                this.fetchNearbyPets(lat, lng);
            },
            (error) => {
                console.warn("Location access denied.");
                // 如果用户拒绝，调用处理拒绝的逻辑
                this.handleLocationError();
                {
                    // 设置一个默认坐标（例如曼彻斯特坐标）
                    const defaultLat = 53.4808;
                    const defaultLng = -2.2426;

                    this.map.setView([defaultLat, defaultLng], 12);

                    // 弹出一个友好的提示框
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

                    // 可选：即使拒绝了定位，也可以加载默认位置附近的宠物
                    this.fetchNearbyPets(defaultLat, defaultLng);
                }
            },
            { enableHighAccuracy: true }
        );
    }

    async fetchNearbyPets(lat, lng) {
        try {
            // --- 核心修复：智能路径探测 ---
            const path = window.location.pathname;
            // 如果路径包含 browse.php 或 controllers，说明已经在子目录了
            const isSubFolder = path.includes('browse.php') || path.includes('controllers/');

            const apiUrl = isSubFolder ? 'api_nearby_pets.php' : 'controllers/api_nearby_pets.php';
            const prefix = isSubFolder ? '../' : '';
            const browsePath = isSubFolder ? 'browse.php' : 'controllers/browse.php';

            console.log("Detected Environment. API Path:", apiUrl);
            // ------------------------------

            const response = await fetch(`${apiUrl}?lat=${lat}&lng=${lng}&radius=50`);
            const data = await response.json();

            if (!Array.isArray(data)) return;

            data.slice(0, 5).forEach(pet => {
                // 图片路径也需要根据环境加 ../
                const imgPath = `${prefix}images/pet-image/${pet.photo_url}`;

                L.circleMarker([pet.latitude, pet.longitude], { color: 'green', radius: 8 })
                    .addTo(this.map)
                    .bindPopup(`
                <div style="text-align:center; width:150px;">
                    <img src="${imgPath}" style="width:100%; border-radius:5px; margin-bottom:5px;">
                    <b style="font-size:16px;">${pet.name}</b><br>
                    <div style="font-size:15px; margin:5px 0;">
                        Distance: <b>${parseFloat(pet.distance).toFixed(2)} km</b>
                    </div>
                    <a href="${browsePath}?search=${encodeURIComponent(pet.name)}" 
                       class="btn btn-primary btn-xs" style="color:white; display:block;">
                       Check in Detail
                    </a>
                </div>
            `);
            });
        } catch (e) { console.error("Nearby Pets Error:", e); }
    }
}




