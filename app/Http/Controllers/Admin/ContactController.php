<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = ContactMessage::query()->orderByDesc('created_at');

        if ($request->filter === 'unread') {
            $query->unread();
        } elseif ($request->filter === 'read') {
            $query->read();
        } elseif ($request->filter === 'pending') {
            $query->whereNull('admin_response');
        } elseif ($request->filter === 'responded') {
            $query->whereNotNull('admin_response');
        }

        $messages = $query->paginate(15)->withQueryString();
        $unreadCount = ContactMessage::unread()->count();
        $pendingCount = ContactMessage::whereNull('admin_response')->count();
        $totalCount = ContactMessage::count();

        $contactSettings = [
            'address' => Setting::contactAddress(),
            'email' => Setting::contactEmail(),
            'whatsapp' => Setting::contactWhatsapp(),
            'whatsapp_label' => Setting::contactWhatsappLabel(),
            'response_note' => Setting::contactResponseNote(),
        ];

        return view('admin.contact.index', compact(
            'messages',
            'unreadCount',
            'pendingCount',
            'totalCount',
            'contactSettings'
        ));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_address' => 'required|string|max:500',
            'contact_email' => 'required|email|max:255',
            'contact_whatsapp' => 'required|string|max:20',
            'contact_whatsapp_label' => 'nullable|string|max:255',
            'contact_response_note' => 'nullable|string|max:500',
        ]);

        $whatsapp = preg_replace('/\D+/', '', $validated['contact_whatsapp']);
        if (str_starts_with($whatsapp, '0')) {
            $whatsapp = '62' . substr($whatsapp, 1);
        }

        Setting::set('contact_address', $validated['contact_address']);
        Setting::set('contact_email', $validated['contact_email']);
        Setting::set('contact_whatsapp', $whatsapp);
        Setting::set('contact_whatsapp_label', $validated['contact_whatsapp_label'] ?? 'Chat dengan kami di WhatsApp');
        Setting::set('contact_response_note', $validated['contact_response_note'] ?? 'Kami biasanya merespons dalam 24-48 jam');

        return redirect()->route('admin.contact.index')
            ->with('success', 'Informasi kontak publik berhasil diperbarui.');
    }

    public function showMessage(string $id)
    {
        $message = ContactMessage::findOrFail($id);

        if (!$message->is_read) {
            $message->markAsRead();
        }

        return view('admin.contact.show', compact('message'));
    }

    public function respond(Request $request, string $id): RedirectResponse
    {
        $message = ContactMessage::findOrFail($id);

        $validated = $request->validate([
            'admin_response' => 'required|string|max:5000',
        ]);

        $message->update([
            'admin_response' => $validated['admin_response'],
            'responded_at' => now(),
            'is_read' => true,
            'read_at' => $message->read_at ?? now(),
        ]);

        return redirect()->route('admin.contact.messages.show', $message->id)
            ->with('success', 'Respon berhasil disimpan.');
    }

    public function markAsRead(string $id): RedirectResponse
    {
        ContactMessage::findOrFail($id)->markAsRead();

        return redirect()->back()->with('success', 'Pesan ditandai sudah dibaca.');
    }

    public function markAsUnread(string $id): RedirectResponse
    {
        ContactMessage::findOrFail($id)->update([
            'is_read' => false,
            'read_at' => null,
        ]);

        return redirect()->back()->with('success', 'Pesan ditandai belum dibaca.');
    }

    public function destroyMessage(string $id)
    {
        ContactMessage::findOrFail($id)->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.contact.index')
            ->with('success', 'Pesan berhasil dihapus.');
    }
}
