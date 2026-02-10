<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="transaction_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal *</label>
        <input type="date" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', isset($cashTransaction) && $cashTransaction ? $cashTransaction->transaction_date?->format('Y-m-d') : date('Y-m-d')) }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="transaction_type" class="block text-sm font-medium text-gray-700 mb-2">Jenis Transaksi *</label>
        <select id="transaction_type" name="transaction_type" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="debit" {{ old('transaction_type', isset($cashTransaction) && $cashTransaction ? $cashTransaction->transaction_type : '') == 'debit' ? 'selected' : '' }}>Penerimaan Kas (Debit)</option>
            <option value="credit" {{ old('transaction_type', isset($cashTransaction) && $cashTransaction ? $cashTransaction->transaction_type : '') == 'credit' ? 'selected' : '' }}>Pengeluaran Kas (Credit)</option>
        </select>
    </div>

    <div class="md:col-span-2">
        <label for="chart_of_account_id" class="block text-sm font-medium text-gray-700 mb-2">Akun Kas/Bank *</label>
        <select id="chart_of_account_id" name="chart_of_account_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="">-- Pilih Akun Kas/Bank --</option>
            @foreach($accounts as $acc)
            <option value="{{ $acc->id }}" {{ old('chart_of_account_id', isset($cashTransaction) && $cashTransaction ? $cashTransaction->chart_of_account_id : '') == $acc->id ? 'selected' : '' }}>{{ $acc->code }} - {{ $acc->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Akun dari Master Keuangan → Akun Perkiraan (tipe: Kas)</p>
    </div>

    <div>
        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Jumlah (Rp) *</label>
        <input type="number" id="amount" name="amount" value="{{ old('amount', isset($cashTransaction) && $cashTransaction ? $cashTransaction->amount : 0) }}" min="0" step="1" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="reference" class="block text-sm font-medium text-gray-700 mb-2">Referensi / No. Dokumen</label>
        <input type="text" id="reference" name="reference" value="{{ old('reference', isset($cashTransaction) && $cashTransaction ? $cashTransaction->reference : '') }}" maxlength="100"
               placeholder="No. kwitansi, bukti transfer, dll"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
        <textarea id="description" name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('description', isset($cashTransaction) && $cashTransaction ? $cashTransaction->description : '') }}</textarea>
    </div>

    <div class="md:col-span-2">
        <label for="document" class="block text-sm font-medium text-gray-700 mb-2">Upload Dokumen (Gambar/PDF)</label>
        <input type="file" id="document" name="document" accept="image/*,.pdf"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
        <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, PDF. Maksimal 5MB.</p>
        
        @if(isset($cashTransaction) && $cashTransaction && $cashTransaction->document_path)
        <div class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @php
                        $ext = strtolower(pathinfo($cashTransaction->document_path, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                    @endphp
                    @if($isImage)
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    @else
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @endif
                    <span class="text-sm text-gray-700">{{ basename($cashTransaction->document_path) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.cash-transactions.download', $cashTransaction->id) }}" target="_blank" class="text-sm text-purple-600 hover:text-purple-800">Lihat</a>
                    <label class="flex items-center gap-1 text-sm text-red-600 cursor-pointer">
                        <input type="checkbox" name="remove_document" value="1" class="w-4 h-4 text-red-600 border-gray-300 rounded">
                        Hapus
                    </label>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
