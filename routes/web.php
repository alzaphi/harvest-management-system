<?php

use Illuminate\Support\Facades\Route;

// Route untuk mengecek konfigurasi PHP
Route::get('/phpinfo', function() {
    phpinfo();
    exit;
})->name('phpinfo');
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\LKTController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubBlockController;
use App\Http\Controllers\StatusSubBlockController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SPTController;
use App\Http\Controllers\ActivityTrackingController;
use App\Http\Controllers\SptConfirmationController;
use App\Models\SubBlock;
use App\Models\User;
use App\Http\Controllers\Auth\OtpResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\HasilTebangController;
use App\Http\Controllers\BAPPController;
use App\Http\Controllers\BappTebangController;
use App\Http\Controllers\BappAngkutController;
use App\Http\Controllers\PaymentController;



// Test route at the very top
Route::get('/test-route', function() {
    return response()->json(['message' => 'Top test route is working']);
});

// Simple test route at the very top
Route::get('/test-route-top', function() {
    return response()->json(['message' => 'Top test route is working']);
});

// Test route to verify route loading
Route::get('/test-route-registration', function() {
    return response()->json(['message' => 'Route registration is working']);
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link');
    return 'Storage linked sucessfully';
});

// Authentication Routes
Route::get('/reset-otp', [OtpResetPasswordController::class, 'showRequestForm'])->name('otp.reset.request');
Route::post('/reset-otp', [OtpResetPasswordController::class, 'sendOtp'])->name('otp.reset.send');

Route::get('/reset-otp/verify', [OtpResetPasswordController::class, 'showVerifyForm'])->name('otp.reset.verify.form');
Route::post('/reset-otp/verify', [OtpResetPasswordController::class, 'verifyOtpAndReset'])->name('otp.reset.verify');

Route::get('/debug-am', function () {
    $user = User::where('role_name', 'Assistant Manager Plantation')->first(); // Ganti kalau kamu pakai kolom lain
    if (!$user) return 'User tidak ditemukan';

    return [
        'Roles' => $user->getRoleNames(),
        'Permissions' => $user->getAllPermissions()->pluck('name'),
        'Can view-lkt?' => $user->can('view-lkt') ? 'YES' : 'NO',
    ];
});

// Rute untuk autentikasi
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Redirect root URL ke dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Temporary route for debugging
    Route::get('/check-complain-table', function() {
        try {
            $tableExists = \Schema::hasTable('complainbapptebang');

            if ($tableExists) {
                $columns = \Schema::getColumnListing('complainbapptebang');
                return [
                    'table_exists' => true,
                    'columns' => $columns
                ];
            } else {
                return ['table_exists' => false];
            }
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    });
});

    // Permission Management Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])
            ->name('permissions.index');
        Route::post('/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'store'])
            ->name('permissions.store');
    });

