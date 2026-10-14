<?php

namespace App\Livewire\Admin\Reports;

use App\Services\PaymentReportService;
use App\Services\SettingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Laporan penerimaan uang pendaftaran; ringkasan dan rincian dimuat sebagai komponen lazy.
 */
#[Layout('layouts.admin')]
class Payments extends Component
{
    #[Url(except: '')]
    public string $from = '';

    #[Url(except: '')]
    public string $to = '';

    #[Url(except: 'received')]
    public string $scope = 'received';

    public function updatedScope(): void
    {
        if (! in_array($this->scope, PaymentReportService::SCOPES, true)) {
            $this->scope = 'received';
        }
    }

    public function resetFilters(): void
    {
        $this->reset('from', 'to', 'scope');
    }

    public function render(SettingService $settings)
    {
        return view('livewire.admin.reports.payments', [
            'scopes' => [
                'received' => __('Received'),
                'pending' => __('Awaiting verification'),
                'rejected' => __('Rejected'),
                'all' => __('All'),
            ],
            'fee' => (int) $settings->get('registration_fee'),
            'filters' => ['from' => $this->from, 'to' => $this->to, 'scope' => $this->scope],
        ])->title(__('Fee Report'));
    }
}
