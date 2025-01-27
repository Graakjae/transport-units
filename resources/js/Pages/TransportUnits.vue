<template>
    <div class="flex flex-col items-center my-8">
        <div class="flex space-x-4 mb-8">
            <button
                @click="filterType('trailer')"
                :class="[
                    'text-white px-4 py-2 rounded flex items-center justify-between gap-3 hover:bg-[#069468]',
                    selectedType === 'trailer' ? 'bg-[#069468]' : 'bg-gray-400',
                ]"
            >
                Trailers
                <img
                    src="/car-trailer-white.png"
                    alt="Trailer"
                    class="w-6 h-6 mr-2"
                />
            </button>
            <button
                @click="filterType('truck')"
                :class="[
                    'text-white px-4 py-2 rounded flex items-center justify-between gap-3 hover:bg-[#069468]',
                    selectedType === 'truck' ? 'bg-[#069468]' : 'bg-gray-400',
                ]"
            >
                Trucks
                <img
                    src="/delivery-truck-white.png"
                    alt="Trailer"
                    class="w-6 h-6 mr-2"
                />
            </button>
        </div>

        <button
            @click="openCreateModal"
            class="absolute right-8 top-8 rounded-full bg-[#069468] h-[50px] w-[50px] flex items-center justify-center text-white text-[20px] gap-1"
        >
            +
            <img
                :src="[
                    selectedType === 'truck'
                        ? '/delivery-truck-white.png'
                        : '/car-trailer-white.png',
                ]"
                alt="Trailer"
                class="w-6 h-6 mr-1"
            />
        </button>

        <input
            class="rounded-md border border-gray-300 p-2 mb-4"
            v-model="search"
            placeholder="Search"
            @input="searchUnits"
        />

        <div
            v-for="unit in paginatedUnits"
            :key="unit.id"
            class="w-[300px] p-4 border rounded-md"
        >
            <p>{{ unit.id }} {{ unit.name }}</p>
        </div>

        <div class="flex space-x-4 mt-4">
            <button
                class="bg-[#069468] py-2 text-white rounded w-[100px]"
                @click="prevPage"
                :disabled="currentPage === 1"
            >
                Previous
            </button>
            <button
                class="bg-[#069468] py-2 text-white rounded w-[100px]"
                @click="nextPage"
                :disabled="currentPage === totalPages"
            >
                Next
            </button>
        </div>

        <div
            v-if="showCreateModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
        >
            <form
                @submit.prevent="createTransportUnit"
                class="bg-white p-8 flex flex-col gap-4 rounded-md"
            >
                <h2 class="text-2xl font-bold">Create Transport Unit</h2>
                <input
                    v-model="newUnit.name"
                    placeholder="Name"
                    required
                    class="border-gray-500 border p-2 rounded-sm"
                />
                <select
                    v-model="newUnit.type"
                    required
                    class="border-gray-500 border p-2 rounded-sm"
                >
                    <option value="trailer">Trailer</option>
                    <option value="truck">Truck</option>
                </select>
                <button
                    type="submit"
                    class="bg-[#069468] py-2 text-white rounded"
                >
                    Create
                </button>
                <button @click="showCreateModal = false">Close</button>
            </form>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

export default {
    setup() {
        const transportUnits = ref([]);
        const search = ref("");
        const newUnit = ref({ name: "", type: "trailer" });
        const showCreateModal = ref(false);
        const selectedType = ref("trailer");
        const currentPage = ref(1);
        const pageSize = 20;

        onMounted(() => {
            fetchUnits();
        });

        const fetchUnits = async () => {
            const response = await axios.get("/api/transport-units", {
                params: {
                    type: selectedType.value,
                    search: search.value,
                    page: currentPage.value,
                    pageSize,
                },
            });
            transportUnits.value = response.data;
        };

        const filterType = (type) => {
            selectedType.value = type;
            currentPage.value = 1;
            fetchUnits();
        };

        const searchUnits = async () => {
            const response = await axios.get("/api/transport-units", {
                params: { search: search.value, type: selectedType.value },
            });

            // Always filter on the frontend
            transportUnits.value = response.data.filter((unit) =>
                unit.name.toLowerCase().includes(search.value.toLowerCase())
            );
        };

        const createTransportUnit = async () => {
            await axios.post("/api/transport-units", newUnit.value);
            newUnit.value = { name: "", type: "trailer" };
            showCreateModal.value = false;
            fetchUnits();
        };

        const openCreateModal = () => {
            showCreateModal.value = true;
        };

        const paginatedUnits = computed(() => {
            const start = (currentPage.value - 1) * pageSize;
            const end = start + pageSize;
            return transportUnits.value.slice(start, end);
        });

        const totalPages = computed(() => {
            return Math.ceil(transportUnits.value.length / pageSize);
        });

        const nextPage = () => {
            if (currentPage.value < totalPages.value) {
                currentPage.value++;
                fetchUnits();
            }
        };

        const prevPage = () => {
            if (currentPage.value > 1) {
                currentPage.value--;
                fetchUnits();
            }
        };

        return {
            transportUnits,
            search,
            newUnit,
            showCreateModal,
            filterType,
            searchUnits,
            createTransportUnit,
            openCreateModal,
            selectedType,
            currentPage,
            paginatedUnits,
            totalPages,
            nextPage,
            prevPage,
        };
    },
};
</script>
