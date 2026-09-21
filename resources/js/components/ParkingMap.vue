<script setup lang="ts">
import L from 'leaflet';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import 'leaflet/dist/leaflet.css';

interface ParkingMarker {
    id: number;
    name: string;
    address: string;
    lat: number | null;
    lng: number | null;
}

const props = defineProps<{
    parkings: ParkingMarker[];
}>();

const mapElement = ref<HTMLElement | null>(null);
let map: L.Map | null = null;

onMounted(() => {
    if (!mapElement.value) {
        return;
    }

    L.Icon.Default.mergeOptions({
        iconRetinaUrl: markerIcon2x,
        iconUrl: markerIcon,
        shadowUrl: markerShadow,
    });

    map = L.map(mapElement.value).setView([-12.0464, -77.0428], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    props.parkings.forEach((parking) => {
        if (parking.lat === null || parking.lng === null) {
            return;
        }

        L.marker([parking.lat, parking.lng])
            .addTo(map as L.Map)
            .bindPopup(`<strong>${parking.name}</strong><br>${parking.address}`);
    });
});

onBeforeUnmount(() => {
    map?.remove();
    map = null;
});
</script>

<template>
    <div ref="mapElement" class="h-[360px] w-full rounded-xl" />
</template>
