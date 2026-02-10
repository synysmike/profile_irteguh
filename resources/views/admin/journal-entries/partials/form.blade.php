<div class="grid grid-cols-1 gap-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="entry_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal *</label>
            <input type="date" id="entry_date" name="entry_date" value="{{ old('entry_date', isset($journalEntry) && $journalEntry ? $journalEntry->entry_date?->format('Y-m-d') : date('Y-m-d')) }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>
        <div>
            <label for="reference" class="block text-sm font-medium text-gray-700 mb-2">Referensi</label>
            <input type="text" id="reference" name="reference" value="{{ old('reference', isset($journalEntry) && $journalEntry ? $journalEntry->reference : '') }}" placeholder="No. dokumen"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>
    </div>
    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
        <textarea id="description" name="description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('description', isset($journalEntry) && $journalEntry ? $journalEntry->description : '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Baris Jurnal (Akun dari COA) *</label>
        <p class="text-xs text-gray-500 mb-2">Min. 2 baris. Total debit = total kredit. Kosongkan debit/credit untuk baris tidak dipakai.</p>
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-md">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Akun</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 w-32">Debit (Rp)</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 w-32">Kredit (Rp)</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Memo</th>
                    </tr>
                </thead>
                <tbody id="journal-lines-tbody">
                    @php
                        $defaultLine = (object)['chart_of_account_id'=>'','debit'=>0,'credit'=>0,'memo'=>''];
                        if (isset($journalEntry) && $journalEntry && $journalEntry->lines->count() > 0) {
                            $lines = $journalEntry->lines->map(fn($l) => (object)['chart_of_account_id'=>$l->chart_of_account_id,'debit'=>$l->debit,'credit'=>$l->credit,'memo'=>$l->memo ?? '']);
                        } else {
                            $lines = collect([$defaultLine, $defaultLine]);
                        }
                        while ($lines->count() < 4) { $lines->push($defaultLine); }
                        $lines = $lines->take(6);
                    @endphp
                    @foreach($lines as $idx => $line)
                    <tr class="journal-line-row">
                        <td class="px-2 py-1">
                            <select name="chart_of_account_id[]" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-purple-500">
                                <option value="">-- Akun --</option>
                                @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('chart_of_account_id.'.$idx, $line->chart_of_account_id ?? '') == $acc->id ? 'selected' : '' }}>{{ $acc->code }} - {{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-2 py-1">
                            <input type="number" name="debit[]" value="{{ old('debit.'.$idx, $line->debit ?? 0) }}" min="0" step="1" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded journal-debit">
                        </td>
                        <td class="px-2 py-1">
                            <input type="number" name="credit[]" value="{{ old('credit.'.$idx, $line->credit ?? 0) }}" min="0" step="1" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded journal-credit">
                        </td>
                        <td class="px-2 py-1">
                            <input type="text" name="memo[]" value="{{ old('memo.'.$idx, $line->memo ?? '') }}" placeholder="Memo" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