// Authentication Routes
Route::middleware('auth')->group(function () {
    // Profile Routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile');
    Route::put('/password/update', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
});

// Vehicle routes
Route::prefix('vehicles')->group(function () {
    Route::get('/export', [VehicleController::class, 'exportExcel'])->name('vehicles.export');
    Route::get('/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
});

// Vendor management routes
Route::get('/vendor-management/vehicles', [VehicleController::class, 'vendorVehicleList'])->name('vendor.vehicle.list');

// Vendor routes
Route::get('vendor/export', [VendorController::class, 'exportExcel'])->name('vendor.export');
Route::get('vendor/{vendor}/details', [SPTController::class, 'getVendorDetails'])->name('vendor.details');
Route::resource('vendor', VendorController::class)->except(['show'])->names([
    'index' => 'vendor.index',
    'create' => 'vendor.create',
    'store' => 'vendor.store',
    'edit' => 'vendor.edit',
    'update' => 'vendor.update',
    'destroy' => 'vendor.destroy',
]);


// User Account Registration Routes
Route::get('users/get-vendor/{id}', [UserController::class, 'getVendorData'])->name('users.get-vendor');
Route::resource('users', UserController::class);

// Barcode routes
Route::get('barcode/{id?}', [\App\Http\Controllers\BarcodeController::class, 'show'])->name('barcode.show');
Route::get('barcode/{id}/download', [\App\Http\Controllers\BarcodeController::class, 'download'])->name('barcode.download');


// Forgot Password Routes

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');


// User Password Update Routes
Route::get('users/{user}/edit-password', [UserController::class, 'editPassword'])->name('users.edit-password');
Route::put('users/{user}/update-password', [UserController::class, 'updatePassword'])->name('users.update-password');

// Sub Block routes
Route::middleware(['permission:view-sub-block-information'])->group(function () {
    Route::get('sub-blocks', [SubBlockController::class, 'index'])->name('sub-blocks.index');

    // Only allow create/edit/delete for users with create permission
    Route::middleware(['permission:create-sub-block-information'])->group(function () {
        Route::get('sub-blocks/create', [SubBlockController::class, 'create'])->name('sub-blocks.create');
        Route::post('sub-blocks', [SubBlockController::class, 'store'])->name('sub-blocks.store');
        Route::get('sub-blocks/{subBlock}/edit', [SubBlockController::class, 'edit'])->name('sub-blocks.edit');
        Route::put('sub-blocks/{subBlock}', [SubBlockController::class, 'update'])->name('sub-blocks.update');
        Route::delete('sub-blocks/{subBlock}', [SubBlockController::class, 'destroy'])->name('sub-blocks.destroy');
    });

    Route::get('sub-blocks/get-blocks-by-division', [SubBlockController::class, 'getBlocksByDivision'])->name('sub-blocks.get-blocks-by-division');

    // Route untuk download layak tebang GeoJSON dengan permission
    Route::middleware(['permission:download-layak-tebang'])->group(function () {
        Route::get('sub-blocks/export-layak-tebang', [SubBlockController::class, 'downloadTebangGeojson'])->name('sub-blocks.download.layak-tebang');
    });
});

// Status Sub Block routes
Route::middleware(['permission:view-status-sub-block'])->group(function () {
    Route::get('status-sub-blocks', [StatusSubBlockController::class, 'index'])->name('status-sub-blocks.index');
    Route::get('status-sub-blocks/get-kode-petak-modal', [StatusSubBlockController::class, 'getKodePetakModal'])->name('status-sub-blocks.get-kode-petak-modal');

    // Only allow update for users with edit permission
    Route::middleware(['permission:edit-sub-block-information'])->group(function () {
        Route::post('status-sub-blocks/update-status', [StatusSubBlockController::class, 'updateStatus'])->name('status-sub-blocks.update-status');
    });
});

// Foreman Sub Block routes (full access for Assistant Divisi Plantation)
Route::middleware(['permission:view-foreman-sub-block'])->group(function () {
    Route::get('foreman-sub-blocks', [\App\Http\Controllers\ForemanSubBlockController::class, 'index'])->name('foreman-sub-blocks.index');

    // Only allow create/edit/delete for users with the right permissions
    Route::middleware(['permission:create-foreman-sub-block'])->group(function () {
        Route::get('foreman-sub-blocks/create', [\App\Http\Controllers\ForemanSubBlockController::class, 'create'])->name('foreman-sub-blocks.create');
        Route::post('foreman-sub-blocks', [\App\Http\Controllers\ForemanSubBlockController::class, 'store'])->name('foreman-sub-blocks.store');
    });

    Route::middleware(['permission:edit-foreman-sub-block'])->group(function () {
        Route::get('foreman-sub-blocks/{foremanSubBlock}/edit', [\App\Http\Controllers\ForemanSubBlockController::class, 'edit'])->name('foreman-sub-blocks.edit');
        Route::put('foreman-sub-blocks/{foremanSubBlock}', [\App\Http\Controllers\ForemanSubBlockController::class, 'update'])->name('foreman-sub-blocks.update');
    });

    Route::middleware(['permission:delete-foreman-sub-block'])->group(function () {
        Route::delete('foreman-sub-blocks/{foremanSubBlock}', [\App\Http\Controllers\ForemanSubBlockController::class, 'destroy'])->name('foreman-sub-blocks.destroy');
    });
});

// Harvest Sub Block routes
Route::middleware(['permission:view-harvest-sub-block'])->group(function () {
    Route::get('harvest-sub-blocks', [\App\Http\Controllers\HarvestSubBlockController::class, 'index'])->name('harvest-sub-blocks.index');

    // Only allow create/edit/delete for users with the right permissions
    Route::middleware(['permission:create-harvest-sub-block'])->group(function () {
        Route::get('harvest-sub-blocks/create', [\App\Http\Controllers\HarvestSubBlockController::class, 'create'])->name('harvest-sub-blocks.create');
        Route::post('harvest-sub-blocks', [\App\Http\Controllers\HarvestSubBlockController::class, 'store'])->name('harvest-sub-blocks.store');
    });

    Route::middleware(['permission:edit-harvest-sub-block'])->group(function () {
        Route::get('harvest-sub-blocks/{harvestSubBlock}/edit', [\App\Http\Controllers\HarvestSubBlockController::class, 'edit'])->name('harvest-sub-blocks.edit');
        Route::put('harvest-sub-blocks/{harvestSubBlock}', [\App\Http\Controllers\HarvestSubBlockController::class, 'update'])->name('harvest-sub-blocks.update');
    });

    Route::middleware(['permission:delete-harvest-sub-block'])->group(function () {
        Route::delete('harvest-sub-blocks/{harvestSubBlock}', [\App\Http\Controllers\HarvestSubBlockController::class, 'destroy'])->name('harvest-sub-blocks.destroy');
    });
});
Route::post('status-sub-blocks/import', [StatusSubBlockController::class, 'import'])->name('status-sub-blocks.import');
Route::get('status-sub-blocks/template', [StatusSubBlockController::class, 'downloadTemplate'])->name('status-sub-blocks.template');

// Payment calculation route
Route::get('/payment-calculation', [PaymentCalculationController::class, 'index'])->name('payment.calculation');

// Payment routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('payment', PaymentController::class)->only(['index', 'show']);
});

