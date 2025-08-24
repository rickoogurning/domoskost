<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kamar;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\OrderLaundry;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function adminDashboard(): JsonResponse
    {
        // Basic stats
        $totalPenghuni = Penghuni::active()->count();
        $totalKamar = Kamar::count();
        $kamarTerisi = Kamar::where('status_kamar', 'Terisi')->count();
        $kamarKosong = Kamar::where('status_kamar', 'Tersedia')->count();

        // Revenue current month
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $pendapatanBulanIni = Pembayaran::whereHas('tagihan', function ($query) use ($currentMonth, $currentYear) {
            $query->where('periode_bulan', $currentMonth)
                  ->where('periode_tahun', $currentYear);
        })
        ->where('status_verifikasi', 'Terverifikasi')
        ->sum('jumlah_bayar');

        // Revenue last month
        $lastMonth = Carbon::now()->subMonth();
        $pendapatanBulanLalu = Pembayaran::whereHas('tagihan', function ($query) use ($lastMonth) {
            $query->where('periode_bulan', $lastMonth->month)
                  ->where('periode_tahun', $lastMonth->year);
        })
        ->where('status_verifikasi', 'Terverifikasi')
        ->sum('jumlah_bayar');

        // Unpaid bills
        $tagihanBelumBayar = Tagihan::unpaid()->count();

        // Active laundry orders
        $laundryAktif = OrderLaundry::active()->count();

        // Revenue trend (last 6 months)
        $revenueTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->format('M');
            
            $sewaRevenue = Pembayaran::whereHas('tagihan.detailTagihan', function ($query) {
                $query->where('jenis_tagihan', 'Sewa Kamar');
            })
            ->whereHas('tagihan', function ($query) use ($date) {
                $query->where('periode_bulan', $date->month)
                      ->where('periode_tahun', $date->year);
            })
            ->where('status_verifikasi', 'Terverifikasi')
            ->sum('jumlah_bayar');

            $laundryRevenue = OrderLaundry::whereMonth('tanggal_terima', $date->month)
                                        ->whereYear('tanggal_terima', $date->year)
                                        ->where('status_bayar', 'Sudah Dibayar')
                                        ->sum('total_biaya');

            $revenueTrend[] = [
                'month' => $month,
                'sewa' => (float) $sewaRevenue,
                'laundry' => (float) $laundryRevenue,
            ];
        }

        // Room occupancy data
        $occupancyData = [
            ['name' => 'Terisi', 'value' => $kamarTerisi, 'color' => '#10b981'],
            ['name' => 'Kosong', 'value' => $kamarKosong, 'color' => '#f59e0b'],
            ['name' => 'Maintenance', 'value' => Kamar::where('status_kamar', 'Maintenance')->count(), 'color' => '#ef4444'],
        ];

        // Laundry status today
        $laundryStatusToday = OrderLaundry::today()
            ->select('status_order', DB::raw('count(*) as count'))
            ->groupBy('status_order')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status_order,
                    'count' => $item->count,
                ];
            });

        // Recent activities
        $recentActivities = collect();

        // Recent payments
        $recentPayments = Pembayaran::with(['tagihan.penghuni.user'])
            ->where('status_verifikasi', 'Terverifikasi')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'type' => 'payment',
                    'user' => $payment->tagihan->penghuni->user->nama_lengkap,
                    'action' => 'melakukan pembayaran sewa',
                    'amount' => $payment->jumlah_bayar,
                    'time' => $payment->created_at->diffForHumans(),
                    'created_at' => $payment->created_at,
                ];
            });

        // Recent laundry orders
        $recentLaundry = OrderLaundry::with(['penghuni.user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'type' => 'laundry',
                    'user' => $order->penghuni->user->nama_lengkap,
                    'action' => $order->status_order === 'Siap Diambil' ? 
                        "mengambil laundry #{$order->kode_order}" : 
                        "membuat order laundry #{$order->kode_order}",
                    'time' => $order->updated_at->diffForHumans(),
                    'created_at' => $order->updated_at,
                ];
            });

        $recentActivities = $recentPayments->concat($recentLaundry)
                                         ->sortByDesc('created_at')
                                         ->take(10)
                                         ->values();

        return $this->successResponse([
            'stats' => [
                'totalPenghuni' => $totalPenghuni,
                'totalKamar' => $totalKamar,
                'kamarTerisi' => $kamarTerisi,
                'kamarKosong' => $kamarKosong,
                'pendapatanBulanIni' => (float) $pendapatanBulanIni,
                'pendapatanBulanLalu' => (float) $pendapatanBulanLalu,
                'tagihanBelumBayar' => $tagihanBelumBayar,
                'laundryAktif' => $laundryAktif,
            ],
            'charts' => [
                'revenueTrend' => $revenueTrend,
                'occupancyData' => $occupancyData,
                'laundryStatusToday' => $laundryStatusToday,
            ],
            'recentActivities' => $recentActivities,
        ], 'Dashboard data retrieved successfully');
    }

    /**
     * Get penghuni dashboard data
     */
    public function penghuniDashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $penghuni = $user->penghuni;

        if (!$penghuni) {
            return $this->errorResponse('Data penghuni tidak ditemukan', 404);
        }

        // Current bill
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $tagihanBulanIni = Tagihan::where('penghuni_id', $penghuni->id)
            ->byPeriod($currentMonth, $currentYear)
            ->with(['detailTagihan', 'pembayaran'])
            ->first();

        // Payment history (last 6 months)
        $paymentHistory = Tagihan::where('penghuni_id', $penghuni->id)
            ->where('status_tagihan', 'Lunas')
            ->with(['pembayaran' => function ($query) {
                $query->where('status_verifikasi', 'Terverifikasi')->latest()->take(1);
            }])
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->take(6)
            ->get()
            ->map(function ($tagihan) {
                return [
                    'id' => $tagihan->id,
                    'periode' => $tagihan->periode_string,
                    'total' => $tagihan->total_tagihan,
                    'status' => $tagihan->status_tagihan,
                    'tanggal_bayar' => $tagihan->pembayaran->first()?->tanggal_bayar,
                ];
            });

        // Active laundry orders
        $activeLaundry = OrderLaundry::where('penghuni_id', $penghuni->id)
            ->active()
            ->with(['jenisLayanan'])
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->kode_order,
                    'tanggal_order' => $order->tanggal_terima,
                    'berat' => $order->berat_kg,
                    'jenis' => $order->jenisLayanan->nama_layanan,
                    'status' => $order->status_order,
                    'estimasi_selesai' => $order->tanggal_estimasi_selesai,
                    'biaya' => $order->total_biaya,
                    'progress' => $order->progress_percentage,
                ];
            });

        // Laundry history (last 10 orders)
        $laundryHistory = OrderLaundry::where('penghuni_id', $penghuni->id)
            ->where('status_order', 'Selesai')
            ->with(['jenisLayanan'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->kode_order,
                    'tanggal_order' => $order->tanggal_terima,
                    'tanggal_selesai' => $order->tanggal_selesai,
                    'berat' => $order->berat_kg,
                    'jenis' => $order->jenisLayanan->nama_layanan,
                    'biaya' => $order->total_biaya,
                ];
            });

        return $this->successResponse([
            'kamar' => [
                'kode' => $penghuni->kamar->kode_kamar,
                'lantai' => $penghuni->kamar->lantai,
                'tipe' => $penghuni->kamar->tipe_kamar,
                'tarif' => $penghuni->kamar->tarif_bulanan,
                'tanggal_masuk' => $penghuni->tanggal_masuk,
            ],
            'tagihan' => [
                'bulan_ini' => $tagihanBulanIni ? [
                    'id' => $tagihanBulanIni->id,
                    'periode' => $tagihanBulanIni->periode_string,
                    'total' => $tagihanBulanIni->total_tagihan,
                    'status' => $tagihanBulanIni->status_tagihan,
                    'jatuh_tempo' => $tagihanBulanIni->tanggal_jatuh_tempo,
                    'denda' => $tagihanBulanIni->denda,
                ] : null,
                'history' => $paymentHistory,
            ],
            'laundry' => [
                'active' => $activeLaundry,
                'history' => $laundryHistory,
            ],
        ], 'Dashboard data retrieved successfully');
    }

    /**
     * Get laundry staff dashboard data
     */
    public function laundryDashboard(): JsonResponse
    {
        $today = now()->toDateString();

        // Statistics
        $stats = [
            'orderHariIni' => OrderLaundry::today()->count(),
            'sedangDiproses' => OrderLaundry::active()->count(),
            'siapDiambil' => OrderLaundry::where('status_order', 'Siap Diambil')->count(),
            'selesaiHariIni' => OrderLaundry::where('status_order', 'Selesai')
                                          ->whereDate('tanggal_selesai', $today)
                                          ->count(),
            'pendapatanHariIni' => OrderLaundry::whereDate('tanggal_terima', $today)
                                             ->where('status_bayar', 'Sudah Dibayar')
                                             ->sum('total_biaya'),
        ];

        // Active orders
        $activeOrders = OrderLaundry::active()
            ->with(['penghuni.user', 'penghuni.kamar', 'jenisLayanan', 'petugasTerima'])
            ->orderBy('tanggal_terima')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->kode_order,
                    'penghuni' => $order->penghuni->user->nama_lengkap,
                    'kamar' => $order->penghuni->kamar->kode_kamar,
                    'tanggal_terima' => $order->tanggal_terima,
                    'berat' => $order->berat_kg,
                    'jenis' => $order->jenisLayanan->nama_layanan,
                    'status' => $order->status_order,
                    'estimasi_selesai' => $order->tanggal_estimasi_selesai,
                    'biaya' => $order->total_biaya,
                    'is_overdue' => $order->is_overdue,
                    'next_status' => $order->getNextStatus(),
                ];
            });

        return $this->successResponse([
            'stats' => $stats,
            'activeOrders' => $activeOrders,
        ], 'Laundry dashboard data retrieved successfully');
    }
}
