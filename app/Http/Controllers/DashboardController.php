<?php

namespace App\Http\Controllers;

use App\Models\Foreman;
use App\Models\SPT;
use App\Models\Vendor;
use App\Models\Vehicle;
use App\Models\HarvestSubBlock;
use App\Models\HasilTebang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get data from database
            $data = [
                'totalMandor' => $this->getActiveMandorCount(),
                'totalVendor' => $this->getActiveVendorCount(),
                'belumDitebang' => $this->getBelumDitebangCount(),
                'sudahDitebang' => $this->getSudahDitebangCount(),
                'sedangDitebang' => $this->getSedangDitebangCount(),
                'totalSpt' => $this->getTotalSptCount(),
                'sudahDiangkut' => $this->getSudahDiangkutCount(),
                'lktDiproses' => $this->getLktDiprosesCount(),
                'todoApproval' => $this->getTodoApprovalCount(),
                'luasAreaTebangan' => $this->getLuasAreaTebangan(),
                'totalKendaraanVendor' => $this->getTotalKendaraanVendor(),
            'totalBapp' => $this->getTotalBappCount(),
            'hasilTebangPTPAG' => $this->getHasilTebangForPTPAG(),
            ];

            // Log the data being sent to the view
            Log::info('Dashboard data:', $data);

            // Return the view with data
            return view('dashboard', $data);

        } catch (\Exception $e) {
            // Log the error and return a safe response
            Log::error('Dashboard error: ' . $e->getMessage());

            // Return view with zero values in case of error
            return view('dashboard', [
                'totalMandor' => 0,
                'totalVendor' => 0,
                'belumDitebang' => 0,
                'sudahDitebang' => 0,
                'sedangDitebang' => 0,
                'totalSpt' => 0,
                'sudahDiangkut' => 0,
                'lktDiproses' => 0,
                'todoApproval' => 0,
                'totalKendaraanVendor' => 0,
                'luasAreaTebangan' => [
                    'sudah_ditebang' => 0,
                    'belum_ditebang' => 0,
                    'total_luas' => 0,
                    'persen_sudah' => 0,
                    'persen_belum' => 0
                ],
                'error' => 'Error loading dashboard data'
            ]);
        }
    }

    private function getActiveMandorCount()
    {
        return Foreman::where('status', 'Aktif')->count();
    }

    private function getActiveVendorCount()
    {
        return Vendor::where('status', 'Aktif')->count();
    }

    private function getBelumDitebangCount()
    {
        return HarvestSubBlock::whereDoesntHave('trackingActivity')
            ->orWhereHas('trackingActivity', function($query) {
                $query->where('status_tracking', 'not_started');
            })
            ->count();
    }

    private function getSudahDitebangCount()
    {
        return HarvestSubBlock::whereHas('trackingActivity', function($query) {
            $query->where('status_tracking', 'completed');
        })->count();
    }

    private function getSedangDitebangCount()
    {
        return HarvestSubBlock::whereHas('trackingActivity', function($query) {
            $query->where('status_tracking', 'in_progress');
        })->count();
    }

    private function getTotalSptCount()
    {
        // For Assistant Manager Plantation, show only pending SPT count
        if (auth()->user()->role_name === 'Assistant Manager Plantation') {
            return SPT::whereIn('approval_stage', [
                SPT::STAGE_PEMBUAT,
                SPT::STAGE_PEMERIKSA,
                SPT::STAGE_PENYETUJU
            ])->count();
        }
        return SPT::count();
    }
    
    private function getPendingSptCount()
    {
        return SPT::whereIn('approval_stage', [
            SPT::STAGE_PEMBUAT,
            SPT::STAGE_PEMERIKSA,
            SPT::STAGE_PENYETUJU
        ])->count();
    }

    private function getSudahDiangkutCount()
    {
        return SPT::where('status', 'Sudah Diangkut')->count();
    }

    private function getLktDiprosesCount()
    {
        $user = auth()->user();
        $query = \App\Models\LKT::query();

        // For Assistant Manager Plantation, show LKTs that are not Draft and have been signed by Pemeriksa 1
        if ($user->role_name === 'Assistant Manager Plantation') {
            return $query->where('status', '!=', 'Draft')
                ->whereNotNull('ttd_diperiksa_oleh_path')
                ->where('approval_stage', '>=', \App\Models\LKT::STAGE_P2)
                ->where('status', '!=', 'Ditolak')
                ->count();
        }
        
        // For PT PAG, show only Disetujui and Selesai LKTs
        if ($user->role_name === 'PT PAG') {
            return $query->whereIn('status', [
                    \App\Models\LKT::STATUS_DISETUJUI,
                ])
                ->count();
        }
        
        // For other users, show all LKTs that are not completed or rejected
        return $query->whereNotIn('status', ['Selesai', 'Ditolak'])->count();
    }

    private function getTodoApprovalCount()
    {
        return SPT::where('status', 'Menunggu Persetujuan')->count();
    }

    private function getTotalBappCount()
    {
        // Hitung semua BAPP Tebang
        $bappTebangCount = \App\Models\BappTebang::count();
            
        // Hitung semua BAPP Angkut
        $bappAngkutCount = \App\Models\BappAngkut::count();
            
        return $bappTebangCount + $bappAngkutCount;
    }

    private function getLuasAreaTebangan()
    {
        // Total luas area yang belum ditebang
        $belumDitebang = HarvestSubBlock::whereDoesntHave('trackingActivity')
            ->orWhereHas('trackingActivity', function($query) {
                $query->where('status_tracking', 'not_started');
            })
            ->sum('luas_area');

        // Total luas area yang sudah ditebang
        $sudahDitebang = HarvestSubBlock::whereHas('trackingActivity', function($query) {
                $query->where('status_tracking', 'completed');
            })
            ->sum('luas_area');

        // Total keseluruhan luas area
        $totalLuas = $belumDitebang + $sudahDitebang;
        
        // Hitung persentase
        $persenSudahDitebang = $totalLuas > 0 ? round(($sudahDitebang / $totalLuas) * 100, 1) : 0;
        $persenBelumDitebang = $totalLuas > 0 ? round(($belumDitebang / $totalLuas) * 100, 1) : 0;

        return [
            'sudah_ditebang' => $sudahDitebang,
            'belum_ditebang' => $belumDitebang,
            'total_luas' => $totalLuas,
            'persen_sudah' => $persenSudahDitebang,
            'persen_belum' => $persenBelumDitebang
        ];
    }

    protected function getTotalKendaraanVendor()
    {
        try {
            return \App\Models\Vehicle::count();
        } catch (\Exception $e) {
            Log::error('Error getting total kendaraan vendor: ' . $e->getMessage());
            return 0;
        }
    }
    
    private function getHasilTebangForPTPAG()
    {
        try {
            // Debug: Check if HarvestSubBlock model exists and has data
            $harvestSubBlockCount = \App\Models\HarvestSubBlock::count();
            Log::info('Total HarvestSubBlock records: ' . $harvestSubBlockCount);
            
            // Get total HA from luas_area where status is completed
            $totalHa = \App\Models\HarvestSubBlock::whereHas('trackingActivity', function($query) {
                    $query->where('status', 'completed');
                })
                ->sum('luas_area');

            Log::info('Total HA calculated: ' . $totalHa);

            // Debug: Check if HasilTebang model exists and has data
            $hasilTebangCount = \App\Models\HasilTebang::count();
            Log::info('Total HasilTebang records: ' . $hasilTebangCount);

            // Get total TON from netto2 in hasil_tebang table
            $totalTon = \App\Models\HasilTebang::sum('netto2');
            Log::info('Total TON calculated: ' . $totalTon);

            // Count total RIT from SPT with status 'Sudah Diangkut' that has BAPP Tebang
            $totalRit = \App\Models\SPT::where('status', 'Sudah Diangkut')
                ->whereHas('bappTebang')
                ->count();
            
            Log::info('Total RIT calculated: ' . $totalRit);

            return [
                'total_ha' => $totalHa,
                'total_rit' => $totalRit,
                'total_ton' => $totalTon
            ];
        } catch (\Exception $e) {
            Log::error('Error getting hasil tebang for PT PAG: ' . $e->getMessage());
            return [
                'total_ha' => 0,
                'total_rit' => 0,
                'total_ton' => 0
            ];
        }
    }
}
