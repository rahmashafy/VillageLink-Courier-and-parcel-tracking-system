
<?php

use App\Http\Controllers\AdminContactMessageController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminDriverController;
use App\Http\Controllers\AdminParcelController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AgentDashboardController;
use App\Http\Controllers\AgentParcelController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerParcelController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RefundRequestController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'contactStore'])->middleware('throttle:5,1')->name('contact.store');
Route::post('/payments/payhere/notify', [PaymentController::class, 'payHereNotify'])->name('payments.payhere.notify');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::post('/theme/{theme}', [ThemeController::class, 'switch'])->name('theme.switch');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/assistant', [ChatController::class, 'index'])->name('assistant.index');
    Route::post('/assistant', [ChatController::class, 'ask'])->middleware('throttle:30,1')->name('assistant.ask');

    Route::middleware('role:customer')->group(function () {
        Route::get('/customer/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');
        Route::get('/customer/chat', [ChatController::class, 'index'])->name('customer.chat');
        Route::post('/customer/chat', [ChatController::class, 'ask'])->middleware('throttle:30,1')->name('customer.chat.ask');
        Route::get('/customer/parcels/track', [CustomerParcelController::class, 'trackForm'])->name('customer.parcels.track');
        Route::post('/customer/parcels/track', [CustomerParcelController::class, 'trackResult'])->middleware('throttle:20,1')->name('customer.parcels.track.result');
        Route::get('/customer/parcels/create', [CustomerParcelController::class, 'create'])->name('customer.parcels.create');
        Route::post('/customer/parcels/route-preview', [CustomerParcelController::class, 'routePreview'])->middleware('throttle:30,1')->name('customer.parcels.route-preview');
        Route::post('/customer/parcels', [CustomerParcelController::class, 'store'])->name('customer.parcels.store');
        Route::get('/customer/parcels', [CustomerParcelController::class, 'index'])->name('customer.parcels.index');
        Route::get('/customer/parcels/{parcel}', [CustomerParcelController::class, 'show'])->name('customer.parcels.show');
        Route::get('/customer/parcels/{parcel}/location', [CustomerParcelController::class, 'latestLocation'])->name('customer.parcels.location');
        Route::get('/customer/parcels/{parcel}/pay', [PaymentController::class, 'payForm'])->name('customer.payments.pay');
        Route::post('/customer/parcels/{parcel}/pay', [PaymentController::class, 'pay'])->name('customer.payments.store');
        Route::get('/customer/payments/{payment}/return', [PaymentController::class, 'gatewayReturn'])->name('customer.payments.return');
        Route::get('/customer/payments/{payment}/cancel', [PaymentController::class, 'gatewayCancel'])->name('customer.payments.cancel');
        Route::get('/customer/parcels/{parcel}/invoice', [PaymentController::class, 'invoice'])->name('customer.payments.invoice');
        Route::get('/customer/parcels/{parcel}/invoice/pdf', [PaymentController::class, 'downloadPdf'])->name('customer.payments.invoice.pdf');
        Route::get('/customer/payments', [PaymentController::class, 'index'])->name('customer.payments.index');
        Route::get('/customer/refunds', [RefundRequestController::class, 'customerIndex'])->name('customer.refunds.index');
        Route::get('/customer/parcels/{parcel}/refund', [RefundRequestController::class, 'create'])->name('customer.refunds.create');
        Route::post('/customer/parcels/{parcel}/refund', [RefundRequestController::class, 'store'])->name('customer.refunds.store');
        Route::get('/customer/complaints', [ComplaintController::class, 'customerIndex'])->name('customer.complaints.index');
        Route::get('/customer/complaints/create', [ComplaintController::class, 'create'])->name('customer.complaints.create');
        Route::post('/customer/complaints', [ComplaintController::class, 'store'])->name('customer.complaints.store');
        Route::get('/customer/complaints/{complaint}', [ComplaintController::class, 'show'])->name('customer.complaints.show');
        Route::post('/customer/complaints/{complaint}/reply', [ComplaintController::class, 'customerReply'])->name('customer.complaints.reply');
        Route::get('/customer/parcels/{parcel}/rating', [RatingController::class, 'create'])->name('customer.ratings.create');
        Route::post('/customer/parcels/{parcel}/rating', [RatingController::class, 'store'])->name('customer.ratings.store');
    });

    Route::middleware('role:driver,agent')->group(function () {
        Route::get('/driver/dashboard', [AgentDashboardController::class, 'index'])->name('agent.dashboard');
        Route::get('/driver/parcels', [AgentParcelController::class, 'index'])->name('agent.parcels.index');
        Route::get('/driver/parcels/{parcel}/status', [AgentParcelController::class, 'editStatus'])->name('agent.parcels.status');
        Route::post('/driver/parcels/{parcel}/status', [AgentParcelController::class, 'updateStatus'])->name('agent.parcels.status.update');
        Route::post('/driver/parcels/{parcel}/location', [AgentParcelController::class, 'updateLocation'])->name('agent.parcels.location.update');
        Route::get('/driver/profile', [DriverProfileController::class, 'edit'])->name('driver.profile.edit');
        Route::put('/driver/profile', [DriverProfileController::class, 'update'])->name('driver.profile.update');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/reports', [AdminDashboardController::class, 'reports'])->name('admin.reports');
        Route::get('/admin/reports/export', [AdminDashboardController::class, 'exportReports'])->name('admin.reports.export');
        Route::get('/admin/payments', [AdminPaymentController::class, 'index'])->name('admin.payments.index');
        Route::patch('/admin/payments/{payment}', [AdminPaymentController::class, 'update'])->name('admin.payments.update');
        Route::get('/admin/refunds', [RefundRequestController::class, 'adminIndex'])->name('admin.refunds.index');
        Route::patch('/admin/refunds/{refundRequest}', [RefundRequestController::class, 'adminUpdate'])->name('admin.refunds.update');
        Route::get('/admin/drivers', [AdminDriverController::class, 'index'])->name('admin.drivers.index');
        Route::get('/admin/drivers/{driver}/edit', [AdminDriverController::class, 'edit'])->name('admin.drivers.edit');
        Route::put('/admin/drivers/{driver}', [AdminDriverController::class, 'update'])->name('admin.drivers.update');
        Route::get('/admin/parcels', [AdminParcelController::class, 'index'])->name('admin.parcels.index');
        Route::get('/admin/parcels/{parcel}/assign', [AdminParcelController::class, 'assignForm'])->name('admin.parcels.assign');
        Route::post('/admin/parcels/{parcel}/assign', [AdminParcelController::class, 'assignAgent'])->name('admin.parcels.assign.store');
        Route::get('/admin/complaints', [ComplaintController::class, 'adminIndex'])->name('admin.complaints.index');
        Route::get('/admin/contact-messages', [AdminContactMessageController::class, 'index'])->name('admin.contact-messages.index');
        Route::patch('/admin/contact-messages/{contactMessage}/read', [AdminContactMessageController::class, 'markRead'])->name('admin.contact-messages.read');
        Route::get('/admin/complaints/{complaint}', [ComplaintController::class, 'adminShow'])->name('admin.complaints.show');
        Route::patch('/admin/complaints/{complaint}', [ComplaintController::class, 'adminUpdate'])->name('admin.complaints.update');
        Route::post('/admin/complaints/{complaint}/reply', [ComplaintController::class, 'adminReply'])->name('admin.complaints.reply');
        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
        Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    });
});

require __DIR__.'/auth.php';
