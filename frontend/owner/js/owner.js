/**
 * Owner Standalone JavaScript Logic
 */

let products = [
    { id: 1, nama: 'Siomay Ayam', satuan: 'Porsi', harga: 15000, stok: 48, stok_minimum: 10, kategori: 'Dimsum Kukus', status: 'Aman' },
    { id: 2, nama: 'Hakau Udang', satuan: 'Porsi', harga: 18000, stok: 4, stok_minimum: 10, kategori: 'Dimsum Kukus', status: 'Menipis' },
    { id: 3, nama: 'Lumpia Udang', satuan: 'Porsi', harga: 16000, stok: 2, stok_minimum: 10, kategori: 'Dimsum Goreng', status: 'Menipis' },
    { id: 4, nama: 'Bakpao Coklat', satuan: 'Porsi', harga: 14000, stok: 6, stok_minimum: 10, kategori: 'Bakpao', status: 'Menipis' },
    { id: 5, nama: 'Pangsit Goreng', satuan: 'Porsi', harga: 14000, stok: 24, stok_minimum: 10, kategori: 'Dimsum Goreng', status: 'Aman' },
    { id: 6, nama: 'Dimsum Mozza', satuan: 'Porsi', harga: 20000, stok: 30, stok_minimum: 10, kategori: 'Dimsum Kukus', status: 'Aman' },
];

function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) dropdown.classList.toggle('hidden');
}

window.addEventListener('click', function(e) {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown && !e.target.closest('.relative')) {
        dropdown.classList.add('hidden');
    }
});

function renderProductsTable(items) {
    const tbody = document.getElementById('productTableBody');
    if (!tbody) return;

    if (items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-stone-400">Tidak ada produk yang ditemukan.</td></tr>`;
        return;
    }

    tbody.innerHTML = items.map(p => {
        let badgeClass = 'text-[#2E8B57] bg-[#2E8B57]/10 border-[#2E8B57]/20';
        if (p.status === 'Menipis') {
            badgeClass = 'text-[#C8372D] bg-[#C8372D]/10 border-[#C8372D]/20';
        } else if (p.status === 'Habis') {
            badgeClass = 'text-stone-600 bg-stone-200 border-stone-300';
        }

        return `
            <tr class="hover:bg-stone-50/70 transition">
                <td class="py-4 px-6">
                    <p class="font-bold text-stone-900">${p.nama}</p>
                    <p class="text-[11px] text-stone-400 mt-0.5">${p.kategori}</p>
                </td>
                <td class="py-4 px-6 text-stone-600">${p.satuan}</td>
                <td class="py-4 px-6 font-semibold text-stone-900">Rp ${Number(p.harga).toLocaleString('id-ID')}</td>
                <td class="py-4 px-6 font-bold ${p.stok < p.stok_minimum ? 'text-brand-red' : 'text-stone-900'}">${p.stok}</td>
                <td class="py-4 px-6">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border ${badgeClass}">
                        ${p.status}
                    </span>
                </td>
                <td class="py-4 px-6 text-right whitespace-nowrap">
                    <a href="javascript:void(0)" onclick="openModalEdit(${p.id})" class="text-xs font-semibold text-stone-600 hover:text-stone-900 hover:underline">Edit</a>
                    <span class="text-stone-300 mx-1.5">&middot;</span>
                    <a href="javascript:void(0)" onclick="alert('Riwayat Stok ${p.nama} (FR-23):\\n- Stok Saat Ini: ${p.stok} porsi')" class="text-xs font-semibold text-stone-600 hover:text-stone-900 hover:underline">Riwayat</a>
                    <span class="text-stone-300 mx-1.5">&middot;</span>
                    <a href="javascript:void(0)" onclick="hapusProduk(${p.id})" class="text-xs font-semibold text-brand-red hover:underline">Hapus</a>
                </td>
            </tr>
        `;
    }).join('');

    const footerInfo = document.getElementById('tableFooterInfo');
    if (footerInfo) {
        footerInfo.textContent = `Menampilkan ${items.length} dari ${products.length} produk · Dihitung otomatis dari riwayat stok produk`;
    }
}

