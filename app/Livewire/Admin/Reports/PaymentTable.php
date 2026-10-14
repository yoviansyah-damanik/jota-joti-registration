<?php

namespace App\Livewire\Admin\Reports;

use App\Livewire\Concerns\UsesDaisyPagination;
use App\Services\PaymentReportService;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Reactive;
use Livewire\Component;

#[Lazy]
class PaymentTable extends Component
{
    use UsesDaisyPagination;

    #[Reactive]
    public string $from = '';

    #[Reactive]
    public string $to = '';

    #[Reactive]
    public string $scope = 'received';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(PaymentReportService $report)
    {
        return view('livewire.admin.reports.payment-table', [
            'troops' => $report->query([
                'from' => $this->from,
                'to' => $this->to,
                'scope' => $this->scope,
                'search' => $this->search,
            ])->paginate(15)->onEachSide(1),
        ]);
    }
}
