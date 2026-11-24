/**
 * Madam Dimsum - Login Logic & API Integration
 */

// Konfigurasi API Backend Laravel
const API_BASE_URL = 'http://127.0.0.1:8000/api';

// Data deskripsi & manfaat untuk tiap peran (Selaras PRD Final)
const roleFeatures = {
    dapur: {
        title: 'Dapur',
        feat1: 'Lihat antrian pesanan masuk',
        feat2: 'Catat produksi & penggunaan bahan',
        feat3: 'Lihat produk yang perlu diproduksi',
        defaultUsername: 'agus'
    },
    kasir: {
        title: 'Kasir',
        feat1: 'Input transaksi kasir & kembalian',
        feat2: 'Cetak dan cetak ulang struk pesanan',
        feat3: 'Pantau status ketersediaan stok',
        defaultUsername: 'kasir'
    },
    owner: {
        title: 'Owner',
        feat1: 'Pantau penjualan hari ini & grafik 7 hari',
        feat2: 'Kelola produk, stok, dan pemasok',
        feat3: 'Laporan penjualan & manajemen pengguna',
        defaultUsername: 'owner'
    }
};

/**
 * Fungsi memilih peran (Owner, Kasir, Dapur)
 */
function selectRole(role) {
    const roleInput = document.getElementById('roleInput');
    if (roleInput) roleInput.value = role;

    // Perbarui status aktif tombol
    const buttons = ['owner', 'kasir', 'dapur'];
    buttons.forEach(r => {
        const btn = document.getElementById('role-' + r);
        if (!btn) return;

        if (r === role) {
            btn.className = 'role-btn py-2.5 px-3 rounded-lg text-sm font-semibold transition-all duration-200 border bg-brand-red border-brand-red text-white shadow-sm';
        } else {
            btn.className = 'role-btn py-2.5 px-3 rounded-lg text-sm font-semibold transition-all duration-200 border bg-white border-stone-200 text-stone-700 hover:border-stone-300';
        }
    });

    // Update label tombol submit
    const roleInfo = roleFeatures[role];
    const submitBtnText = document.getElementById('submitBtnText');
    if (submitBtnText) {
        submitBtnText.textContent = 'Masuk sebagai ' + roleInfo.title;
    }

    // Update poin keunggulan di panel sisi kiri
    const feat1 = document.getElementById('feat1');
    const feat2 = document.getElementById('feat2');
    const feat3 = document.getElementById('feat3');

    if (feat1) feat1.textContent = roleInfo.feat1;
    if (feat2) feat2.textContent = roleInfo.feat2;
    if (feat3) feat3.textContent = roleInfo.feat3;
}

/**
 * Toggle tampilkan/sembunyikan password
 */
function togglePasswordVisibility() {
    const passInput = document.getElementById('password');
    const eyeOpen = document.getElementById('eyeIconOpen');
    const eyeClosed = document.getElementById('eyeIconClosed');

    if (passInput.type === 'password') {
        passInput.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        passInput.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    }
}

/**
 * Isi otomatis untuk akun uji coba (Demo Quick Fill)
 */
function quickFill(role, username, password) {
    selectRole(role);
    document.getElementById('username').value = username;
    document.getElementById('password').value = password;
}

/**
 * Menampilkan pesan alert (success / error / info)
 */
function showAlert(type, message) {
    const alertBox = document.getElementById('alertBox');
    alertBox.className = 'fade-in mb-5 p-3.5 rounded-xl text-sm border';

    if (type === 'success') {
        alertBox.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-800');
        alertBox.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>${message}</span>
            </div>
        `;
    } else if (type === 'error') {
        alertBox.classList.add('bg-red-50', 'border-red-200', 'text-brand-red');
        alertBox.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-red shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>${message}</span>
            </div>
        `;
    } else {
        alertBox.classList.add('bg-stone-100', 'border-stone-300', 'text-stone-700');
        alertBox.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-stone-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span>${message}</span>
            </div>
        `;
    }

    alertBox.classList.remove('hidden');
}

/**
 * Handle form submission ke backend API
 */
async function handleFormSubmit(event) {
    event.preventDefault();

    const role = document.getElementById('roleInput').value;
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const remember = document.getElementById('remember').checked;

    const submitBtn = document.getElementById('submitBtn');
    const spinner = document.getElementById('submitSpinner');
    const submitBtnText = document.getElementById('submitBtnText');

    // Validasi sederhana di sisi client
    if (!username || !password) {
        showAlert('error', 'Username dan password wajib diisi.');
        return;
    }

    // Set status tombol loading
    submitBtn.disabled = true;
    spinner.classList.remove('hidden');
    submitBtnText.textContent = 'Memproses masuk...';

    try {
        const response = await fetch(`${API_BASE_URL}/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                role: role,
                username: username,
                password: password,
                remember: remember
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            showAlert('success', `Login berhasil! Selamat datang, ${data.user.name} (${data.user.role.toUpperCase()})`);
            
            // Redirect ke dashboard backend jika backend berjalan
            setTimeout(() => {
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                }
            }, 1200);
        } else {
            showAlert('error', data.message || 'Login gagal. Periksa kembali username dan password Anda.');
        }
    } catch (err) {
        // Fallback jika backend server belum dinyalakan oleh user
        console.warn('Gagal terhubung ke API backend:', err);
        showAlert('info', `Server Backend (${API_BASE_URL}) belum aktif. Jalankan 'php artisan serve' di folder backend. (Kredensial Anda: [${role.toUpperCase()}] ${username})`);
    } finally {
        submitBtn.disabled = false;
        spinner.classList.add('hidden');
        submitBtnText.textContent = 'Masuk sebagai ' + roleFeatures[role].title;
    }
}

// Inisialisasi saat halaman dimuat
window.addEventListener('DOMContentLoaded', () => {
    selectRole('dapur');
});

