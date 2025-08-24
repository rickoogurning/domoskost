<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\OrderLaundry;
use App\Models\Penghuni;
use App\Models\Kamar;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    /**
     * Get revenue report
     */
    public function pendapatan(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
            'format' => 'in:monthly,weekly,daily',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $periodeAwal = Carbon::parse($request->periode_mulai);
        $periodeAkhir = Carbon::parse($request->periode_selesai);
        $format = $request->get('format', 'monthly');

        try {
            // Revenue from room rent
            $pendapatanSewa = Pembayaran::whereHas('tagihan.detailTagihan', function ($query) {
                $query->where('jenis_tagihan', 'Sewa Kamar');
            })
            ->whereHas('tagihan', function ($query) use ($periodeAwal, $periodeAkhir) {
                $query->whereBetween('periode_tahun', [$periodeAwal->year, $periodeAkhir->year]);
                if ($periodeAwal->year === $periodeAkhir->year) {
                    $query->whereBetween('periode_bulan', [$periodeAwal->month, $periodeAkhir->month]);
                }
            })
            ->where('status_verifikasi', 'Terverifikasi')
            ->sum('jumlah_bayar');

            // Revenue from laundry
            $pendapatanLaundry = OrderLaundry::whereBetween('tanggal_terima', [$periodeAwal, $periodeAkhir])
                                           ->where('status_bayar', 'Sudah Dibayar')
                                           ->sum('total_biaya');

            // Revenue from penalties
            $pendapatanDenda = Pembayaran::whereHas('tagihan.detailTagihan', function ($query) {
                $query->where('jenis_tagihan', 'Denda');
            })
            ->whereHas('tagihan', function ($query) use ($periodeAwal, $periodeAkhir) {
                $query->whereBetween('tanggal_terbit', [$periodeAwal, $periodeAkhir]);
            })
            ->where('status_verifikasi', 'Terverifikasi')
            ->sum('jumlah_bayar');

            $totalPendapatan = $pendapatanSewa + $pendapatanLaundry + $pendapatanDenda;

            // Detailed breakdown by period
            $breakdown = [];
            
            if ($format === 'monthly') {
                $period = $periodeAwal->copy();
                while ($period->lte($periodeAkhir)) {
                    $bulan = $period->month;
                    $tahun = $period->year;
                    
                    $sewaBulan = Pembayaran::whereHas('tagihan', function ($query) use ($bulan, $tahun) {
                        $query->where('periode_bulan', $bulan)
                              ->where('periode_tahun', $tahun);
                    })
                    ->whereHas('tagihan.detailTagihan', function ($query) {
                        $query->where('jenis_tagihan', 'Sewa Kamar');
                    })
                    ->where('status_verifikasi', 'Terverifikasi')
                    ->sum('jumlah_bayar');

                    $laundryBulan = OrderLaundry::whereMonth('tanggal_terima', $bulan)
                                               ->whereYear('tanggal_terima', $tahun)
                                               ->where('status_bayar', 'Sudah Dibayar')
                                               ->sum('total_biaya');

                    $dendaBulan = Pembayaran::whereHas('tagihan', function ($query) use ($bulan, $tahun) {
                        $query->where('periode_bulan', $bulan)
                              ->where('periode_tahun', $tahun);
                    })
                    ->whereHas('tagihan.detailTagihan', function ($query) {
                        $query->where('jenis_tagihan', 'Denda');
                    })
                    ->where('status_verifikasi', 'Terverifikasi')
                    ->sum('jumlah_bayar');

                    $breakdown[] = [
                        'periode' => $period->format('Y-m'),
                        'periode_display' => $period->format('F Y'),
                        'sewa' => (float) $sewaBulan,
                        'laundry' => (float) $laundryBulan,
                        'denda' => (float) $dendaBulan,
                        'total' => (float) ($sewaBulan + $laundryBulan + $dendaBulan),
                    ];

                    $period->addMonth();
                }
            }

            // Outstanding receivables
            $piutangSewa = Tagihan::whereNotIn('status_tagihan', ['Lunas'])
                                 ->sum('total_tagihan') - 
                          Tagihan::whereNotIn('status_tagihan', ['Lunas'])
                                 ->withSum('pembayaran', 'jumlah_bayar')
                                 ->get()
                                 ->sum('pembayaran_sum_jumlah_bayar');

            $piutangLaundry = OrderLaundry::where('status_order', 'Selesai')
                                        ->where('status_bayar', 'Belum Dibayar')
                                        ->sum('total_biaya');

            // Payment method breakdown
            $metodeBreakdown = Pembayaran::whereBetween('tanggal_bayar', [$periodeAwal, $periodeAkhir])
                                       ->where('status_verifikasi', 'Terverifikasi')
                                       ->select('metode_bayar', DB::raw('SUM(jumlah_bayar) as total'))
                                       ->groupBy('metode_bayar')
                                       ->get()
                                       ->mapWithKeys(function ($item) {
                                           return [$item->metode_bayar => (float) $item->total];
                                       });

            // Top paying tenants
            $topPenghuni = Pembayaran::with(['tagihan.penghuni.user'])
                                   ->whereBetween('tanggal_bayar', [$periodeAwal, $periodeAkhir])
                                   ->where('status_verifikasi', 'Terverifikasi')
                                   ->select('tagihan_id', DB::raw('SUM(jumlah_bayar) as total_bayar'))
                                   ->groupBy('tagihan_id')
                                   ->orderByDesc('total_bayar')
                                   ->limit(10)
                                   ->get()
                                   ->map(function ($item) {
                                       return [
                                           'penghuni' => $item->tagihan->penghuni->user->nama_lengkap,
                                           'kamar' => $item->tagihan->penghuni->kamar->kode_kamar ?? '-',
                                           'total_bayar' => (float) $item->total_bayar,
                                       ];
                                   });

            return $this->successResponse([
                'periode' => [
                    'mulai' => $periodeAwal->toDateString(),
                    'selesai' => $periodeAkhir->toDateString(),
                    'format' => $format,
                ],
                'ringkasan' => [
                    'total_pendapatan' => (float) $totalPendapatan,
                    'pendapatan_sewa' => (float) $pendapatanSewa,
                    'pendapatan_laundry' => (float) $pendapatanLaundry,
                    'pendapatan_denda' => (float) $pendapatanDenda,
                    'persentase_sewa' => $totalPendapatan > 0 ? round(($pendapatanSewa / $totalPendapatan) * 100, 2) : 0,
                    'persentase_laundry' => $totalPendapatan > 0 ? round(($pendapatanLaundry / $totalPendapatan) * 100, 2) : 0,
                    'persentase_denda' => $totalPendapatan > 0 ? round(($pendapatanDenda / $totalPendapatan) * 100, 2) : 0,
                ],
                'piutang' => [
                    'piutang_sewa' => (float) $piutangSewa,
                    'piutang_laundry' => (float) $piutangLaundry,
                    'total_piutang' => (float) ($piutangSewa + $piutangLaundry),
                ],
                'breakdown_periode' => $breakdown,
                'breakdown_metode' => $metodeBreakdown,
                'top_penghuni' => $topPenghuni,
                'generated_at' => now()->toISOString(),
            ], 'Laporan pendapatan berhasil diambil');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal mengambil laporan pendapatan: ' . $e->getMessage());
        }
    }

    /**
     * Export revenue report to PDF
     */
    public function exportPendapatan(Request $request)
    {
        // Reuse the same logic as pendapatan method
        $laporanResponse = $this->pendapatan($request);
        
        if (!$laporanResponse->getData()->success) {
            return $laporanResponse;
        }

        $data = $laporanResponse->getData()->data;

        try {
            $pdf = PDF::loadView('laporan.pendapatan', compact('data'));
            
            $filename = 'Laporan_Pendapatan_' . 
                       Carbon::parse($data->periode->mulai)->format('Y_m') . '_' .
                       Carbon::parse($data->periode->selesai)->format('Y_m') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal mengexport laporan: ' . $e->getMessage());
        }
    }

    /**
     * Get tenant report
     */
    public function penghuni(Request $request): JsonResponse
    {
        try {
            // Total statistics
            $totalPenghuni = Penghuni::count();
            $penghuniAktif = Penghuni::where('status_penghuni', 'Aktif')->count();
            $penghuniNonAktif = Penghuni::where('status_penghuni', 'Non-Aktif')->count();

            // Occupancy rate
            $totalKamar = Kamar::count();
            $kamarTerisi = Kamar::where('status_kamar', 'Terisi')->count();
            $occupancyRate = $totalKamar > 0 ? round(($kamarTerisi / $totalKamar) * 100, 2) : 0;

            // Demographics
            $demografi = [
                'jenis_kelamin' => Penghuni::where('status_penghuni', 'Aktif')
                    ->select('jenis_kelamin', DB::raw('count(*) as count'))
                    ->groupBy('jenis_kelamin')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->jenis_kelamin => $item->count];
                    }),
                'pekerjaan' => Penghuni::where('status_penghuni', 'Aktif')
                    ->select('pekerjaan', DB::raw('count(*) as count'))
                    ->groupBy('pekerjaan')
                    ->orderByDesc('count')
                    ->limit(5)
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->pekerjaan ?: 'Tidak Diketahui' => $item->count];
                    }),
                'rentang_umur' => $this->getRentangUmur(),
            ];

            // Recent check-ins and check-outs
            $checkInTerbaru = Penghuni::with(['user', 'kamar'])
                ->where('status_penghuni', 'Aktif')
                ->orderBy('tanggal_masuk', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($penghuni) {
                    return [
                        'nama' => $penghuni->user->nama_lengkap,
                        'kamar' => $penghuni->kamar->kode_kamar,
                        'tanggal_masuk' => $penghuni->tanggal_masuk,
                        'lama_tinggal' => $penghuni->lama_tinggal,
                    ];
                });

            $checkOutTerbaru = Penghuni::with(['user', 'kamar'])
                ->where('status_penghuni', 'Non-Aktif')
                ->whereNotNull('tanggal_keluar')
                ->orderBy('tanggal_keluar', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($penghuni) {
                    return [
                        'nama' => $penghuni->user->nama_lengkap,
                        'kamar' => $penghuni->kamar->kode_kamar ?? '-',
                        'tanggal_keluar' => $penghuni->tanggal_keluar,
                        'lama_tinggal' => $penghuni->lama_tinggal,
                    ];
                });

            // Payment performance
            $performancePembayaran = Penghuni::with(['user', 'kamar'])
                ->where('status_penghuni', 'Aktif')
                ->get()
                ->map(function ($penghuni) {
                    $totalTagihan = $penghuni->tagihan()->count();
                    $tagihanLunas = $penghuni->tagihan()->where('status_tagihan', 'Lunas')->count();
                    $tagihanTerlambat = $penghuni->tagihan()->where('status_tagihan', 'Terlambat')->count();
                    
                    return [
                        'penghuni' => $penghuni->user->nama_lengkap,
                        'kamar' => $penghuni->kamar->kode_kamar,
                        'total_tagihan' => $totalTagihan,
                        'tagihan_lunas' => $tagihanLunas,
                        'tagihan_terlambat' => $tagihanTerlambat,
                        'persentase_lunas' => $totalTagihan > 0 ? round(($tagihanLunas / $totalTagihan) * 100, 2) : 0,
                    ];
                })
                ->sortByDesc('persentase_lunas')
                ->values();

            // Monthly check-in trend (last 12 months)
            $trendCheckIn = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $count = Penghuni::whereMonth('tanggal_masuk', $month->month)
                               ->whereYear('tanggal_masuk', $month->year)
                               ->count();
                
                $trendCheckIn[] = [
                    'bulan' => $month->format('Y-m'),
                    'bulan_display' => $month->format('M Y'),
                    'jumlah' => $count,
                ];
            }

            return $this->successResponse([
                'ringkasan' => [
                    'total_penghuni' => $totalPenghuni,
                    'penghuni_aktif' => $penghuniAktif,
                    'penghuni_non_aktif' => $penghuniNonAktif,
                    'occupancy_rate' => $occupancyRate,
                    'kamar_terisi' => $kamarTerisi,
                    'total_kamar' => $totalKamar,
                ],
                'demografi' => $demografi,
                'check_in_terbaru' => $checkInTerbaru,
                'check_out_terbaru' => $checkOutTerbaru,
                'performance_pembayaran' => $performancePembayaran,
                'trend_check_in' => $trendCheckIn,
                'generated_at' => now()->toISOString(),
            ], 'Laporan penghuni berhasil diambil');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal mengambil laporan penghuni: ' . $e->getMessage());
        }
    }

    /**
     * Get laundry report
     */
    public function laundry(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $periodeAwal = Carbon::parse($request->periode_mulai);
        $periodeAkhir = Carbon::parse($request->periode_selesai);

        try {
            // Basic statistics
            $totalOrder = OrderLaundry::whereBetween('tanggal_terima', [$periodeAwal, $periodeAkhir])->count();
            $orderSelesai = OrderLaundry::whereBetween('tanggal_terima', [$periodeAwal, $periodeAkhir])
                                      ->where('status_order', 'Selesai')
                                      ->count();
            $orderDibatalkan = OrderLaundry::whereBetween('tanggal_terima', [$periodeAwal, $periodeAkhir])
                                         ->where('status_order', 'Dibatalkan')
                                         ->count();

            $totalBerat = OrderLaundry::whereBetween('tanggal_terima', [$periodeAwal, $periodeAkhir])
                                    ->sum('berat_kg');
            $totalPendapatan = OrderLaundry::whereBetween('tanggal_terima', [$periodeAwal, $periodeAkhir])
                                         ->where('status_bayar', 'Sudah Dibayar')
                                         ->sum('total_biaya');

            // Service type breakdown
            $layananPopuler = OrderLaundry::with('jenisLayanan')
                ->whereBetween('tanggal_terima', [$periodeAwal, $periodeAkhir])
                ->select('jenis_layanan_id', DB::raw('count(*) as total_order'), DB::raw('sum(berat_kg) as total_berat'), DB::raw('sum(total_biaya) as total_pendapatan'))
                ->groupBy('jenis_layanan_id')
                ->orderByDesc('total_order')
                ->get()
                ->map(function ($item) {
                    return [
                        'layanan' => $item->jenisLayanan->nama_layanan,
                        'total_order' => $item->total_order,
                        'total_berat' => (float) $item->total_berat,
                        'total_pendapatan' => (float) $item->total_pendapatan,
                        'rata_rata_berat' => $item->total_order > 0 ? round($item->total_berat / $item->total_order, 2) : 0,
                    ];
                });

            // Daily trend
            $trendHarian = [];
            $period = $periodeAwal->copy();
            while ($period->lte($periodeAkhir)) {
                $orderHari = OrderLaundry::whereDate('tanggal_terima', $period)->count();
                $beratHari = OrderLaundry::whereDate('tanggal_terima', $period)->sum('berat_kg');
                $pendapatanHari = OrderLaundry::whereDate('tanggal_terima', $period)
                                            ->where('status_bayar', 'Sudah Dibayar')
                                            ->sum('total_biaya');

                $trendHarian[] = [
                    'tanggal' => $period->toDateString(),
                    'tanggal_display' => $period->format('d/m'),
                    'total_order' => $orderHari,
                    'total_berat' => (float) $beratHari,
                    'total_pendapatan' => (float) $pendapatanHari,
                ];

                $period->addDay();
            }

            // Performance metrics
            $rataWaktuSelesai = OrderLaundry::whereBetween('tanggal_terima', [$periodeAwal, $periodeAkhir])
                ->where('status_order', 'Selesai')
                ->whereNotNull('tanggal_selesai')
                ->get()
                ->avg(function ($order) {
                    return Carbon::parse($order->tanggal_selesai)->diffInHours(Carbon::parse($order->tanggal_terima));
                });

            $orderTerlambat = OrderLaundry::whereBetween('tanggal_terima', [$periodeAwal, $periodeAkhir])
                ->where('tanggal_estimasi_selesai', '<', now())
                ->whereNotIn('status_order', ['Selesai', 'Dibatalkan'])
                ->count();

            // Top customers
            $pelangganTop = OrderLaundry::with(['penghuni.user'])
                ->whereBetween('tanggal_terima', [$periodeAwal, $periodeAkhir])
                ->select('penghuni_id', DB::raw('count(*) as total_order'), DB::raw('sum(berat_kg) as total_berat'), DB::raw('sum(total_biaya) as total_bayar'))
                ->groupBy('penghuni_id')
                ->orderByDesc('total_order')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'penghuni' => $item->penghuni->user->nama_lengkap,
                        'kamar' => $item->penghuni->kamar->kode_kamar ?? '-',
                        'total_order' => $item->total_order,
                        'total_berat' => (float) $item->total_berat,
                        'total_bayar' => (float) $item->total_bayar,
                    ];
                });

            return $this->successResponse([
                'periode' => [
                    'mulai' => $periodeAwal->toDateString(),
                    'selesai' => $periodeAkhir->toDateString(),
                ],
                'ringkasan' => [
                    'total_order' => $totalOrder,
                    'order_selesai' => $orderSelesai,
                    'order_dibatalkan' => $orderDibatalkan,
                    'completion_rate' => $totalOrder > 0 ? round(($orderSelesai / $totalOrder) * 100, 2) : 0,
                    'total_berat' => (float) $totalBerat,
                    'total_pendapatan' => (float) $totalPendapatan,
                    'rata_berat_per_order' => $totalOrder > 0 ? round($totalBerat / $totalOrder, 2) : 0,
                    'rata_pendapatan_per_order' => $totalOrder > 0 ? round($totalPendapatan / $totalOrder, 0) : 0,
                ],
                'performance' => [
                    'rata_waktu_selesai_jam' => round($rataWaktuSelesai ?? 0, 1),
                    'order_terlambat' => $orderTerlambat,
                    'ontime_rate' => $totalOrder > 0 ? round((($totalOrder - $orderTerlambat) / $totalOrder) * 100, 2) : 0,
                ],
                'layanan_populer' => $layananPopuler,
                'trend_harian' => $trendHarian,
                'pelanggan_top' => $pelangganTop,
                'generated_at' => now()->toISOString(),
            ], 'Laporan laundry berhasil diambil');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal mengambil laporan laundry: ' . $e->getMessage());
        }
    }

    /**
     * Get age range distribution
     */
    private function getRentangUmur(): array
    {
        $penghuni = Penghuni::where('status_penghuni', 'Aktif')
                           ->whereNotNull('tanggal_lahir')
                           ->get();

        $ranges = [
            '< 20' => 0,
            '20-25' => 0,
            '26-30' => 0,
            '31-35' => 0,
            '> 35' => 0,
        ];

        foreach ($penghuni as $p) {
            $umur = $p->umur;
            if ($umur < 20) {
                $ranges['< 20']++;
            } elseif ($umur <= 25) {
                $ranges['20-25']++;
            } elseif ($umur <= 30) {
                $ranges['26-30']++;
            } elseif ($umur <= 35) {
                $ranges['31-35']++;
            } else {
                $ranges['> 35']++;
            }
        }

        return $ranges;
    }
}