function filterProducts() {
    const searchVal = (document.getElementById('searchProduct')?.value || '').toLowerCase();
    const catVal = document.getElementById('filterCategory')?.value || 'Semua';

    const filtered = products.filter(p => {
        const matchSearch = p.nama.toLowerCase().includes(searchVal);
        const matchCat = (catVal === 'Semua') || (p.kategori === catVal);
        return matchSearch && matchCat;
    });

    renderProductsTable(filtered);
}

function openModalTambah() {
    document.getElementById('modalTambahProduk')?.classList.remove('hidden');
}

function closeModalTambah() {
    document.getElementById('modalTambahProduk')?.classList.add('hidden');
}

function handleTambahProduk(e) {
    e.preventDefault();
    const nama = document.getElementById('inputNama').value;
    const satuan = document.getElementById('inputSatuan').value;
    const harga = Number(document.getElementById('inputHarga').value);
    const stok = Number(document.getElementById('inputStok').value);
    const stokMin = Number(document.getElementById('inputStokMin').value);
    const kategori = document.getElementById('inputKategori').value;

    const status = stok <= 0 ? 'Habis' : (stok < stokMin ? 'Menipis' : 'Aman');

    const newProd = {
        id: products.length + 1,
        nama,
        satuan,
        harga,
        stok,
        stok_minimum: stokMin,
        kategori,
        status
    };

    products.unshift(newProd);
    closeModalTambah();
    filterProducts();
    alert(`Produk '${nama}' berhasil ditambahkan! Stok awal ${stok} otomatis tercatat di riwayat (BR-02).`);
}

let activeEditId = null;

function openModalEdit(id) {
    const prod = products.find(p => p.id === id);
    if (!prod) return;

    activeEditId = id;
    document.getElementById('editNama').value = prod.nama;
    document.getElementById('editSatuan').value = prod.satuan;
    document.getElementById('editHarga').value = prod.harga;
    document.getElementById('editStok').value = prod.stok;
    document.getElementById('editStokMin').value = prod.stok_minimum;
    document.getElementById('editKategori').value = prod.kategori;
    document.getElementById('editAlasan').value = '';

    document.getElementById('modalEditProduk')?.classList.remove('hidden');
}

function closeModalEdit() {
    document.getElementById('modalEditProduk')?.classList.add('hidden');
    activeEditId = null;
}

function handleEditProduk(e) {
    e.preventDefault();
    if (!activeEditId) return;

    const prod = products.find(p => p.id === activeEditId);
    if (!prod) return;

    const oldStok = prod.stok;
    const newStok = Number(document.getElementById('editStok').value);
    const stokMin = Number(document.getElementById('editStokMin').value);

    prod.nama = document.getElementById('editNama').value;
    prod.satuan = document.getElementById('editSatuan').value;
    prod.harga = Number(document.getElementById('editHarga').value);
    prod.stok = newStok;
    prod.stok_minimum = stokMin;
    prod.kategori = document.getElementById('editKategori').value;
    prod.status = newStok <= 0 ? 'Habis' : (newStok < stokMin ? 'Menipis' : 'Aman');

    closeModalEdit();
    filterProducts();

    if (newStok !== oldStok) {
        alert(`Perubahan produk '${prod.nama}' berhasil disimpan!\nPenyesuaian stok dari ${oldStok} menjadi ${newStok} otomatis dicatat di riwayat (BR-02 & FR-22).`);
    } else {
        alert(`Data produk '${prod.nama}' berhasil diperbarui!`);
    }
}

function hapusProduk(id) {
    const prod = products.find(p => p.id === id);
    if (!prod) return;

    if (confirm(`Apakah Anda yakin ingin menghapus produk '${prod.nama}'?\nProduk akan dinonaktifkan dari katalog dan penjualan (BR-11).`)) {
        products = products.filter(p => p.id !== id);
        filterProducts();
        alert(`Produk '${prod.nama}' berhasil dihapus (dinonaktifkan)!`);
    }
}

function hapusProdukDariModal() {
    if (!activeEditId) return;
    const id = activeEditId;
    closeModalEdit();
    hapusProduk(id);
}

document.addEventListener('DOMContentLoaded', () => {
    renderProductsTable(products);
});

