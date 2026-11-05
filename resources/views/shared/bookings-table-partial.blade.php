@php
  $prefix = $prefix ?? 'admin';
  $hasFilter = request()->filled('q') || request()->filled('status') || request()->filled('court') || request()->filled('date');
@endphp

@if($bookings->isEmpty())
  <div class="lx-empty">
    <div class="lx-empty-icon"><i class="bi bi-inbox"></i></div>
    <div class="lx-empty-title">Tidak ada booking yang cocok</div>
    <div class="small mb-3">Coba ubah kata kunci pencarian atau reset filter yang aktif.</div>
    @if($hasFilter)
      <a href="{{ route($prefix . '.bookings.index') }}" class="btn btn-light btn-sm">Reset Filter</a>
    @endif
  </div>
@else
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th class="ps-4">Invoice &amp; Pemesan</th>
          <th>Lapangan &amp; Jadwal</th>
          <th class="text-end">Total Tagihan (QRIS)</th>
          <th class="text-center">Bukti QRIS</th>
          <th class="text-center">Status Approval</th>
          <th>Di-Approve Oleh (Admin)</th>
          <th class="text-end pe-4">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($bookings as $b)
          @php
            $start = \Carbon\Carbon::parse($b->start_dt);
            $end   = \Carbon\Carbon::parse($b->end_dt);
            $nominalTransfer = $b->payment_method === 'dp' ? $b->dp_amount : $b->total_price;
            $isPending = ($b->status === 'pending');
          @endphp
          <tr class="lx-row-link {{ $isPending ? 'table-warning bg-opacity-10' : '' }}" data-href="{{ route($prefix . '.bookings.show', $b) }}">
            <td class="ps-4">
              <div class="d-flex align-items-center gap-2">
                <span class="font-mono fw-bold text-dark">#{{ $b->formatted_invoice_no }}</span>
                @if($isPending)
                  <span class="badge bg-warning text-dark" style="font-size:0.6rem;letter-spacing:0.3px;"><i class="bi bi-clock-history me-1"></i>PERLU APPROVAL</span>
                @endif
              </div>
              <div class="fw-semibold text-dark">{{ $b->display_name }}</div>
              <div class="text-muted small font-mono">{{ $b->display_phone }}</div>
            </td>
            <td>
              <div class="fw-semibold text-dark">{{ $b->court->name }}</div>
              <div class="text-muted small">{{ $start->translatedFormat('d M Y') }} · <strong class="text-dark">{{ $start->format('H:i') }}–{{ $end->format('H:i') }}</strong></div>
            </td>
            <td class="text-end">
              <div class="fw-semibold text-dark">Rp{{ number_format($b->total_price, 0, ',', '.') }}</div>
              <span class="badge bg-light text-dark border" style="font-size:.65rem;">
                QRIS {{ strtoupper($b->payment_method ?? 'LUNAS') }} (Rp{{ number_format($nominalTransfer, 0, ',', '.') }})
              </span>
            </td>
            <td class="text-center" onclick="event.stopPropagation();">
              @if($b->payment_proof)
                <button type="button"
                        class="btn btn-light btn-sm border d-inline-flex align-items-center gap-1.5 py-1 px-2 js-preview-proof"
                        data-proof-url="{{ $b->payment_proof_url }}"
                        data-invoice="#{{ $b->formatted_invoice_no }}"
                        data-customer="{{ $b->display_name }}"
                        data-nominal="Rp{{ number_format($nominalTransfer, 0, ',', '.') }} ({{ strtoupper($b->payment_method) }})"
                        data-status="{{ $b->status }}"
                        data-confirm-url="{{ route($prefix . '.bookings.confirm', $b) }}"
                        title="Klik untuk melihat gambar bukti pembayaran QRIS">
                  <img src="{{ $b->payment_proof_url }}" alt="Bukti" class="rounded" style="width:26px;height:26px;object-fit:cover;">
                  <span class="small fw-semibold text-primary">Lihat Bukti</span>
                </button>
              @else
                <span class="badge bg-light text-muted border" style="font-size:.68rem;">Belum Upload</span>
              @endif
            </td>
            <td class="text-center">
              @if($b->status === 'confirmed')
                <span class="lx-pill lx-pill-success">Approved</span>
              @elseif($b->status === 'pending')
                <span class="lx-pill lx-pill-warning">Menunggu Approve</span>
              @else
                <span class="lx-pill lx-pill-danger">Dibatalkan</span>
              @endif
            </td>
            <td>
              @if($b->status === 'confirmed')
                <div class="fw-semibold text-dark d-flex align-items-center gap-1">
                  <i class="bi bi-person-check-fill text-success"></i>
                  <span>{{ $b->approver_name ?: 'Administrator' }}</span>
                </div>
                @if($b->approved_at_formatted)
                  <div class="text-muted" style="font-size:.7rem">{{ $b->approved_at_formatted }}</div>
                @endif
              @elseif($b->status === 'pending')
                <span class="text-muted small fst-italic text-warning fw-semibold">Belum di-approve</span>
              @else
                <span class="text-muted small">—</span>
              @endif
            </td>
            <td class="text-end pe-4" onclick="event.stopPropagation();">
              <div class="d-inline-flex align-items-center gap-1">
                @if($b->status === 'pending')
                  <form action="{{ route($prefix . '.bookings.confirm', $b) }}" method="POST" class="d-inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-success btn-sm" title="Approve Jadwal &amp; Validasi Pembayaran QRIS">
                      <i class="bi bi-check2-circle me-1"></i> Approve
                    </button>
                  </form>
                  <form action="{{ route($prefix . '.bookings.cancel', $b) }}" method="POST" class="d-inline"
                        data-confirm="Batalkan booking #{{ $b->formatted_invoice_no }} atas nama {{ $b->display_name }}?"
                        data-confirm-title="Batalkan Booking" data-confirm-ok="Ya, batalkan" data-confirm-variant="danger">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-light btn-sm btn-icon" title="Tolak / Batalkan">
                      <i class="bi bi-x-lg text-danger"></i>
                    </button>
                  </form>
                @endif
                <a href="{{ route($prefix . '.bookings.receipt', $b) }}" target="_blank" class="btn btn-light btn-sm btn-icon" title="Cetak Struk">
                  <i class="bi bi-printer"></i>
                </a>
                <a href="{{ route($prefix . '.bookings.show', $b) }}" class="btn {{ $b->remaining_amount > 0 && $b->status === 'confirmed' ? 'btn-primary' : 'btn-light' }} btn-sm">
                  {{ $b->remaining_amount > 0 && $b->status === 'confirmed' ? 'Lunasi DP' : 'Detail' }}
                </a>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif
