/**
 * MapManager Class - 负责管理所有的地图操作
 * This class handles all map-related activities.
 */
class MapManager {
    constructor(containerId) {
        // 保存地图容器的 ID (save the div id)
        this.containerId = containerId;
        this.map = null;
    }

    /**
     * 初始化地图 (Start the map)
     */
    init() {
        // 1. 创建地图对象，默认先定位到曼彻斯特 (Create map, default to Manchester)
        // [53.4808, -2.2426] 是坐标，13 是缩放等级
        this.map = L.map(this.containerId, {
            zoomControl: false // 禁用默认的左上角缩放按钮
        }).setView([53.4808, -2.2426], 13);

        // 2. 加载 OpenStreetMap 的“瓦片”图层 (Load the map graphics/tiles)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);

        L.control.zoom({
            position: 'bottomright'
        }).addTo(this.map);

        console.log("Map is ready! 地图准备好了！");

        // 3. 立即尝试定位用户 (Try to find the user right away)
        this.locateUser();
    }

    /**
     * 获取用户的地理定位 (Find where the user is)
     */
    locateUser() {
        // 检查浏览器支不支持定位 (Check if browser supports geolocation)
        if (!navigator.geolocation) {
            alert("Your browser does not support location. 你的浏览器不支持定位。");
            return;
        }

        // 浏览器开始找人 (Browser starts searching)
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // 把地图移动到用户所在的位置 (Move map to user's location)
                this.map.setView([lat, lng], 15);

                // 在用户位置打个红点 (Add a marker at user's spot)
                L.marker([lat, lng]).addTo(this.map)
                    .bindPopup("You are here! 你在这里！")
                    .openPopup();
            },
            (error) => {
                console.warn("Location access denied. 用户拒绝了定位请求。");
            }
        );
    }
}