// Foreman management routes
Route::get('foreman/export', [\App\Http\Controllers\ForemanController::class, 'exportExcel'])->name('foreman.export');
Route::resource('foreman', \App\Http\Controllers\ForemanController::class)->names([
    'index' => 'foreman.index',
    'create' => 'foreman.create',
    'store' => 'foreman.store',
    'show' => 'foreman.show',
    'edit' => 'foreman.edit',
    'update' => 'foreman.update',
    'destroy' => 'foreman.destroy',
]);

// Harvest Sub Block routes
Route::get('harvest-sub-blocks/export', [\App\Http\Controllers\HarvestSubBlockController::class, 'export'])->name('harvest-sub-blocks.export');
Route::resource('harvest-sub-blocks', \App\Http\Controllers\HarvestSubBlockController::class)->names([
    'index' => 'harvest-sub-blocks.index',
    'create' => 'harvest-sub-blocks.create',
    'store' => 'harvest-sub-blocks.store',
    'show' => 'harvest-sub-blocks.show',
    'edit' => 'harvest-sub-blocks.edit',
    'update' => 'harvest-sub-blocks.update',
    'destroy' => 'harvest-sub-blocks.destroy',
]);
Route::post('/harvest-sub-blocks/import', [\App\Http\Controllers\HarvestSubBlockController::class, 'importFromGeojson'])->name('harvest.import');

// SPT (Surat Perintah Tebang) routes
Route::get('spt/export', [SPTController::class, 'export'])->name('spt.export');
Route::get('spt/{spt}/print', [SPTController::class, 'print'])->name('spt.print');
Route::get('spt/{spt}/download-pdf', [SPTController::class, 'downloadPdf'])->name('spt.download-pdf');
Route::get('spt/get-available-blocks/{date}', [SPTController::class, 'getAvailableBlocks'])->name('spt.get-available-blocks');
Route::get('spt/mandors/{date}/{block}', [SPTController::class, 'getMandors'])->name('spt.mandors');
Route::get('spt/sub-block-info/{kodePetak}', [SPTController::class, 'getSubBlockInfo'])->name('spt.sub-block-info');
Route::get('/spt/availability/{date}', [SPTController::class, 'getAvailabilityByDate'])
    ->name('spt.availability.byDate')
    ->middleware('web'); // Explicitly add web middleware

Route::resource('spt', SPTController::class)->names([
    'index' => 'spt.index',
    'create' => 'spt.create',
    'store' => 'spt.store',
    'show' => 'spt.show',
    'edit' => 'spt.edit',
    'update' => 'spt.update',
    'destroy' => 'spt.destroy',
]);

