<?php

namespace App\Http\Controllers;

use App\Models\Spt;
use App\Models\SptConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SptConfirmationController extends Controller
{
    /**
     * Menyimpan konfirmasi SPT
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Spt  $spt
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Spt $spt)
    {
        $user = Auth::user();
        
        // Tidak perlu findOrFail karena route model binding sudah menangani ini
        
        // Cek apakah sudah ada konfirmasi dari role yang sama
        $existingConfirmation = SptConfirmation::where('spt_id', $spt->id)
            ->where('user_id', $user->id)
            ->where('role_name', $user->role_name)
            ->first();
            
        if ($existingConfirmation) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah mengkonfirmasi SPT ini sebelumnya.'
            ], 400);
        }
        
        // Buat konfirmasi baru
        $confirmation = new SptConfirmation([
            'spt_id' => $spt->id,
            'user_id' => $user->id,
            'role_name' => $user->role_name,
            'confirmed_at' => now()
        ]);
        
        $spt->confirmations()->save($confirmation);
        
        return response()->json([
            'success' => true,
            'message' => 'SPT berhasil dikonfirmasi.',
            'confirmation' => $confirmation
        ]);
    }
    
    /**
     * Menghapus konfirmasi SPT
     *
     * @param  \App\Models\Spt  $spt
     * @return \Illuminate\Http\Response
     */
    public function destroy(Spt $spt)
    {
        $user = Auth::user();
        
        $deleted = SptConfirmation::where('spt_id', $spt->id)
            ->where('user_id', $user->id)
            ->where('role_name', $user->role_name)
            ->delete();
            
        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Konfirmasi SPT berhasil dibatalkan.'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Konfirmasi SPT tidak ditemukan.'
        ], 404);
    }
}
