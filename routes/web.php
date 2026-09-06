<?php

use App\Http\Controllers\CategoriaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CarritoItemController;

// Route::view('/', 'welcome');

// Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
// Route::get('/productos/crear', [ProductoController::class, 'create'])->name('productos.create');
// Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
// Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
// Route::get('/productos/{producto}/editar', [ProductoController::class, 'edit'])->name('productos.edit');
// Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');

// Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
// Route::resource('categorias', CategoriaController::class);
// Route::post('/carrito/agregar', [CarritoItemController::class, 'store'])->name('carrito.agregar')->middleware('auth');

Route::view('/', 'welcome');

// --- PRODUCTOS (Orden estricto: estáticas primero, dinámicas después) ---
Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/productos/crear', [ProductoController::class, 'create'])->name('productos.create');
Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
Route::get('/productos/{producto}/editar', [ProductoController::class, 'edit'])->name('productos.edit');
Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');

// --- CATEGORÍAS (Orden estricto: estáticas primero, dinámicas después) ---
Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
Route::get('/categorias/crear', [CategoriaController::class, 'create'])->name('categorias.create');
Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
Route::get('/categorias/{categoria}/editar', [CategoriaController::class, 'edit'])->name('categorias.edit');
Route::put('/categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

// Carrito
Route::post('/carrito/agregar', [CarritoItemController::class, 'store'])->name('carrito.agregar')->middleware('auth');