@extends('layouts.panel', [
    'peran' => 'Owner',
    'ikonPeran' => 'store',
    'menu' => [
        ['route' => 'owner.dashboard',         'icon' => 'layout-dashboard', 'label' => 'Dashboard', 'aktif' => ['owner.dashboard']],
        ['route' => 'owner.produk',            'icon' => 'package',          'label' => 'Produk & Stok', 'aktif' => ['owner.produk*', 'owner.stok-masuk*']],
        ['route' => 'owner.pemasok',           'icon' => 'truck',            'label' => 'Pemasok'],
        ['route' => 'owner.laporan',           'icon' => 'chart-column',     'label' => 'Laporan'],
        ['route' => 'owner.riwayat-transaksi', 'icon' => 'receipt-text',     'label' => 'Riwayat Transaksi'],
        ['route' => 'owner.pengguna',          'icon' => 'users',            'label' => 'Pengguna'],
        ['route' => 'owner.pencadangan',       'icon' => 'database-backup',  'label' => 'Pencadangan'],
    ],
])
