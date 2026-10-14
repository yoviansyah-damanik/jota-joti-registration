<?php

use App\Http\Controllers\AdminExportController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ParticipantCardController;
use App\Livewire\Admin;
use App\Livewire\Public;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Publik (tanpa login)
Route::livewire('/', Public\TermsPage::class)->name('home');
Route::livewire('/daftar', Public\Registration\Wizard::class)->name('register');
Route::livewire('/daftar/selesai', Public\Registration\Completed::class)->name('register.completed');
Route::get('/daftar/dokumen/{type}', [DownloadController::class, 'draftDocument'])
    ->middleware('throttle:60,1')
    ->name('register.document');
Route::get('/daftar/bukti/{code}', [DownloadController::class, 'registrationReceipt'])
    ->middleware('throttle:20,1')
    ->name('register.receipt');
Route::livewire('/cetak-kartu', Public\CardsPage::class)->middleware('cards.published')->name('cards.index');
Route::livewire('/status', Public\StatusCheck::class)->name('status');
Route::get('/template/peserta.xlsx', [DownloadController::class, 'participantTemplate'])
    ->middleware('throttle:30,1')
    ->name('template.participants');
Route::get('/logo/download', [DownloadController::class, 'officialLogo'])
    ->middleware('throttle:30,1')
    ->name('logo.download');
Route::get('/kartu/{code}', [ParticipantCardController::class, 'registrant'])
    ->middleware(['throttle:20,1', 'cards.published'])
    ->name('cards.registrant');
Route::get('/kartu/gudep/{troop}', [ParticipantCardController::class, 'public'])
    ->middleware(['throttle:20,1', 'cards.published'])
    ->name('cards.public');
Route::get('/kartu/{code}/pdf/{participant?}', [ParticipantCardController::class, 'registrantPdf'])
    ->middleware(['throttle:20,1', 'cards.published'])
    ->name('cards.registrant.pdf');
Route::get('/kartu/gudep/{troop}/pdf/{participant?}', [ParticipantCardController::class, 'publicPdf'])
    ->middleware(['throttle:20,1', 'cards.published'])
    ->name('cards.public.pdf');
Route::get('/sertifikat/{code}/gudep', [CertificateController::class, 'troop'])
    ->middleware(['throttle:20,1', 'certificates.published'])
    ->name('certificates.troop');
Route::get('/sertifikat/{code}/peserta/{participant?}', [CertificateController::class, 'participants'])
    ->middleware(['throttle:20,1', 'certificates.published'])
    ->name('certificates.participants');

// Pengiriman dan Cek Penilaian Tugas
Route::livewire('/tugas', Public\Tasks\Submit::class)->name('tasks.submit');
Route::livewire('/tugas/status', Public\Tasks\CheckStatus::class)->name('tasks.status');
Route::get('/tugas/dokumen/{submission}/{type}', [DownloadController::class, 'taskDocument'])->name('tasks.download');

// Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/login', Admin\Auth\Login::class)->middleware('guest')->name('login');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    })->middleware('auth')->name('logout');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::livewire('/', Admin\Dashboard::class)->name('dashboard');
        Route::livewire('/registrations', Admin\Registrations\Index::class)->name('registrations.index');
        Route::livewire('/registrations/{troop}', Admin\Registrations\Show::class)->name('registrations.show');
        Route::livewire('/groups', Admin\Groups\Assign::class)->name('groups');
        Route::livewire('/groups/details', Admin\Groups\Details::class)->name('groups.details');
        Route::livewire('/attendance', Admin\Attendance\Index::class)->name('attendance.index');
        Route::livewire('/attendance/sessions', Admin\Attendance\Sessions::class)->name('attendance.sessions');
        Route::livewire('/tasks', Admin\Tasks\Index::class)->name('tasks.index');
        Route::livewire('/reports/payments', Admin\Reports\Payments::class)->name('reports.payments');
        Route::livewire('/settings', Admin\Settings::class)->name('settings');
        Route::livewire('/profile', Admin\Profile::class)->name('profile');

        Route::get('/documents/{document}', [DownloadController::class, 'document'])->name('documents.show');
        Route::get('/exports/participants', [AdminExportController::class, 'participants'])->name('exports.participants');
        Route::get('/exports/attendance/{session}', [AdminExportController::class, 'attendance'])->name('exports.attendance');
        Route::get('/cards/{troop}', [ParticipantCardController::class, 'admin'])->name('cards.show');
        Route::get('/cards/{troop}/pdf/{participant?}', [ParticipantCardController::class, 'adminPdf'])->name('cards.pdf');
        Route::get('/certificates/{troop}/gudep', [CertificateController::class, 'adminTroop'])->name('certificates.troop');
        Route::get('/certificates/{troop}/peserta/{participant?}', [CertificateController::class, 'adminParticipants'])->name('certificates.participants');
        Route::get('/exports/payments', [AdminExportController::class, 'payments'])->name('exports.payments');
        Route::get('/print/payments', [AdminExportController::class, 'paymentsSheet'])->name('print.payments');
        Route::get('/print/attendance/{session}', [AdminExportController::class, 'attendanceSheet'])->name('print.attendance');
        Route::get('/print/attendance-manual', [AdminExportController::class, 'manualAttendanceSheet'])->name('print.attendance.manual');
    });
});
