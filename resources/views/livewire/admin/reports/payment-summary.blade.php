<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-ui.stat compact :title="__('Received')" :value="\App\Support\Rupiah::format($summary['received'])" tone="success" icon="check-circle"
        :description="__(':troops troops · :count participants', ['troops' => $summary['received_troops'], 'count' => $summary['received_participants']])" />

    <x-ui.stat compact :title="__('Awaiting verification')" :value="\App\Support\Rupiah::format($summary['pending'])" tone="warning" icon="clipboard-check"
        :description="__(':troops troops', ['troops' => $summary['pending_troops']])" />

    <x-ui.stat compact :title="__('Expected total')" :value="\App\Support\Rupiah::format($summary['expected'])" tone="primary" icon="banknotes"
        :description="__('Received + awaiting verification')" />

    <x-ui.stat compact :title="__('Rejected')" :value="$summary['rejected_troops']" tone="error" icon="x-circle"
        :description="__('Troops, not counted in the total')" />
</div>
