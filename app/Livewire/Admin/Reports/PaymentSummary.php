<?php

namespace App\Livewire\Admin\Reports;

use App\Services\PaymentReportService;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Reactive;
use Livewire\Component;

#[Lazy]
class PaymentSummary extends Component
{
    #[Reactive]
    public string $from = '';

    #[Reactive]
    public string $to = '';

    public function render(PaymentReportService $report)
    {
        // ringkasan selalu memuat semua status pada periode terpilih
        return view('livewire.admin.reports.payment-summary', [
            'summary' => $report->summary(['from' => $this->from, 'to' => $this->to]),
        ]);
    }
}
