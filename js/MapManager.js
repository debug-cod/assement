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
            const isViewsFolder = window.location.pathname.includes('views');
            const prefix = isViewsFolder ? '../' : '';
            const apiUrl = `${prefix}controllers/api_nearby_pets.php`;
            const browsePath = isViewsFolder ? 'browse.php' : 'controllers/browse.php';

            const response = await fetch(`${apiUrl}?lat=${lat}&lng=${lng}&radius=50`);
            const data = await response.json();

            // 【关键修复点】检查返回的是否为数组
            if (!Array.isArray(data)) {
                console.error("API并没有返回宠物列表:", data.error || data);
                return;
            }

            // 限制只显示前 5 个 (满足你“显示五个”的要求)
            const topFivePets = data.slice(0, 5);

            topFivePets.forEach(pet => {
                const imgPath = `${prefix}images/pet-image/${pet.photo_url}`;

                L.circleMarker([pet.latitude, pet.longitude], {
                    color: 'green',
                    radius: 8
                })
                    .addTo(this.map)
                    .bindPopup(`
                    <div style="text-align:center; width:150px; font-family: Arial, sans-serif;">
                        <img src="${imgPath}" style="width:100%; max-height:100px; object-fit:cover; border-radius:5px; margin-bottom:8px;">
                        <b style="font-size: 16px; display:block;">${pet.name}</b>
                        
                        <div style="font-size: 15px; margin: 8px 0;">
                            Distance: <b style="color: #2c3e50;">${parseFloat(pet.distance).toFixed(2)} km</b>
                        </div>

                        <a href="${browsePath}?search=${encodeURIComponent(pet.name)}" 
                           class="btn btn-primary btn-xs" 
                           style="color:white; text-decoration:none; display:block; padding: 4px; background-color: #3498db; border-radius:3px;">
                           Check in Detail
                        </a>
                    </div>
                `);
            });
        } catch (e) {
            console.error("Nearby load failed:", e);
        }
    }
}




