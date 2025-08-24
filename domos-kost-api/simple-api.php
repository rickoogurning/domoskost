<?php
/**
 * Simple PHP API untuk Demo Domos Kost
 * Dapat dijalankan tanpa Composer/Laravel framework
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'domos_kost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}

// Get request info
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/simple-api.php', '', $path);
$pathParts = array_filter(explode('/', $path));
$input = json_decode(file_get_contents('php://input'), true);

// Simple router
function route($method, $path, $pathParts, $input, $pdo) {
    // Health check
    if ($path === '/health' || $path === '/api/health') {
        return [
            'success' => true,
            'status' => 'OK',
            'message' => 'Domos Kost Simple API is running',
            'timestamp' => date('c')
        ];
    }

    // Login endpoint
    if ($method === 'POST' && ($path === '/auth/login' || $path === '/api/auth/login')) {
        return handleLogin($input, $pdo);
    }

    // Dashboard endpoints
    if ($method === 'GET' && $path === '/dashboard/admin') {
        return getAdminDashboard($pdo);
    }

    if ($method === 'GET' && $path === '/dashboard/penghuni') {
        return getPenghuniDashboard($pdo);
    }

    if ($method === 'GET' && $path === '/dashboard/laundry') {
        return getLaundryDashboard($pdo);
    }

    // Default response
    return [
        'success' => false,
        'message' => 'Endpoint not found',
        'available_endpoints' => [
            'health' => 'GET /health',
            'auth' => 'POST /auth/login',
            'dashboard' => 'GET /dashboard/{admin|penghuni|laundry}'
        ]
    ];
}

// Login handler
function handleLogin($input, $pdo) {
    if (!$input || !isset($input['username']) || !isset($input['password'])) {
        return [
            'success' => false,
            'message' => 'Username dan password diperlukan'
        ];
    }

    // Demo credentials
    $demoUsers = [
        'pelita' => ['password' => 'password123', 'role' => 'Admin', 'name' => 'Pelita Ginting'],
        'thika' => ['password' => 'password123', 'role' => 'Penghuni', 'name' => 'Thika'],
        'diana' => ['password' => 'password123', 'role' => 'Petugas Laundry', 'name' => 'Diana']
    ];

    $username = $input['username'];
    $password = $input['password'];

    if (isset($demoUsers[$username]) && $demoUsers[$username]['password'] === $password) {
        $user = $demoUsers[$username];
        return [
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'username' => $username,
                    'nama_lengkap' => $user['name'],
                    'role' => $user['role']
                ],
                'token' => 'demo-token-' . $username
            ]
        ];
    }

    return [
        'success' => false,
        'message' => 'Username atau password salah'
    ];
}

// Admin dashboard data
function getAdminDashboard($pdo) {
    try {
        // Sample data for demo
        return [
            'success' => true,
            'data' => [
                'stats' => [
                    'totalPenghuni' => 18,
                    'totalKamar' => 24,
                    'kamarTerisi' => 18,
                    'kamarKosong' => 6,
                    'pendapatanBulanIni' => 18000000,
                    'pendapatanBulanLalu' => 16500000,
                    'tagihanBelumBayar' => 3,
                    'laundryAktif' => 8
                ],
                'charts' => [
                    'revenueTrend' => [
                        ['month' => 'Mar', 'sewa' => 15000000, 'laundry' => 2500000],
                        ['month' => 'Apr', 'sewa' => 16000000, 'laundry' => 2800000],
                        ['month' => 'May', 'sewa' => 16500000, 'laundry' => 3000000],
                        ['month' => 'Jun', 'sewa' => 17000000, 'laundry' => 3200000],
                        ['month' => 'Jul', 'sewa' => 17500000, 'laundry' => 3500000],
                        ['month' => 'Aug', 'sewa' => 18000000, 'laundry' => 3800000]
                    ],
                    'occupancyData' => [
                        ['name' => 'Terisi', 'value' => 18, 'color' => '#10b981'],
                        ['name' => 'Kosong', 'value' => 6, 'color' => '#f59e0b']
                    ],
                    'laundryStatusToday' => [
                        ['status' => 'Diterima', 'count' => 3],
                        ['status' => 'Dicuci', 'count' => 2],
                        ['status' => 'Siap Diambil', 'count' => 2],
                        ['status' => 'Selesai', 'count' => 1]
                    ]
                ],
                'recentActivities' => [
                    [
                        'type' => 'payment',
                        'user' => 'Thika',
                        'action' => 'melakukan pembayaran sewa',
                        'amount' => 1000000,
                        'time' => '2 jam yang lalu'
                    ],
                    [
                        'type' => 'laundry',
                        'user' => 'Christine',
                        'action' => 'membuat order laundry #LD-202508-001',
                        'time' => '3 jam yang lalu'
                    ]
                ]
            ]
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error getting admin dashboard: ' . $e->getMessage()
        ];
    }
}

// Penghuni dashboard data
function getPenghuniDashboard($pdo) {
    return [
        'success' => true,
        'data' => [
            'kamar' => [
                'kode' => 'A-101',
                'lantai' => 1,
                'tipe' => 'Single',
                'tarif' => 1000000,
                'tanggal_masuk' => '2024-01-15'
            ],
            'tagihan' => [
                'bulan_ini' => [
                    'periode' => 'Agustus 2025',
                    'total' => 1000000,
                    'status' => 'Belum Dibayar',
                    'jatuh_tempo' => '2025-08-10',
                    'denda' => 0
                ],
                'history' => [
                    ['periode' => 'Juli 2025', 'total' => 1000000, 'status' => 'Lunas', 'tanggal_bayar' => '2025-07-08'],
                    ['periode' => 'Juni 2025', 'total' => 1000000, 'status' => 'Lunas', 'tanggal_bayar' => '2025-06-05']
                ]
            ],
            'laundry' => [
                'active' => [
                    [
                        'id' => 'LD-202508-001',
                        'jenis' => 'Cuci + Setrika',
                        'berat' => 3.5,
                        'status' => 'Dicuci',
                        'progress' => 40,
                        'tanggal_order' => '2025-08-23T08:00:00Z',
                        'estimasi_selesai' => '2025-08-25T17:00:00Z',
                        'biaya' => 35000
                    ]
                ],
                'history' => [
                    ['id' => 'LD-202507-045', 'jenis' => 'Cuci + Setrika', 'berat' => 2.8, 'biaya' => 28000, 'tanggal_selesai' => '2025-07-20T16:00:00Z']
                ]
            ]
        ]
    ];
}

// Laundry dashboard data  
function getLaundryDashboard($pdo) {
    return [
        'success' => true,
        'data' => [
            'stats' => [
                'orderHariIni' => 5,
                'sedangDiproses' => 8,
                'siapDiambil' => 3,
                'selesaiHariIni' => 4,
                'pendapatanHariIni' => 150000
            ],
            'activeOrders' => [
                [
                    'id' => 'LD-202508-001',
                    'kode_order' => 'LD-202508-001',
                    'penghuni' => ['nama_lengkap' => 'Thika', 'kamar' => 'A-101'],
                    'jenis_layanan' => ['nama_layanan' => 'Cuci + Setrika'],
                    'tanggal_terima' => '2025-08-23T08:00:00Z',
                    'tanggal_estimasi_selesai' => '2025-08-25T17:00:00Z',
                    'berat_kg' => 3.5,
                    'total_biaya' => 35000,
                    'status_order' => 'Dicuci',
                    'is_overdue' => false,
                    'next_status' => 'Dikeringkan'
                ],
                [
                    'id' => 'LD-202508-002',
                    'kode_order' => 'LD-202508-002',
                    'penghuni' => ['nama_lengkap' => 'Christine', 'kamar' => 'B-205'],
                    'jenis_layanan' => ['nama_layanan' => 'Cuci Biasa'],
                    'tanggal_terima' => '2025-08-23T10:00:00Z',
                    'tanggal_estimasi_selesai' => '2025-08-24T17:00:00Z',
                    'berat_kg' => 2.0,
                    'total_biaya' => 15000,
                    'status_order' => 'Siap Diambil',
                    'is_overdue' => false,
                    'next_status' => 'Selesai'
                ]
            ]
        ]
    ];
}

// Execute router
try {
    $response = route($method, $path, $pathParts, $input, $pdo);
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
