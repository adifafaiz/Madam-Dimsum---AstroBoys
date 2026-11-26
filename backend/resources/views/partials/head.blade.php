{{-- Aset tampilan bersama: font, Tailwind + token warna, ikon Lucide. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: {
                        red: '#C8372D',
                        redHover: '#B22F26',
                        redSoft: '#F7E4E1',
                        dark: '#2A211C',
                        darkCard: '#362B25',
                        lightBg: '#FBF7F2',
                        border: '#EADFD3',
                        secondary: '#8A7B70',
                        ink: '#2A211C',
                    }
                },
                // Skala teks lebih besar: dasar 16px, text-sm 15px, judul halaman (text-3xl) 28px.
                fontSize: {
                    xs: ['13px', '18px'],
                    sm: ['15px', '22px'],
                    base: ['16px', '24px'],
                    lg: ['18px', '26px'],
                    xl: ['20px', '28px'],
                    '2xl': ['24px', '30px'],
                    '3xl': ['28px', '34px'],
                    '4xl': ['34px', '40px'],
                    '5xl': ['44px', '50px'],
                },
                fontFamily: {
                    sans: ['Inter', 'system-ui', 'sans-serif'],
                    display: ['Fredoka', 'Inter', 'sans-serif'],
                },
                // Sudut konsisten 6px; tanpa bayangan tebal.
                borderRadius: {
                    none: '0', sm: '4px', DEFAULT: '6px', md: '6px', lg: '6px',
                    xl: '6px', '2xl': '6px', '3xl': '6px', full: '9999px',
                },
                boxShadow: {
                    sm: '0 1px 0 rgba(42,33,28,0.04)',
                    DEFAULT: '0 1px 2px rgba(42,33,28,0.06)',
                    md: '0 1px 2px rgba(42,33,28,0.06)',
                    lg: '0 2px 6px rgba(42,33,28,0.08)',
                    xl: '0 2px 6px rgba(42,33,28,0.08)',
                    '2xl': '0 2px 6px rgba(42,33,28,0.08)',
                    none: 'none',
                },
            }
        }
    }
</script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<style>
    body { font-family: 'Inter', system-ui, sans-serif; font-size: 16px; line-height: 1.5; color: #2A211C; }
    /* Angka tabular hanya di tempat berisi angka (pada Inter, tnum juga melebarkan tanda hubung di teks biasa) */
    .tabel, .struk, input, select, b, strong, .font-bold, .font-semibold, .tabular-nums { font-variant-numeric: tabular-nums; }
    h1, h2, h3, .font-display { font-family: 'Fredoka', 'Inter', sans-serif; letter-spacing: 0; }
    [data-lucide], svg.lucide { width: 1.125rem; height: 1.125rem; stroke-width: 1.75; flex-shrink: 0; }
    /* Panel keranjang bergaya struk: pinggir putus-putus + lekukan sobekan di garis total. */
    .struk { position: relative; background: #fff; border: 2px dashed #D9C8B8; border-radius: 6px; }
    .struk .sobek { position: relative; border-top: 2px dashed #EADFD3; }
    .struk .sobek::before, .struk .sobek::after {
        content: ''; position: absolute; top: -11px; width: 20px; height: 20px; border-radius: 9999px; background: #FBF7F2;
    }
    .struk .sobek::before { left: -12px; }
    .struk .sobek::after  { right: -12px; }
    .struk .sobek.polos::before, .struk .sobek.polos::after { display: none; }

    /* ---------- Komponen bersama ---------- */
    .label { display: block; font-size: 15px; font-weight: 500; color: #2A211C; margin-bottom: .5rem; }
    .bantuan { font-size: 13px; color: #8A7B70; margin-top: .375rem; }
    .field {
        width: 100%; height: 48px; padding: 0 14px; background: #fff; border: 1px solid #EADFD3; border-radius: 6px;
        font-size: 16px; color: #2A211C; transition: border-color .15s, box-shadow .15s;
    }
    textarea.field { height: auto; min-height: 96px; padding: 12px 14px; line-height: 1.5; }
    select.field { padding-right: 36px; cursor: pointer; appearance: none; -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%238A7B70' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center; }
    .field::placeholder { color: #B9ABA0; }
    .field:focus { outline: none; border-color: #C8372D; box-shadow: 0 0 0 3px rgba(200,55,45,.15); }
    .field[readonly], .field:disabled { background: #FBF7F2; color: #8A7B70; }
    .field-ikon { position: relative; }
    .field-ikon > [data-lucide], .field-ikon > svg.lucide { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #8A7B70; pointer-events: none; }
    .field-ikon > .field { padding-left: 42px; }

    .btn { position: relative; display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 44px; padding: 0 18px;
        border-radius: 6px; font-size: 15px; font-weight: 600; white-space: nowrap; border: 1px solid transparent;
        transition: background-color .15s, border-color .15s, color .15s; cursor: pointer; }
    .btn-lg { min-height: 48px; font-size: 16px; }
    .btn-sm { min-height: 36px; padding: 0 12px; font-size: 14px; }
    .btn:disabled { opacity: .6; cursor: not-allowed; }
    .btn:focus-visible { outline: 3px solid rgba(200,55,45,.3); outline-offset: 2px; }
    .btn-primary { background: #C8372D; color: #fff; }
    .btn-primary:not(:disabled):hover { background: #B22F26; }
    .btn-secondary { background: #fff; color: #2A211C; border-color: #EADFD3; }
    .btn-secondary:not(:disabled):hover { border-color: #D9C8B8; background: #FBF7F2; }
    .btn-dark { background: #2A211C; color: #fff; }
    .btn-dark:not(:disabled):hover { background: #000; }
    .btn-danger { background: #FCF1EF; color: #B0261D; }
    .btn-danger:not(:disabled):hover { background: #F7E0DC; }
    .btn-ghost { background: transparent; color: #2A211C; }
    .btn-ghost:not(:disabled):hover { background: #F3ECE4; }

    .kartu { background: #fff; border: 1px solid #EADFD3; border-radius: 6px; }
    .kartu-judul { font-family: 'Fredoka', 'Inter', sans-serif; font-size: 20px; font-weight: 600; color: #2A211C; line-height: 1.3; }

    .tabel { width: 100%; text-align: left; font-size: 15px; }
    .tabel thead { background: #FBF7F2; border-bottom: 1px solid #EADFD3; }
    .tabel th { padding: 12px 20px; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #8A7B70; white-space: nowrap; }
    .tabel td { padding: 14px 20px; vertical-align: middle; }
    .tabel tbody tr + tr { border-top: 1px solid #EADFD3; }
    .tabel tbody tr:hover { background: rgba(251,247,242,.7); }

    .modal { position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(42,33,28,.55); }
    .modal.hidden { display: none; }
    .modal-panel { width: 100%; max-height: calc(100vh - 32px); overflow-y: auto; background: #fff; border: 1px solid #EADFD3; border-radius: 6px; }
    .modal-kepala { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 20px 24px; border-bottom: 1px solid #EADFD3; }
    .modal-isi { padding: 20px 24px; }
    .modal-kaki { display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid #EADFD3; background: #FBF7F2; }

    .kosong { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 48px 24px; }
    .kosong > [data-lucide], .kosong > svg.lucide { width: 40px; height: 40px; color: #C9BBAE; }
    .kosong-judul { margin-top: 12px; font-size: 18px; font-weight: 600; color: #2A211C; }
    .kosong-sub { color: #8A7B70; max-width: 36ch; }

</style>
<script>
    // Render ikon Lucide; panggil ulang window.renderIcons() setelah menambah HTML lewat JS.
    window.renderIcons = () => window.lucide && window.lucide.createIcons();
    document.addEventListener('DOMContentLoaded', window.renderIcons);
</script>
