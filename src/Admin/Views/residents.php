<div class="wrap" x-data="residentsManager">
    <h1 class="wp-heading-inline">Data Penduduk</h1>

    <!-- Debug Info -->
    <div style="margin: 10px 0; padding: 10px; border: 1px solid #ccc; background: #fff;" x-show="true">
        <strong>Status:</strong> <span x-text="'Alpine Active'">Alpine Not Running</span>.
        <strong>Data:</strong> <span x-text="residents.length">0</span> residents.
        <strong>Loading:</strong> <span x-text="loading">false</span>.
        <strong>Modal State:</strong> <span x-text="showModal">false</span>.
        <button type="button" @click="openModal('add'); console.log('Button Clicked')" class="button button-primary">Tambah Penduduk</button>

        <hr>
        <strong>Test Counter:</strong>
        <div x-data="{ count: 0 }">
            <span x-text="count"></span>
            <button type="button" @click="count++" class="button button-small">Increment</button>
        </div>
    </div>

    <!-- <button type="button" @click="openModal('add')" class="page-title-action">Tambah Penduduk</button> -->
    <hr class="wp-header-end">

    <div class="wp-desa-container" style="margin-top: 20px;">

        <!-- Search & Filter (Placeholder) -->
        <div style="margin-bottom: 15px;">
            <input type="text" placeholder="Cari NIK / Nama..." class="regular-text">
        </div>

        <!-- Table -->
        <table class="wp-list-table widefat fixed striped table-view-list residents">
            <thead>
                <tr>
                    <th>NIK</th>
                    <th>Nama Lengkap</th>
                    <th>Jenis Kelamin</th>
                    <th>Pekerjaan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr>
                        <td colspan="5">Memuat data...</td>
                    </tr>
                </template>
                <template x-for="resident in residents" :key="resident.id">
                    <tr>
                        <td x-text="resident.nik"></td>
                        <td x-text="resident.nama_lengkap"></td>
                        <td x-text="resident.jenis_kelamin"></td>
                        <td x-text="resident.pekerjaan"></td>
                        <td>
                            <button @click="editResident(resident)" class="button button-small">Edit</button>
                            <button @click="deleteResident(resident.id)" class="button button-small button-link-delete">Hapus</button>
                        </td>
                    </tr>
                </template>
                <template x-if="!loading && residents.length === 0">
                    <tr>
                        <td colspan="5">Belum ada data penduduk.</td>
                    </tr>
                </template>
            </tbody>
        </table>

    </div>

    <!-- Modal Form -->
    <div x-show="showModal" class="wp-desa-modal-overlay" x-transition x-cloak>
        <div class="wp-desa-modal" @click.away="showModal = false">
            <h2 x-text="modalMode === 'add' ? 'Tambah Penduduk' : 'Edit Penduduk'"></h2>
            <form @submit.prevent="saveResident">
                <table class="form-table">
                    <tr>
                        <th><label>NIK</label></th>
                        <td><input type="text" x-model="form.nik" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label>Nama Lengkap</label></th>
                        <td><input type="text" x-model="form.nama_lengkap" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label>Jenis Kelamin</label></th>
                        <td>
                            <select x-model="form.jenis_kelamin">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Pekerjaan</label></th>
                        <td><input type="text" x-model="form.pekerjaan" class="regular-text"></td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary">Simpan</button>
                    <button type="button" @click="showModal = false" class="button">Batal</button>
                </p>
            </form>
        </div>
    </div>

</div>

<style>
    [x-cloak] {
        display: none !important;
    }

    .wp-desa-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .wp-desa-modal {
        background: #fff;
        padding: 20px;
        width: 500px;
        max-width: 90%;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
    }
</style>