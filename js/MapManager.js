/**
 * MapManager Class - 负责管理所有的地图操作
 */
class MapManager {
    constructor(containerId) {
        this.containerId = containerId;
        this.map = null;
        // 【新增】用于存储用户的真实位置，初始为 null
        this.userLocation = null;
    }

    /**
     * 初始化地图
     */
    init() {
        // 默认定位到曼彻斯特
        this.map = L.map(this.containerId, {
            zoomControl: false
        }).setView([53.4808, -2.2426], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);

        L.control.zoom({
            position: 'bottomright'
        }).addTo(this.map);

        console.log("Map initialized.");

        // 启动定位
        this.locateUser();
    }

    /**
     * 获取用户的地理定位
     */
    locateUser() {
        if (!navigator.geolocation) {
            console.log("Geolocation is not supported by this browser.");
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // 【关键修改】将坐标存入实例变量，供计算距离使用
                this.userLocation = L.latLng(lat, lng);

                // 把地图移动到用户位置
                this.map.setView([lat, lng], 15);

                // 添加用户位置标记（蓝色小点）
                L.circleMarker([lat, lng], {
                    color: '#3388ff',
                    fillColor: '#3388ff',
                    fillOpacity: 0.5,
                    radius: 10
                }).addTo(this.map).bindPopup("You are here").openPopup();

                console.log("User location fixed:", lat, lng);
            },
            (error) => {
                console.warn("Location access denied or timed out.");
            },
            { enableHighAccuracy: true } // 开启高精度模式
        );
    }
}
