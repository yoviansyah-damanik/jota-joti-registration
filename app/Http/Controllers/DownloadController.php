<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Exports\ParticipantTemplateExport;
use App\Models\Troop;
use App\Models\TroopDocument;
use App\Services\RegistrationReceiptPdf;
use App\Services\SettingService;
use App\Support\RegistrationDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DownloadController extends Controller
{
    public function participantTemplate()
    {
        return Excel::download(new ParticipantTemplateExport, 'template-peserta.xlsx');
    }

    /**
     * Bukti pendaftaran (PDF) berisi kode pendaftaran; akses lewat kode pendaftaran itu sendiri.
     */
    public function registrationReceipt(string $code, SettingService $settings, RegistrationReceiptPdf $receipt)
    {
        $troop = Troop::where('registration_code', strtoupper(trim($code)))
            ->orWhere('id', trim($code))
            ->firstOrFail();

        $content = $receipt->render($troop, $settings->get('event_name'), $settings->get('organization_name'));

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="bukti-pendaftaran-'.$troop->registration_code.'.pdf"',
        ]);
    }

    /**
     * Pratinjau/unduh file draft milik sesi pendaftar sendiri (hanya PDF dan gambar yang dibuka inline).
     */
    public function draftDocument(Request $request, DocumentType $type, RegistrationDraft $draft)
    {
        $file = $draft->file($type);
        $disk = Storage::disk(RegistrationDraft::DISK);
        abort_unless($file && $disk->exists($file['path']), 404);

        $mime = $file['mime'] ?? '';
        $previewable = $mime === 'application/pdf' || str_starts_with($mime, 'image/');

        if ($request->boolean('inline') && $previewable) {
            return $disk->response($file['path'], $file['original_name'], ['Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff'], 'inline');
        }

        return $disk->download($file['path'], $file['original_name']);
    }

    /**
     * Dokumen gudep hanya dapat diakses admin; ?inline=1 untuk pratinjau di browser.
     */
    public function document(Request $request, TroopDocument $document)
    {
        $disk = Storage::disk(RegistrationDraft::DISK);
        abort_unless($disk->exists($document->path), 404);

        $filename = $document->troop->troop_number.' - '.$document->type->label().'.'.pathinfo($document->path, PATHINFO_EXTENSION);

        if ($request->boolean('inline')) {
            return $disk->response($document->path, $filename, ['Content-Type' => $document->mime], 'inline');
        }

        return $disk->download($document->path, $filename);
    }

    /**
     * Unduh atau pratinjau berkas tugas (JOTA Digital / JOTI).
     * Dapat diakses admin atau publik yang menyertakan kode ID peserta yang cocok.
     */
    public function taskDocument(Request $request, \App\Models\TaskSubmission $submission, string $type)
    {
        $path = $type === 'jota' ? $submission->jota_file_path : $submission->joti_file_path;
        $disk = Storage::disk('local');
        abort_unless($path && $disk->exists($path), 404);

        if (! auth()->check() || ! auth()->user()->is_admin) {
            $code = $request->query('code');
            abort_unless($code && strtoupper(trim($code)) === strtoupper($submission->participant->attendance_code), 403);
        }

        $typeName = $type === 'jota' ? 'Tugas-JOTA-Digital' : 'Tugas-JOTI';
        $filename = $submission->participant->attendance_code.' - '.$typeName.' - '.$submission->participant->full_name.'.pdf';

        if ($request->boolean('inline')) {
            return $disk->response($path, $filename, ['Content-Type' => 'application/pdf'], 'inline');
        }

        return $disk->download($path, $filename);
    }

    /**
     * Unduh berkas logo resmi JOTA-JOTI.
     */
    public function officialLogo(SettingService $settings)
    {
        $path = $settings->logoPath();
        abort_unless(file_exists($path), 404);

        $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'webp';
        $filename = 'logo-jotajoti-'.now()->year.'.'.$ext;

        return response()->download($path, $filename);
    }
}