// SPT Approval Routes - Direct approval from SPT show page
Route::post('/spt/{spt}/approve', [SPTController::class, 'approve'])
    ->name('spt.approve')
    ->middleware('auth');

// SPT Approval Routes - Using ApprovalSPTController for all approval related actions
Route::prefix('approval')->name('approval.')->middleware(['auth'])->group(function () {
    // List all SPTs pending approval
    Route::get('/spt', [App\Http\Controllers\ApprovalSPTController::class, 'index'])
        ->name('spt.index')
        ->middleware('can:approve-spt');

    // Show SPT details for approval
    Route::get('/spt/{spt}', [App\Http\Controllers\ApprovalSPTController::class, 'show'])
        ->name('spt.show')
        ->middleware('can:approve-spt');

    // Process approval/rejection (accept both POST and PUT methods)
    Route::match(['post', 'put'], '/spt/{spt}/approve', [App\Http\Controllers\ApprovalSPTController::class, 'approve'])
        ->name('spt.approve')
        ->middleware('can:approve-spt');
});

Route::post('/spt/{spt}/confirm-mandor', [SPTController::class, 'confirmMandor'])->name('spt.confirm.mandor');

// GIS INFORMATION
Route::middleware(['web'])->group(function () {
    Route::prefix('gis')->name('gis.')->group(function () {
        Route::get('/', [App\Http\Controllers\GisController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\GisController::class, 'create'])->name('create'); // tampilkan form upload
        Route::post('/', [App\Http\Controllers\GisController::class, 'store'])->name('store');       // simpan file upload
        Route::get('/{id}/edit', [App\Http\Controllers\GisController::class, 'edit'])->name('edit');   // tampilkan form edit
        Route::put('/{id}', [App\Http\Controllers\GisController::class, 'update'])->name('update');   // update data
        Route::delete('/{id}', [App\Http\Controllers\GisController::class, 'destroy'])->name('destroy');

        // Chunked upload routes
        Route::post('/upload-chunk', [App\Http\Controllers\GisController::class, 'uploadChunk'])->name('upload.chunk');
        Route::post('/merge-chunks', [App\Http\Controllers\GisController::class, 'mergeChunks'])->name('merge.chunks');
    });
});

// SUB-BLOCKS
Route::get('/sub-blocks', [SubBlockController::class, 'index'])->name('sub-blocks.index');
Route::get('/sub-blocks/create', [SubBlockController::class, 'create'])->name('sub-blocks.create');
Route::get('/sub-blocks/{id}/edit', [SubBlockController::class, 'edit'])->name('sub-blocks.edit');
Route::get('/get-blocks-by-division', [SubBlockController::class, 'getBlocksByDivision']);
Route::post('/sub-blocks', [SubBlockController::class, 'store'])->name('sub-blocks.store');
Route::put('/sub-blocks/{id}', [SubBlockController::class, 'update'])->name('sub-blocks.update');
Route::delete('/sub-blocks/{id}', [SubBlockController::class, 'destroy'])->name('sub-blocks.destroy');
Route::get('/sub-blocks/export-layak-tebang', [SubBlockController::class, 'downloadTebangGeojson'])->name('sub-blocks.download.layak-tebang');
Route::get('/sub-blocks/export-geojson', [SubBlockController::class, 'exportGeoJson'])->name('sub-blocks.export-geojson');
Route::post('/sub-blocks/import-geojson', [SubBlockController::class, 'importGeoJson'])->name('sub-blocks.import-geojson');

// FOREMAN SUB-BLOCKS
Route::resource('foreman-sub-blocks', 'App\Http\Controllers\ForemanSubBlockController');

// STATUS SUB-BLOCKS
Route::resource('status-sub-blocks', '\App\Http\Controllers\StatusSubBlockController');
Route::get('/get-kode-petak-modal', [StatusSubBlockController::class, 'getKodePetakModal'])->name('get-kode-petak-modal');

