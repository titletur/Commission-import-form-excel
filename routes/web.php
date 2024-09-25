<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\storeController;
use App\Http\Controllers\pcController;
use App\Http\Controllers\saleController;
use App\Http\Controllers\productController;
use App\Http\Controllers\manageUsersController;
use App\Http\Middleware\CheckImportPermission;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [CommissionController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

Route::middleware(['auth', CheckImportPermission::class])->group(function () {
    //check permission
});
    Route::get('import', [ImportController::class, 'form'])->name('import.form');
    Route::post('import', [ImportController::class, 'import'])->name('import.import');
    Route::post('import/store', [ImportController::class, 'store'])->name('import.store');


    // Main page to display the year selection and data for 12 months
    Route::get('index', [CommissionController::class, 'index'])->name('commissions.index');
    Route::post('/sales', [CommissionController::class, 'sale_in'])->name('sales_in.store');
    // Route for the import page, passing month and year as parameters
    Route::get('import/{year}/{month}/{var_month}', [ImportController::class, 'form'])->name('import');
    
    //show commission
    Route::get('commissions/{year}/{month}/{var_month}', [CommissionController::class, 'show'])->name('commissions.show');
    //export data excel PDF
    Route::get('/commissions/export', [CommissionController::class, 'export'])->name('commissions.export');
    //edit data commission pc
    Route::get('/commissions/edit', [CommissionController::class, 'edit'])->name('commissions.edit');
    //update data commission pc
    Route::post('/update-commission', [CommissionController::class, 'updateCommission'])->name('update_commission');
    //update data commission sale
    Route::post('update_commission_sale', [CommissionController::class, 'updateCommission_sale'])->name('update_commission_sale');
    
    
    
    //update status complated
    Route::post('/status/updateOrCreate', [CommissionController::class, 'updateOrCreate'])->name('status.updateOrCreate');


    // Route to update the target for a specific PC
    Route::get('target/{year}/{month}/{var_month}', [CommissionController::class, 'editTarget'])->name('editTarget');
    Route::post('target/update', [CommissionController::class, 'updateTarget'])->name('updateTarget');


    //store manage
    Route::resource('stores', storeController::class)->except(['show']);
    Route::patch('stores/{id}/status', [StoreController::class, 'updateStatus'])->name('stores.updateStatus');
    Route::get('/stores/export', [StoreController::class, 'export'])->name('stores.export');


    //pc manage
    Route::resource('pc', pcController::class)->except(['show']);
    Route::patch('pc/{id}/status', [pcController::class, 'updateStatus'])->name('pc.updateStatus');
    Route::get('/pcs/export', [pcController::class, 'export'])->name('pcs.export');

    //sale manage
    Route::resource('sale', saleController::class)->except(['show']);
    Route::patch('sale/{id_sale}/status', [saleController::class, 'updateStatus'])->name('sale.updateStatus');
    Route::get('/sales/{id_sale}/stores', [SaleController::class, 'getStoresForSale']);
    Route::get('/sales/export', [SaleController::class, 'export'])->name('sales.export');

    
    //product manage
    Route::resource('product', productController::class)->except(['show']);
    Route::patch('product/{id}/status', [productController::class, 'updateStatus'])->name('product.updateStatus');
    Route::get('/products/export', [productController::class, 'export'])->name('products.export');
    Route::post('/toggle-edit-mode', [productController::class, 'toggleEditMode'])->name('toggleEditMode');

     //Users manage
    Route::resource('users', manageUsersController::class)->except(['show']);
    Route::patch('users/{id}/status', [manageUsersController::class, 'updateStatus'])->name('users.updateStatus');
    //  Route::get('/users/export', [UsersController::class, 'export'])->name('users.export');
});

require __DIR__.'/auth.php';
