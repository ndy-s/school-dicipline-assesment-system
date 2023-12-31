<template>
    <div class="overflow-x-scroll border border-gray-500 sm:rounded-lg my-2">
        <div class="sm:rounded-lg text-gray-300 p-2">
            <table class="min-w-full divide-y divide-gray-500 table-fixed font-mono">
                <thead class="border-b border-gray-700 rounded-lg">
                    <tr>
                        <th 
                            scope="col" 
                            class="py-3 px-6 w-3/12 text-xs font-semibold tracking-wider text-left text-gray-400 uppercase"
                            v-for="(oneTableHead, index) in tableHead"
                            :key="index"
                        >
                            <span 
                                class="inline-flex w-full justify-between" 
                                @click="sort(oneTableHead)"
                                v-if="oneTableHead === 'nama' || oneTableHead === 'peran'"
                            >
                                {{ oneTableHead }}
                                <font-awesome-icon :icon="['fas', 'arrow-up-a-z']" v-if="params.field === oneTableHead && params.direction === 'asc'"/>
                                <font-awesome-icon :icon="['fas', 'arrow-down-z-a']" v-if="params.field === oneTableHead && params.direction === 'desc'"/>
                            </span>
                            <span v-else>
                                {{ oneTableHead }}
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr 
                        v-if="tablePaginate.data.length"
                        class="border-b border-gray-700 rounded-lg"
                        v-for="(oneTableData, index) in tablePaginate.data"
                        :key="index"
                    >
                        <td 
                            class="py-4 px-6 text-m text-gray-300 whitespace-nowrap"
                            v-for="(oneTableHead, index) in tableHead"
                            :key="index"
                        >
                            <div class="flex items-center">
                                <img 
                                    v-if="oneTableHead === 'nama' && oneTableData['image_path']"
                                    :src="`/img/${oneTableData['image_path']}`"
                                    class="w-6 flex mr-4 rounded-full"
                                >
                                <span v-if="oneTableHead === 'aksi' && canCreate">
                                    <button title="Ubah" @click="editFunc(oneTableData)">
                                        <font-awesome-icon :icon="['fas', 'pen-clip']" style="color: paleturquoise;"/>
                                    </button>
                                    &nbsp;
                                    <button title="Hapus" @click="deleteFunc(oneTableData)">
                                        <font-awesome-icon :icon="['fas', 'trash-can']" style="color: darkorange;"/>
                                    </button>
                                </span>
                                <span v-else-if="oneTableHead === 'tanggal dibuat'">
                                    {{ formatDate(oneTableData['created_at']) }}
                                </span>
                                <span v-else-if="oneTableHead === 'orang tua' || oneTableHead === 'akun guru'">
                                    {{ oneTableData?.user?.nama ? `${oneTableData.user.nama} (${oneTableData.user.token || '-'})` : '-' }}
                                </span>
                                <span v-else-if="oneTableHead === 'guru mata pelajaran' || oneTableHead === 'guru'">
                                    {{ oneTableData?.guru?.nama ? `${oneTableData.guru.nama} (${oneTableData.guru.nip || '-'})` : '-' }}
                                </span>
                                <span v-else-if="oneTableHead === 'siswa'">
                                    {{ oneTableData?.siswa?.nama ? `${oneTableData.siswa.nama} (${oneTableData.siswa.nis || '-'})` : '-' }}
                                </span>
                                <span v-else-if="oneTableHead === 'kategori pelanggaran'">
                                    {{ oneTableData?.s_o_p?.kategori ?? '-' }}
                                </span>
                                <span v-else-if="oneTableHead === 'mata pelajaran'">
                                    {{ oneTableData?.mata_pelajaran?.nama ? `${oneTableData.mata_pelajaran.nama} (Kelas ${oneTableData.mata_pelajaran.kelas || '-'})` : '-' }}
                                </span>
                                <a
                                    v-else-if="oneTableHead === 'bukti dokumentasi' && oneTableData['bukti_path']"
                                    :href="`/bukti/${oneTableData['bukti_path']}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <img
                                        :src="`/bukti/${oneTableData['bukti_path']}`"
                                        class="w-6 h-6 rounded-sm"
                                        alt="Image"
                                    >
                                </a>
                                <span v-else class="flex">{{ oneTableData[oneTableHead] ?? '-' }}</span>
                            </div>

                        </td>
                    </tr>
                    <tr v-else>
                        <td class="py-4 px-6 text-m text-gray-300 whitespace-nowrap text-center col-span-full" :colspan="tableHead.length">Data tidak ditemukan!</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex flex-wrap justify-center mt-2" v-show="tablePaginate.links.length > 3">
                <template v-for="(link, key) in tablePaginate.links" :key="key">
                    <div 
                        v-if="link.url === null" 
                        :key="`${key}-disabled`" 
                        class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-white border rounded"
                        v-html="link.label"
                    />
                    <Link 
                        v-else 
                        :key="key" 
                        class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white hover:text-gray-900 text-gray-200" 
                        :class="{'bg-white text-gray-900': link.active }" 
                        :href="link.url"
                    >
                        <span v-html="link.label"></span>
                    </Link>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { format } from 'date-fns';
    import { id } from 'date-fns/locale';

    const props = defineProps({
        tableHead: Array,
        tablePaginate: Object,
        tableFilters: Object,
        params: Object,
        editFunc: Function,
        deleteFunc: Function,
        canCreate: Boolean,
    });

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        const dateFormat = "dd MMMM yyyy";
        return format(date, dateFormat, { locale: id });
    };

    const sort = (field) => {
        props.params.field = field;
        props.params.direction = props.params.direction === 'asc' ? 'desc' : 'asc';
    };
</script>