// BAPP Routes
Route::prefix('bapp')->name('bapp.')->group(function () {
    // BAPP Tebang specific routes
    Route::get('generate-selection/{kode_vendor}', [BappTebangController::class, 'generateSelection'])
        ->name('generate-selection');
    Route::post('confirm', [BappTebangController::class, 'confirmBapp'])
        ->name('confirm');
    Route::get('confirm', [BappTebangController::class, 'showConfirm'])
        ->name('confirm.form');
    Route::post('/', [BappTebangController::class, 'store'])->name('store');

    // Signature route
    Route::post('{kode_bapp}/save-signature', [BAPPController::class, 'saveSignature'])
        ->name('approval.save-signature');

    // Main BAPP routes (using BAPPController)
    Route::get('/', [BAPPController::class, 'index'])->name('index');
    Route::get('/create', [BAPPController::class, 'create'])->name('create');
    Route::get('/{bapp}/print', [BAPPController::class, 'print'])->name('print');

    // BAPP Recap
    Route::get('recap', [BAPPController::class, 'recap'])->name('recap.index');
    Route::get('recap/{period}', [BAPPController::class, 'recapDetail'])->name('recap.detail');
    Route::get('recap/{period}/spd', [BAPPController::class, 'showSPD'])->name('recap.spd');
    Route::post('recap/submit', [BAPPController::class, 'submitRecap'])->name('recap.submit');

    // Routes that need the 'jenis' parameter
    Route::prefix('{jenis}')->group(function () {
        Route::get('/{bapp}', [BAPPController::class, 'show'])->name('show');
        Route::get('/{bapp}/edit', [BAPPController::class, 'edit'])->name('edit');
        Route::put('/{bapp}', [BAPPController::class, 'update'])->name('update');
        Route::delete('/{bapp}', [BAPPController::class, 'destroy'])->name('destroy');

        // Approval route
        Route::put('/{bapp}/approve', [BappTebangController::class, 'approve'])
            ->name('approve')
            ->where('bapp', '[0-9]+')  // Ensure bapp is a number
            ->middleware('permission:approve-bapp');

        // Komplain routes
        Route::get('/{bapp}/komplain/edit', [BAPPController::class, 'editKomplain'])->name('komplain.edit');
        Route::put('/{bapp}/komplain', [BAPPController::class, 'updateKomplain'])->name('komplain.update');
    });
});

Route::post('/bapp/spd/store', [App\Http\Controllers\BAPPController::class, 'storeSPD'])->name('spd.store');

// Rekap BAPP Routes
Route::prefix('rekap-bapp')->name('rekap-bapp.')->group(function () {
    Route::get('/', [BAPPController::class, 'index'])->name('index');
    Route::get('/{id}', [BAPPController::class, 'show'])->name('show');
    Route::post('/{id}/signature', [BAPPController::class, 'saveSignature'])->name('save-signature');
    Route::get('/{period}/detail', [BAPPController::class, 'showByPeriod'])->name('detail');
    Route::get('/approval/{id}', [BAPPController::class, 'approvalDetail'])->name('approval.show');
});

// SPD Routes
Route::prefix('spd')->name('spd.')->middleware(['auth'])->group(function () {
    Route::get('/', [BAPPController::class, 'listSPD'])->name('index');
    Route::get('/{id}', [BAPPController::class, 'viewSPD'])->name('view');

    // Alias untuk kompatibilitas dengan kode yang menggunakan spd.show
    Route::get('/{id}/show', [BAPPController::class, 'viewSPD'])->name('show');
    Route::get('/', [BAPPController::class, 'listSPD'])->name('index');
    Route::get('/approval', [BAPPController::class, 'approvalIndex'])->name('approval.index');
    Route::get('/approval/{id}', [BAPPController::class, 'showApproval'])->name('approval.show');
    Route::get('/approval/{id}/detail', [BAPPController::class, 'approvalDetail'])->name('approval.detail');

    // Tambahkan route untuk proses approval
    Route::post('/approval/{id}/process', [BAPPController::class, 'processApproval'])->name('approval.process');

    Route::post('/approval/{id}/approve', [BAPPController::class, 'approveSPD'])->name('approval.approve');
    Route::post('/approval/{id}/reject', [BAPPController::class, 'rejectSPD'])->name('approval.reject');

    Route::post('/{id}/sign', [BAPPController::class, 'signSPD'])->name('sign');
    Route::post('/{id}/complete-payment', [BAPPController::class, 'completePaymentSPD'])->name('complete-payment');

    // Route untuk submit review
    Route::post('/{id}/submit-review', [BAPPController::class, 'submitForReview'])->name('submit-review');
});

