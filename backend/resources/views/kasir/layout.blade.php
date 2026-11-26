@extends('layouts.panel', [
    'peran' => 'Kasir',
    'ikonPeran' => 'receipt-text',
    'menu' => [
        ['route' => 'kasir.transaksi', 'icon' => 'shopping-basket', 'label' => 'Transaksi'],
        ['route' => 'kasir.cek-stok',  'icon' => 'package-search',  'label' => 'Cek Stok'],
        ['route' => 'kasir.riwayat',   'icon' => 'history',         'label' => 'Riwayat Saya'],
    ],
])
