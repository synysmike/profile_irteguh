<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index()
    {
        $captchaA = random_int(1, 9);
        $captchaB = random_int(1, 9);
        session([
            'contact_captcha_sum' => $captchaA + $captchaB,
        ]);

        $contactInfo = [
            'address' => Setting::contactAddress(),
            'email' => Setting::contactEmail(),
            'whatsapp' => Setting::contactWhatsapp(),
            'whatsapp_label' => Setting::contactWhatsappLabel(),
            'whatsapp_url' => Setting::contactWhatsappUrl(),
            'response_note' => Setting::contactResponseNote(),
            'maps_embed_url' => Setting::contactMapsEmbedUrl(),
        ];

        return view('public.contact', compact('contactInfo', 'captchaA', 'captchaB'));
    }
    
    public function store(Request $request)
    {
        if ($request->filled('website')) {
            return redirect()->route('contact')
                ->with('success', 'Terima kasih atas pesan Anda! Kami akan segera menghubungi Anda.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
            'phone' => 'nullable|string|max:20',
            'captcha_answer' => ['required', 'integer', function ($attribute, $value, $fail) {
                $expected = session('contact_captcha_sum');
                if ($expected === null || (int) $value !== (int) $expected) {
                    $fail('Jawaban verifikasi anti-robot tidak benar.');
                }
            }],
        ]);

        if ($validator->fails()) {
            return redirect()->route('contact')
                ->withErrors($validator)
                ->withInput();
        }

        session()->forget('contact_captcha_sum');

        ContactMessage::create([
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'phone' => $request->phone,
        ]);

        return redirect()->route('contact')->with('success', 'Terima kasih atas pesan Anda! Kami akan segera menghubungi Anda.');
    }
}