// LKT routes
Route::prefix('lkt')->name('lkt.')->group(function () {
    Route::get('/', [LKTController::class, 'index'])->name('index');
    Route::get('export', [LKTController::class, 'exportExcel'])->name('export');
    Route::post('{id}/sign', [LKTController::class, 'sign'])->name('sign');
    Route::get('get-spt-data/{kode}', [LKTController::class, 'getSPTData'])->name('get-spt-data');
    Route::patch('{id}/update-status', [LKTController::class, 'updateStatus'])->name('update-status');
    Route::get('{lkt}/download-pdf', [LKTController::class, 'downloadPdf'])->name('download-pdf');
    Route::get('check-timbangan/{id}', [LKTController::class, 'checkTimbangan'])->name('check-timbangan');

    // Approval routes
    Route::prefix('approval')
        ->name('approval.')
        ->middleware(['auth', 'permission:approve-lkt'])
        ->group(function () {
            Route::get('/', [LKTController::class, 'approvalIndex'])->name('index');
            Route::get('/{id}', [LKTController::class, 'approvalShow'])->name('show');
            Route::post('/{id}/approve', [LKTController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [LKTController::class, 'reject'])->name('reject');
            Route::post('/{id}/timbangan', [LKTController::class, 'timbangan'])->name('timbangan');
        });

    Route::resource('/', LKTController::class)->parameters(['' => 'lkt'])->names([
        'index' => 'index',
        'create' => 'create',
        'store' => 'store',
        'show' => 'show',
        'edit' => 'edit',
        'update' => 'update',
        'destroy' => 'destroy',
    ]);
});

// Activity Tracking Routes
// Konfirmasi SPT
Route::middleware(['auth'])->group(function () {
    Route::post('/spt/{spt}/confirm', [SptConfirmationController::class, 'store'])->name('spt.confirm');
    Route::delete('/spt/{spt}/unconfirm', [SptConfirmationController::class, 'destroy'])->name('spt.unconfirm');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/activity-tracking', [ActivityTrackingController::class, 'index'])->name('activity.tracking.index');
    Route::post('/activity-tracking/{id}/status', [ActivityTrackingController::class, 'updateStatus'])->name('activity.tracking.update-status');
    Route::get('/activity-tracking/{id}/detail', [ActivityTrackingController::class, 'getDetail'])->name('activity.tracking.detail');
});

// Hasil Tebang routes
Route::prefix('hasil-tebang')->name('hasil-tebang.')->middleware(['auth'])->group(function () {
    Route::get('/', [HasilTebangController::class, 'index'])->name('index');
    Route::get('/create', [HasilTebangController::class, 'create'])->name('create');
    Route::post('/', [HasilTebangController::class, 'store'])->name('store');

    // Vendor-specific routes
    Route::get('/{kode_vendor}', [HasilTebangController::class, 'show'])->name('show');
    Route::get('/{kode_vendor}/generate', [HasilTebangController::class, 'generateSelection'])->name('generate');

    // Edit selection route
    Route::get('/edit/selection', [HasilTebangController::class, 'editSelection'])->name('edit.selection');
    Route::get('/edit/form/{id}', [HasilTebangController::class, 'editForm'])->name('edit.form');
    Route::post('/update-selection', [HasilTebangController::class, 'updateSelection'])->name('update.selection');

    // Delete routes
    Route::get('/delete/selection', [HasilTebangController::class, 'deleteSelection'])->name('delete.selection');
    Route::get('/delete/confirm/{id}', [HasilTebangController::class, 'deleteConfirm'])->name('delete.confirm');
    Route::delete('/delete/{id}', [HasilTebangController::class, 'destroy'])->name('destroy');

    // BAPP confirmation
    Route::post('/confirm', [HasilTebangController::class, 'confirmBapp'])->name('confirm');

    // Single record operations
    Route::get('/record/{id}/edit', [HasilTebangController::class, 'edit'])->name('edit');
    Route::put('/record/{id}', [HasilTebangController::class, 'update'])->name('update');
    Route::delete('/record/{id}', [HasilTebangController::class, 'destroy'])->name('destroy');
});

Route::get('hasil-tebang/{id}/edit-form', [HasilTebangController::class, 'editForm'])->name('hasil-tebang.edit-form');

// Payment routes
Route::prefix('pembayaran')->name('pembayaran.')->group(function () {
    Route::get('/', function () {
        // For now, we'll just return the view directly
        // In a real application, you would fetch payments from the database here
        $pembayarans = []; // Empty array for now
        return view('pembayaran.index', compact('pembayarans'));
    })->name('index');
});

// BAPP Angkut Routes
Route::prefix('bapp-angkut')->name('bapp.angkut.')->middleware(['auth'])->group(function () {
    // Halaman utama BAPP Angkut
    Route::get('/', [BappAngkutController::class, 'index'])->name('index');

    // Edit BAPP Angkut
    Route::get('/{bapp}/edit', [BappAngkutController::class, 'edit'])->name('edit');
    Route::put('/{bapp}', [BappAngkutController::class, 'update'])->name('update');

    // Generate BAPP Angkut
    Route::get('/generate/{vendorKode}', [BappAngkutController::class, 'showGenerateAngkut'])->name('generate');

    // Konfirmasi BAPP Angkut
    Route::get('/confirm', [BappAngkutController::class, 'showConfirmAngkut'])->name('confirm');
    Route::post('/confirm', [BappAngkutController::class, 'confirmAngkut'])->name('confirm.submit');

    // Simpan BAPP Angkut
    Route::post('/store', [BappAngkutController::class, 'storeAngkut'])->name('store');

    // Alias for compatibility with existing code
    Route::post('/', [BappAngkutController::class, 'storeAngkut'])->name('store.angkut');

    // Tampilkan detail BAPP Angkut
    Route::get('/{id}', [BappAngkutController::class, 'show'])->name('show');
});

// Vendor Angkut Routes
Route::prefix('vendor-angkut')->name('vendor-angkut.')->middleware(['auth'])->group(function () {
    Route::get('/', [\App\Http\Controllers\VendorAngkutController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\VendorAngkutController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\VendorAngkutController::class, 'store'])->name('store');
    Route::get('/{vendor}', [\App\Http\Controllers\VendorAngkutController::class, 'show'])->name('show');
    Route::get('/{vendor}/edit', [\App\Http\Controllers\VendorAngkutController::class, 'edit'])->name('edit');
    Route::put('/{vendor}', [\App\Http\Controllers\VendorAngkutController::class, 'update'])->name('update');
    Route::delete('/{vendor}', [\App\Http\Controllers\VendorAngkutController::class, 'destroy'])->name('destroy');
});

// Routes untuk approval BAPP
Route::prefix('bapp/approval')
    ->name('bapp.approval.')
    ->middleware(['auth', 'role:admin|manager-plantation|vendor|assistant-manager-plantation'])
    ->group(function () {
        Route::get('/', [BAPPController::class, 'approvalIndex'])->name('index');
        Route::get('/{kode_bapp}', [BAPPController::class, 'approvalShow'])->name('show');
        Route::post('/{kode_bapp}/submit-vendor', [BAPPController::class, 'submitVendor'])->name('submit-vendor');
        Route::post('/{kode_bapp}/vendor-approve', [BAPPController::class, 'vendorApprove'])->name('vendor.approve');
        Route::post('/{kode_bapp}/final-approve', [BAPPController::class, 'finalApprove'])->name('final.approve');
        Route::post('/{kode_bapp}/reject', [BAPPController::class, 'rejectBAPP'])->name('reject');
    });

// Payment History Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('payments')->name('payment.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/notify/{type}/{id}', [PaymentController::class, 'sendNotification'])->name('send-notification');
    });
});

Route::post('bapp/{id}/save-signature', [BAPPController::class, 'saveSignature'])->name('bapp.save-signature');

// BAPP Approval Routes
Route::prefix('bapp-approval')->name('bapp.approval.')->middleware(['auth'])->group(function () {
    Route::get('/', [BAPPController::class, 'approvalIndex'])->name('index');
    Route::get('/{id}', [BAPPController::class, 'approvalShow'])->name('show');
    Route::post('/{id}/approve', [BAPPController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [BAPPController::class, 'reject'])->name('reject');
    Route::post('/{id}/signature', [BAPPController::class, 'saveSignature'])->name('save-signature');
});

require __DIR__.'/auth.php';
