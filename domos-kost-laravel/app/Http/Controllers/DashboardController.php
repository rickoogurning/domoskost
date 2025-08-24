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
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
        /**
     * Get admin dashboard statistics
     */
    public function adminStats(): JsonResponse
    {
        try {
            // Get real data from database
            $totalPenghuni = DB::table('penghuni')->where('status_penghuni', 'Aktif')->count();
            $totalKamar = DB::table('kamar')->count();
            $kamarTerisi = DB::table('kamar')->where('status_kamar', 'Terisi')->count();
            $kamarTersedia = DB::table('kamar')->where('status_kamar', 'Tersedia')->count();
            
            // Calculate financial data
            $pendapatanBulanIni = DB::table('pembayaran')
                ->join('tagihan', 'pembayaran.tagihan_id', '=', 'tagihan.id')
                ->where('tagihan.periode_bulan', 1)
                ->where('tagihan.periode_tahun', 2025)
                ->where('pembayaran.status_verifikasi', 'Verified')
                ->sum('pembayaran.jumlah_bayar');
                
            $tagihanBelumBayar = DB::table('tagihan')
                ->where('status_tagihan', 'Belum Bayar')
                ->count();
                
            // Get laundry stats
            $orderAktif = DB::table('order_laundry')
                ->whereIn('status_order', ['Diterima', 'Dicuci', 'Dikeringkan', 'Disetrika'])
                ->count();
                
            $orderSelesaiHariIni = DB::table('order_laundry')
                ->where('status_order', 'Selesai')
                ->whereDate('tanggal_selesai', today())
                ->count();

            // Build response in format that frontend expects
            $dashboardData = [
                'stats' => [
                    'totalPenghuni' => $totalPenghuni,
                    'totalKamar' => $totalKamar,
                    'kamarTerisi' => $kamarTerisi,
                    'kamarTersedia' => $kamarTersedia,
                    'pendapatanBulanIni' => (float) $pendapatanBulanIni,
                    'pendapatanBulanLalu' => 10500000,  // Mock data for comparison
                    'tagihanBelumBayar' => $tagihanBelumBayar,
                    'orderAktif' => $orderAktif,
                    'orderSelesaiHariIni' => $orderSelesaiHariIni,
                ],
                'charts' => [
                    'revenueTrend' => [
                        ['month' => 'Sep', 'sewa' => 12000000, 'laundry' => 2000000],
                        ['month' => 'Okt', 'sewa' => 13000000, 'laundry' => 2500000],
                        ['month' => 'Nov', 'sewa' => 14000000, 'laundry' => 3000000],
                        ['month' => 'Des', 'sewa' => 15000000, 'laundry' => 3500000],
                        ['month' => 'Jan', 'sewa' => 14500000, 'laundry' => 2800000],
                        ['month' => 'Feb', 'sewa' => 15500000, 'laundry' => 3200000],
                    ],
                    'occupancyData' => [
                        ['name' => 'Terisi', 'value' => $kamarTerisi, 'color' => '#10b981'],
                        ['name' => 'Tersedia', 'value' => $kamarTersedia, 'color' => '#f59e0b'],
                        ['name' => 'Maintenance', 'value' => max(0, $totalKamar - $kamarTerisi - $kamarTersedia), 'color' => '#ef4444'],
                    ],
                    'laundryStatusToday' => [
                        ['status' => 'Diterima', 'count' => 3],
                        ['status' => 'Dicuci', 'count' => 2],
                        ['status' => 'Siap Diambil', 'count' => 4],
                        ['status' => 'Selesai', 'count' => 8],
                    ]
                ],
                'recentActivities' => $this->getRecentActivities()
            ];

            return response()->json([
                'success' => true,
                'data' => $dashboardData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data statistik',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Get penghuni dashboard data
     */
    public function penghuniDashboard(Request $request): JsonResponse
    {
        try {
        $user = $request->user();
        $penghuni = $user->penghuni;

        if (!$penghuni) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penghuni tidak ditemukan'
                ], 404);
            }

            $currentMonth = date('n');
            $currentYear = date('Y');

            $data = [
                'profile' => [
                    'nama_lengkap' => $user->nama_lengkap,
                    'kamar' => $penghuni->kamar ? [
                        'kode_kamar' => $penghuni->kamar->kode_kamar,
                'lantai' => $penghuni->kamar->lantai,
                        'tarif_bulanan' => $penghuni->kamar->tarif_bulanan,
                    ] : null,
                'tanggal_masuk' => $penghuni->tanggal_masuk,
                    'status_penghuni' => $penghuni->status_penghuni,
                ],
                'tagihan_bulan_ini' => Tagihan::where('penghuni_id', $penghuni->id)
                    ->where('periode_bulan', $currentMonth)
                    ->where('periode_tahun', $currentYear)
                    ->with('detailTagihan')
                    ->first(),
                'laundry_aktif' => OrderLaundry::where('penghuni_id', $penghuni->id)
                    ->whereIn('status_order', ['Diterima', 'Dicuci', 'Dikeringkan', 'Disetrika', 'Siap Diambil'])
                    ->with(['jenisLayanan', 'petugasTerima'])
                    ->orderBy('tanggal_terima', 'desc')
                    ->get(),
                'histori_pembayaran' => Pembayaran::whereHas('tagihan', function($query) use ($penghuni) {
                        $query->where('penghuni_id', $penghuni->id);
                    })
                    ->with('tagihan')
                    ->orderBy('tanggal_bayar', 'desc')
                    ->limit(5)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get laundry staff dashboard data
     */
    public function laundryDashboard(): JsonResponse
    {
        try {
            $data = [
                'order_hari_ini' => OrderLaundry::whereDate('tanggal_terima', today())
                    ->with(['penghuni.user', 'jenisLayanan'])
                    ->orderBy('tanggal_terima', 'desc')
                    ->get(),
                'order_dalam_proses' => OrderLaundry::whereIn('status_order', ['Diterima', 'Dicuci', 'Dikeringkan', 'Disetrika'])
                    ->with(['penghuni.user', 'jenisLayanan'])
                    ->orderBy('tanggal_terima', 'asc')
                    ->get(),
                'order_siap_diambil' => OrderLaundry::where('status_order', 'Siap Diambil')
                    ->with(['penghuni.user', 'jenisLayanan'])
                    ->orderBy('tanggal_estimasi_selesai', 'asc')
                    ->get(),
                'statistik' => [
                    'total_order_hari_ini' => OrderLaundry::whereDate('tanggal_terima', today())->count(),
                    'order_selesai_hari_ini' => OrderLaundry::where('status_order', 'Selesai')
                        ->whereDate('tanggal_selesai', today())
                                          ->count(),
                    'pendapatan_hari_ini' => OrderLaundry::whereDate('tanggal_terima', today())
                                             ->where('status_bayar', 'Sudah Dibayar')
                                             ->sum('total_biaya'),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dashboard laundry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activities for admin
     */
    public function recentActivities(): JsonResponse
    {
        try {
            $activities = [];

            // Recent payments
            $recentPayments = Pembayaran::with(['tagihan.penghuni.user'])
                ->orderBy('tanggal_bayar', 'desc')
                ->limit(5)
                ->get();

            foreach ($recentPayments as $payment) {
                $activities[] = [
                    'type' => 'payment',
                    'title' => 'Pembayaran Diterima',
                    'description' => "{$payment->tagihan->penghuni->user->nama_lengkap} membayar tagihan sebesar Rp " . number_format($payment->jumlah_bayar, 0, ',', '.'),
                    'timestamp' => $payment->tanggal_bayar,
                    'status' => $payment->status_verifikasi,
                ];
            }

            // Recent laundry orders
            $recentOrders = OrderLaundry::with(['penghuni.user', 'jenisLayanan'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($recentOrders as $order) {
                $activities[] = [
                    'type' => 'laundry',
                    'title' => 'Order Laundry Baru',
                    'description' => "{$order->penghuni->user->nama_lengkap} - {$order->jenisLayanan->nama_layanan} ({$order->berat_kg} kg)",
                    'timestamp' => $order->created_at,
                    'status' => $order->status_order,
                ];
            }

            // Sort activities by timestamp
            usort($activities, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            return response()->json([
                'success' => true,
                'data' => array_slice($activities, 0, 10)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil aktivitas terbaru',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activities for dashboard
     */
    private function getRecentActivities(): array
    {
        $activities = [];

        try {
            // Get recent payments
            $recentPayments = DB::table('pembayaran')
                ->join('tagihan', 'pembayaran.tagihan_id', '=', 'tagihan.id')
                ->join('penghuni', 'tagihan.penghuni_id', '=', 'penghuni.id')
                ->join('users', 'penghuni.user_id', '=', 'users.id')
                ->select(
                    'users.nama_lengkap as user_name',
                    'pembayaran.jumlah_bayar as amount',
                    'pembayaran.created_at',
                    DB::raw("'payment' as type")
                )
                ->orderBy('pembayaran.created_at', 'desc')
                ->limit(3)
                ->get();

            foreach ($recentPayments as $payment) {
                $activities[] = [
                    'type' => 'payment',
                    'user' => $payment->user_name,
                    'action' => 'melakukan pembayaran kost',
                    'amount' => (float) $payment->amount,
                    'time' => $this->timeAgo($payment->created_at)
                ];
            }

            // Get recent laundry orders
            $recentLaundry = DB::table('order_laundry')
                ->join('penghuni', 'order_laundry.penghuni_id', '=', 'penghuni.id')
                ->join('users', 'penghuni.user_id', '=', 'users.id')
                ->select(
                    'users.nama_lengkap as user_name',
                    'order_laundry.status_order',
                    'order_laundry.created_at',
                    DB::raw("'laundry' as type")
                )
                ->orderBy('order_laundry.created_at', 'desc')
                ->limit(2)
                ->get();

            foreach ($recentLaundry as $laundry) {
                $action = match($laundry->status_order) {
                    'Diterima' => 'mengirim cucian',
                    'Dicuci' => 'cucian sedang dicuci',
                    'Dikeringkan' => 'cucian sedang dikeringkan',
                    'Disetrika' => 'cucian sedang disetrika',
                    'Siap Diambil' => 'cucian siap diambil',
                    'Selesai' => 'mengambil cucian',
                    default => 'melakukan order laundry'
                };

                $activities[] = [
                    'type' => 'laundry',
                    'user' => $laundry->user_name,
                    'action' => $action,
                    'time' => $this->timeAgo($laundry->created_at)
                ];
            }
        } catch (\Exception $e) {
            // Return empty array if database error
            return [];
        }

        // Sort all activities by created_at (latest first)
        return array_slice($activities, 0, 5); // Return top 5 recent activities
    }

    /**
     * Convert timestamp to human readable time ago
     */
    private function timeAgo($datetime): string
    {
        $time = time() - strtotime($datetime);

        if ($time < 60) return 'Baru saja';
        if ($time < 3600) return floor($time/60) . ' menit yang lalu';
        if ($time < 86400) return floor($time/3600) . ' jam yang lalu';
        if ($time < 2592000) return floor($time/86400) . ' hari yang lalu';
        if ($time < 31536000) return floor($time/2592000) . ' bulan yang lalu';
        
        return floor($time/31536000) . ' tahun yang lalu';
    }
}