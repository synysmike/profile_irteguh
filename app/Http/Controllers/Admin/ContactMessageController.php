<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ContactMessage::query()->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('filter')) {
            if ($request->filter === 'unread') {
                $query->unread();
            } elseif ($request->filter === 'read') {
                $query->read();
            }
        }

        $messages = $query->paginate(20);
        $unreadCount = ContactMessage::unread()->count();
        $totalCount = ContactMessage::count();

        return view('admin.contact-messages.index', compact('messages', 'unreadCount', 'totalCount'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $message = ContactMessage::findOrFail($id);
        
        // Mark as read when viewing
        if (!$message->is_read) {
            $message->markAsRead();
        }

        return view('admin.contact-messages.show', compact('message'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.contact-messages.index')
            ->with('success', 'Pesan berhasil dihapus.');
    }

    /**
     * Mark message as read
     */
    public function markAsRead(string $id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->markAsRead();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesan ditandai sebagai sudah dibaca.',
            ]);
        }

        return redirect()->back()->with('success', 'Pesan ditandai sebagai sudah dibaca.');
    }

    /**
     * Mark message as unread
     */
    public function markAsUnread(string $id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update([
            'is_read' => false,
            'read_at' => null,
        ]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesan ditandai sebagai belum dibaca.',
            ]);
        }

        return redirect()->back()->with('success', 'Pesan ditandai sebagai belum dibaca.');
    }
}
