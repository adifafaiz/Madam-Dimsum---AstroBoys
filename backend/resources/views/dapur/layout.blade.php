@extends('layouts.panel', [
    'peran' => 'Dapur',
    'ikonPeran' => 'chef-hat',
    'menu' => [
        ['route' => 'dapur.antrian',          'icon' => 'clipboard-list', 'label' => 'Antrian Pesanan'],
        ['route' => 'dapur.catat-produksi',   'icon' => 'circle-plus',    'label' => 'Catat Produksi'],
        ['route' => 'dapur.stok',             'icon' => 'package',        'label' => 'Stok'],
        ['route' => 'dapur.riwayat-produksi', 'icon' => 'history',        'label' => 'Riwayat Produksi'],
    ],
])
