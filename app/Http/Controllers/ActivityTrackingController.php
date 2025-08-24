<?php

namespace App\Http\Controllers;

use App\Models\TrackingActivity;
use App\Models\SPT;
use Illuminate\Http\Request;

class ActivityTrackingController extends Controller
{
    /**
     * Display a listing of the tracking activities.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Sync tracking records with SPTs
            $this->syncTrackingRecords();

            $user = auth()->user();
            $username = $user->username;
            $userRole = $user->role_name;
            
            $query = TrackingActivity::with([
                'spt' => function($q) {
                    $q->withCount('lkt');
                },
                'spt.vendor',
                'spt.mandor'
            ]);
            
            // Apply role-based filtering
            if ($userRole === 'mandor') {
                // Get mandor's kode_mandor from foreman
                $mandor = \App\Models\Foreman::where('email', $username)
                    ->orWhere('kode_mandor', $username)
                    ->first();
                
                if ($mandor) {
                    // For Mandor, only show tracking activities that are linked to SPTs assigned to them
                    $query->whereHas('spt', function($q) use ($mandor) {
                        $q->where('kode_mandor', $mandor->kode_mandor)
                          ->where('status', 'Disetujui');
                    });
                    
                    // Log the query for debugging
                    \Log::info('Activity Tracking Query for Mandor:', [
                        'user_email' => $username,
                        'kode_mandor' => $mandor->kode_mandor,
                        'sql' => $query->toSql(),
                        'bindings' => $query->getBindings()
                    ]);
                } else {
                    // If no mandor found, return empty result
                    $query->whereRaw('1=0');
                    \Log::warning('No mandor found for user: ' . $username);
                }
            } else {
                // For other roles, apply the normal filtering
                $query->whereHas('spt', function($q) use ($user) {
                    $q->where('status', 'Disetujui');
                    
                    // If user is a vendor, only show their SPTs
                    if ($user && $user->hasRole('vendor') && $userVendor = $user->vendor) {
                        $q->where('kode_vendor', $userVendor->kode_vendor);
                    }
                });
            }
            
            $query->orderBy('updated_at', 'desc');

        
            
        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('kode_spt', 'like', '%' . $searchTerm . '%')
                  ->orWhere('kode_petak', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('spt.vendor', function($q) use ($searchTerm) {
                      $q->where('nama_vendor', 'like', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('spt.mandor', function($q) use ($searchTerm) {
                      $q->where('nama_mandor', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        // Apply status filter
        if ($request->has('status') && !empty($request->status)) {
            $status = $request->status;
            // Gunakan nilai status langsung dari request karena sudah sesuai dengan database
            $query->where('status_tracking', $status);
        }

        // Apply kode_petak filter
        if ($request->has('kode_petak') && !empty($request->kode_petak)) {
            $query->where('kode_petak', 'like', '%' . $request->kode_petak . '%');
        }

        // Apply date range filter
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('updated_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('updated_at', '<=', $request->end_date);
        }

        $trackingList = $query->paginate(15)->appends($request->query());

            return view('activity-tracking.index', compact('trackingList'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading tracking activities: ' . $e->getMessage());
        }
    }

    /**
     * Get tracking activity details.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getDetail($id)
    {
        $activity = TrackingActivity::with([
            'spt.vendor', 
            'spt.mandor',
            'spt.lkt' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'spt.lkt.vendorTebang',
            'spt.lkt.vendorAngkut'
        ])->findOrFail($id);

        $petak = [
            'code' => $activity->kode_petak,
            'status' => $activity->status_tracking,
            'location' => 'Lokasi Petak ' . $activity->kode_petak
        ];

        // Format LKT data for the view
        $formattedLkts = [];
        foreach ($activity->spt->lkt as $lkt) {
            $formattedLkts[] = [
                'kode_lkt' => $lkt->kode_lkt,
                'tanggal_tebang' => $lkt->tanggal_tebang ? \Carbon\Carbon::parse($lkt->tanggal_tebang)->format('d/m/Y') : '-',
                'vendor_tebang' => $lkt->vendorTebang ? $lkt->vendorTebang->nama_vendor : '-',
                'vendor_angkut' => $lkt->vendorAngkut ? $lkt->vendorAngkut->nama_vendor : '-',
                'status' => $lkt->status,
                'created_at' => $lkt->created_at->format('d/m/Y H:i')
            ];
        }
        
        return response()->json([
            'petak' => $petak,
            'lkts' => $formattedLkts,
            'lkt_count' => $activity->spt->lkt->count(),
            'spt_code' => $activity->kode_spt
        ]);
    }

    private function syncTrackingRecords()
    {
        // Get only approved SPTs that should have tracking records
        $spts = SPT::whereNotNull('kode_spt')
            ->whereNotNull('kode_petak')
            ->where('status', 'Disetujui')
            ->get();

        foreach ($spts as $spt) {
            // Check if tracking record exists
            $tracking = TrackingActivity::where('kode_spt', $spt->kode_spt)
                ->where('kode_petak', $spt->kode_petak)
                ->first();

            if (!$tracking) {
                // Create new tracking record
                TrackingActivity::createFromSPT($spt);
            } else {
                // For existing records, we don't need to update status based on SPT status
                // Status will be managed independently by admin/mandor
            }
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $tracking = TrackingActivity::with(['spt' => function($q) {
                $q->withCount('lkt');
            }])->findOrFail($id);

            // Check if user is admin or mandor
            if (!in_array(auth()->user()->role_name, ['admin', 'mandor'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                    'error' => 'Anda tidak memiliki izin untuk memperbarui status.'
                ], 403);
            }

            // Validate status
            $validated = $request->validate([
                'status' => 'required|in:not_started,in_progress,completed'
            ]);

            // Map status to display format
            $statusMap = [
                'not_started' => 'Not Started',
                'in_progress' => 'In Progress',
                'completed' => 'completed'
            ];
            $displayStatus = $statusMap[$validated['status']] ?? $validated['status'];

            // Prevent changing from Not Started when there are no LKTs
            if ($tracking->status_tracking === 'not_started' && $tracking->spt->lkt_count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah status',
                    'error' => 'Status tidak dapat diubah karena jumlah LKT masih 0.'
                ], 422);
            }
            
            // Prevent changing to Not Started when there are LKTs
            if ($validated['status'] === 'not_started' && $tracking->spt->lkt_count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah status',
                    'error' => 'Tidak dapat mengubah ke status Not Started karena sudah ada LKT yang dibuat.'
                ], 422);
            }
            
            // Prevent marking as completed if there are incomplete LKTs
            if ($validated['status'] === 'completed') {
                $incompleteLkts = $tracking->spt->lkt()->where('status', '!=', 'selesai')->count();
                if ($incompleteLkts > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak dapat menyelesaikan',
                        'error' => 'Tidak dapat menyelesaikan karena masih ada ' . $incompleteLkts . ' LKT yang belum selesai.'
                    ], 422);
                }
            }

            // Update status
            $tracking->status_tracking = $validated['status'];
            $tracking->updated_by = auth()->user()->name;
            
            if ($tracking->save()) {
                // Refresh the model to get updated data
                $tracking->refresh();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Status berhasil diperbarui',
                    'status' => $tracking->status_tracking,
                    'updated_at' => $tracking->updated_at->format('d/m/Y H:i')
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status',
                'error' => 'Terjadi kesalahan saat menyimpan perubahan.'
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
