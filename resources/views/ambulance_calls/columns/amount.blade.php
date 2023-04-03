<div class="d-flex justify-content-end">
@if($row->amount)
        <p class="mt-3">{{ getCurrencyFormat($row->amount) }}</p>
@else
        N/A
@endif
</div>
