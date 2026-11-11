<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Project;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleTransaction;
use App\Models\Setting;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LayananCheckoutService
{
    /**
     * @param  array{name:string,phone:string,address:string,customer_type_id:int,company_name?:?string,payment_method?:string}  $buyer
     * @param  array<int, array{id:string,qty:int}>  $items  id = alloc-{id}|project-{id}
     * @return array{customer:Customer,sale:?Sale,projects:array<int,Project>,whatsapp_url:string,message:string}
     */
    public function checkout(array $buyer, array $items): array
    {
        if (count($items) === 0) {
            throw ValidationException::withMessages([
                'items' => ['Keranjang masih kosong.'],
            ]);
        }

        $phone = PhoneNumber::normalize($buyer['phone'] ?? null);
        if (! $phone) {
            throw ValidationException::withMessages([
                'phone' => ['Nomor WhatsApp tidak valid.'],
            ]);
        }

        $name = trim((string) ($buyer['name'] ?? ''));
        $address = trim((string) ($buyer['address'] ?? ''));
        $companyName = trim((string) ($buyer['company_name'] ?? ''));
        $payment = (($buyer['payment_method'] ?? 'transfer') === 'cod') ? 'COD / Bayar di Tempat' : 'Transfer Bank';

        if ($name === '' || $address === '') {
            throw ValidationException::withMessages([
                'name' => ['Nama dan alamat/catatan wajib diisi.'],
            ]);
        }

        $customerType = CustomerType::with('category')->find((int) ($buyer['customer_type_id'] ?? 0));
        if (! $customerType) {
            throw ValidationException::withMessages([
                'customer_type_id' => ['Pilih tipe customer.'],
            ]);
        }

        $legacyType = $customerType->resolveLegacyKey();
        if ($legacyType === 'company' && $companyName === '') {
            throw ValidationException::withMessages([
                'company_name' => ['Nama perusahaan wajib diisi untuk tipe Perusahaan.'],
            ]);
        }

        return DB::transaction(function () use ($phone, $name, $address, $companyName, $payment, $items, $customerType, $legacyType) {
            $payload = [
                'name' => $name,
                'address' => $address,
                'customer_type_id' => $customerType->id,
                'customer_type' => $legacyType,
                'company_name' => $companyName !== '' ? $companyName : null,
                'is_active' => true,
            ];

            $customer = Customer::query()->where('phone', $phone)->first();
            if ($customer) {
                $customer->update($payload);
            } else {
                $customer = Customer::create(array_merge($payload, [
                    'phone' => $phone,
                    'notes' => 'Dibuat dari checkout Layanan Kami',
                ]));
            }

            $customer->loadMissing('customerType.category');

            $productLines = [];
            $clonedProjects = [];
            $lineSummaries = [];

            foreach ($items as $index => $row) {
                $id = (string) ($row['id'] ?? '');
                $qty = (int) ($row['qty'] ?? 0);

                if ($qty < 1) {
                    throw ValidationException::withMessages([
                        "items.{$index}" => ['Qty tidak valid.'],
                    ]);
                }

                if (str_starts_with($id, 'alloc-')) {
                    $allocId = (int) substr($id, 6);
                    $allocation = SaleTransaction::layananPublic()->find($allocId);
                    if (! $allocation) {
                        throw ValidationException::withMessages([
                            "items.{$index}" => ['Produk tidak tersedia di etalase.'],
                        ]);
                    }
                    if ($qty > (int) $allocation->quantity) {
                        throw ValidationException::withMessages([
                            "items.{$index}" => ['Qty melebihi sisa stok alokasi (max '.$allocation->quantity.').'],
                        ]);
                    }

                    $unit = (float) $allocation->unit_price;
                    $subtotal = round($unit * $qty, 2);
                    $productLines[] = [
                        'allocation' => $allocation,
                        'quantity' => $qty,
                        'unit_price' => $unit,
                        'subtotal' => $subtotal,
                        'description' => $allocation->description,
                    ];
                    $lineSummaries[] = [
                        'type' => 'produk',
                        'name' => $allocation->description,
                        'qty' => $qty,
                        'subtotal' => $subtotal,
                    ];
                    continue;
                }

                if (str_starts_with($id, 'project-')) {
                    $projectId = (int) substr($id, 8);
                    $template = Project::layananPublic()->find($projectId);
                    if (! $template) {
                        throw ValidationException::withMessages([
                            "items.{$index}" => ['Jasa tidak tersedia di etalase.'],
                        ]);
                    }
                    if ($qty > 1) {
                        throw ValidationException::withMessages([
                            "items.{$index}" => ['Qty jasa maksimal 1 per item.'],
                        ]);
                    }

                    $clone = $this->cloneProjectTemplate($template, $customer, $payment);
                    $clonedProjects[] = $clone;
                    $lineSummaries[] = [
                        'type' => 'jasa',
                        'name' => $clone->title,
                        'qty' => 1,
                        'subtotal' => (float) $clone->total,
                        'code' => $clone->code,
                    ];
                    continue;
                }

                throw ValidationException::withMessages([
                    "items.{$index}" => ['Item keranjang tidak dikenali.'],
                ]);
            }

            $sale = null;
            if (count($productLines) > 0) {
                $sale = $this->createPendingSale($customer, $productLines, $payment);
            }

            $message = $this->buildWhatsappMessage($customer, $payment, $lineSummaries, $sale, $clonedProjects);
            $waNumber = preg_replace('/\D+/', '', Setting::contactWhatsapp()) ?: '6281332444088';
            $whatsappUrl = 'https://wa.me/'.$waNumber.'?text='.rawurlencode($message);

            return [
                'customer' => $customer,
                'sale' => $sale,
                'projects' => $clonedProjects,
                'whatsapp_url' => $whatsappUrl,
                'message' => $message,
            ];
        });
    }

    /**
     * @param  array<int, array{allocation:SaleTransaction,quantity:int,unit_price:float,subtotal:float,description:string}>  $productLines
     */
    private function createPendingSale(Customer $customer, array $productLines, string $payment): Sale
    {
        $subtotal = round(array_sum(array_column($productLines, 'subtotal')), 2);
        $allocRefs = collect($productLines)
            ->map(fn ($line) => 'alloc#'.$line['allocation']->id.'×'.$line['quantity'])
            ->implode(', ');

        $sale = Sale::create([
            'customer_id' => $customer->id,
            'invoice_number' => Sale::generateInvoiceNumber(),
            'sale_date' => now()->toDateString(),
            'subtotal' => $subtotal,
            'ppn_amount' => 0,
            'total' => $subtotal,
            'discount_amount' => 0,
            'show_in_public' => false,
            'is_posted' => false,
            'is_layanan_pending' => true,
            'notes' => '[Layanan Kami] Draft pending · bayar: '.$payment.' · sumber: '.$allocRefs,
        ]);

        foreach (array_values($productLines) as $index => $line) {
            SaleItem::create([
                'sale_id' => $sale->id,
                // sengaja null: belum potong/lock alokasi sampai admin konfirmasi (Fase D)
                'sale_transaction_id' => null,
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'discount_amount' => 0,
                'subtotal' => $line['subtotal'],
                'notes' => 'layanan_alloc:'.$line['allocation']->id,
                'sort_order' => $index,
            ]);
        }

        return $sale;
    }

    private function cloneProjectTemplate(Project $template, Customer $customer, string $payment): Project
    {
        return Project::create([
            'code' => Project::generateCode(),
            'title' => $template->title,
            'slug' => Project::makeUniqueSlug($template->title),
            'description' => $template->description,
            'customer_id' => $customer->id,
            'tax_id' => $template->tax_id,
            'discount_id' => $template->discount_id,
            'discount_name' => $template->discount_name,
            'discount_type' => $template->discount_type,
            'discount_value' => $template->discount_value,
            'discount_amount' => $template->discount_amount,
            'status' => 'pending',
            'progress_percent' => 0,
            'base_subtotal' => $template->base_subtotal,
            'subtotal' => $template->subtotal,
            'ppn_amount' => $template->ppn_amount,
            'tax_name' => $template->tax_name,
            'tax_rate' => $template->tax_rate,
            'tax_calculation_type' => $template->tax_calculation_type,
            'total' => $template->total,
            'payment_method' => $template->payment_method ?: 'termin',
            'start_date' => null,
            'due_date' => null,
            'notes' => '[Layanan Kami] Draft dari template '.$template->code.' · bayar: '.$payment,
            'is_portfolio_public' => false,
            'is_portfolio_featured' => false,
            'show_in_layanan' => false,
            'service_id' => $template->service_id,
            'layanan_template_project_id' => $template->id,
            'is_layanan_pending' => true,
            'portfolio_category' => $template->portfolio_category,
            'portfolio_excerpt' => $template->portfolio_excerpt,
        ]);
    }

    /**
     * @param  array<int, array{type:string,name:string,qty:int,subtotal:float,code?:string}>  $lineSummaries
     * @param  array<int, Project>  $projects
     */
    private function buildWhatsappMessage(
        Customer $customer,
        string $payment,
        array $lineSummaries,
        ?Sale $sale,
        array $projects
    ): string {
        $customer->loadMissing('customerType');
        $typeLabel = $customer->customerType?->name
            ?? (($customer->customer_type === 'company') ? 'Perusahaan' : 'Individu');

        $lines = [];
        $lines[] = '*Pesanan Layanan Kami*';
        $lines[] = 'Nama: '.$customer->name;
        $lines[] = 'Tipe: '.$typeLabel;
        if ($customer->company_name) {
            $lines[] = 'Lembaga/Perusahaan: '.$customer->company_name;
        }
        $lines[] = 'WA: '.PhoneNumber::toLocal($customer->phone);
        $lines[] = 'Alamat/Catatan: '.$customer->address;
        $lines[] = 'Bayar: '.$payment;
        $lines[] = '';
        $lines[] = '*Item:*';

        $total = 0.0;
        foreach ($lineSummaries as $item) {
            $total += (float) $item['subtotal'];
            $extra = isset($item['code']) ? ' ('.$item['code'].')' : '';
            $lines[] = '- ['.$item['type'].'] '.$item['name'].$extra
                .' ×'.$item['qty']
                .' = Rp '.number_format((float) $item['subtotal'], 0, ',', '.');
        }

        $lines[] = '';
        $lines[] = '*Total: Rp '.number_format($total, 0, ',', '.').'*';

        if ($sale) {
            $lines[] = 'Draft Sale: '.$sale->invoice_number;
        }
        if (count($projects) > 0) {
            $codes = collect($projects)->pluck('code')->implode(', ');
            $lines[] = 'Draft Project: '.$codes;
        }

        $lines[] = '';
        $lines[] = '_Status: menunggu konfirmasi admin_';

        return implode("\n", $lines);
    }
}
