<?php
// api/generate_pdf.php - Bilet PDF Oluşturma

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$ticketId = input('ticket_id');

if (!$ticketId) {
    die('Geçersiz bilet ID');
}

$currentUser = getCurrentUser();

// Bileti getir
$ticket = fetchOne("
    SELECT 
        t.id as ticket_id,
        t.status,
        t.total_price,
        t.created_at,
        tr.departure_city,
        tr.destination_city,
        tr.departure_time,
        tr.arrival_time,
        tr.price as seat_price,
        bc.name as company_name,
        bc.logo_path,
        u.full_name as passenger_name,
        u.email as passenger_email,
        GROUP_CONCAT(bs.seat_number) as seat_numbers
    FROM Tickets t
    INNER JOIN Trips tr ON t.trip_id = tr.id
    INNER JOIN Bus_Company bc ON tr.company_id = bc.id
    INNER JOIN User u ON t.user_id = u.id
    LEFT JOIN Booked_Seats bs ON bs.ticket_id = t.id
    WHERE t.id = ?
    GROUP BY t.id
", [$ticketId]);

if (!$ticket) {
    die('Bilet bulunamadı');
}

// Yetki kontrolü - Kullanıcı kendi biletini veya firma/admin yetkisi varsa
if ($currentUser['role'] === ROLE_USER && $ticket['passenger_email'] !== $currentUser['email']) {
    die('Bu bilete erişim yetkiniz yok');
}

$seatNumbers = explode(',', $ticket['seat_numbers']);

// HTML içeriği
ob_start();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otobüs Bileti - <?php echo $ticket['ticket_id']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #f5f5f5;
        }
        .ticket {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 3px solid #0d6efd;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #0d6efd;
            font-size: 32px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            color: #333;
            font-weight: bold;
            margin-top: 10px;
        }
        .route-section {
            display: table;
            width: 100%;
            margin: 30px 0;
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            padding: 20px;
            border-radius: 10px;
        }
        .route-item {
            display: table-cell;
            width: 40%;
            text-align: center;
        }
        .route-arrow {
            display: table-cell;
            width: 20%;
            text-align: center;
            font-size: 40px;
            color: #0d6efd;
            vertical-align: middle;
        }
        .city-name {
            font-size: 28px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        .date-time {
            font-size: 18px;
            color: #666;
        }
        .details-section {
            margin: 30px 0;
        }
        .detail-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .detail-label {
            display: table-cell;
            width: 40%;
            font-weight: bold;
            color: #666;
            font-size: 16px;
        }
        .detail-value {
            display: table-cell;
            width: 60%;
            color: #333;
            font-size: 16px;
        }
        .seats {
            background: #0d6efd;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
            font-size: 18px;
        }
        .price-section {
            text-align: center;
            margin: 30px 0;
            padding: 25px;
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            border-radius: 10px;
        }
        .price-label {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .price-value {
            font-size: 40px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px dashed #0d6efd;
            color: #666;
            font-size: 14px;
        }
        .barcode {
            text-align: center;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 24px;
            letter-spacing: 2px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
        }
        .status-active {
            background: #198754;
            color: white;
        }
        .status-cancelled {
            background: #dc3545;
            color: white;
        }
        @media print {
            body {
                padding: 0;
                background: white;
            }
            .ticket {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h1>🚌 OTOBÜS BİLETİ</h1>
            <div class="company-name"><?php echo e($ticket['company_name']); ?></div>
            <span class="status-badge status-<?php echo $ticket['status']; ?>">
                <?php echo $ticket['status'] === 'active' ? 'GEÇERLİ BİLET' : 'İPTAL EDİLDİ'; ?>
            </span>
        </div>
        
        <div class="route-section">
            <div class="route-item">
                <div class="city-name"><?php echo e($ticket['departure_city']); ?></div>
                <div class="date-time"><?php echo formatDate($ticket['departure_time'], 'd F Y'); ?></div>
                <div class="date-time"><strong><?php echo formatDate($ticket['departure_time'], 'H:i'); ?></strong></div>
            </div>
            <div class="route-arrow">→</div>
            <div class="route-item">
                <div class="city-name"><?php echo e($ticket['destination_city']); ?></div>
                <div class="date-time"><?php echo formatDate($ticket['arrival_time'], 'd F Y'); ?></div>
                <div class="date-time"><strong><?php echo formatDate($ticket['arrival_time'], 'H:i'); ?></strong></div>
            </div>
        </div>
        
        <div class="details-section">
            <div class="detail-row">
                <div class="detail-label">Yolcu Adı:</div>
                <div class="detail-value"><?php echo e($ticket['passenger_name']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">E-posta:</div>
                <div class="detail-value"><?php echo e($ticket['passenger_email']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Koltuk Numaraları:</div>
                <div class="detail-value">
                    <span class="seats"><?php echo implode(', ', $seatNumbers); ?></span>
                </div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Yolcu Sayısı:</div>
                <div class="detail-value"><?php echo count($seatNumbers); ?> Kişi</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Bilet No:</div>
                <div class="detail-value"><?php echo e($ticket['ticket_id']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Satın Alma Tarihi:</div>
                <div class="detail-value"><?php echo formatDate($ticket['created_at'], 'd F Y H:i'); ?></div>
            </div>
        </div>
        
        <div class="price-section">
            <div class="price-label">Toplam Ücret</div>
            <div class="price-value"><?php echo formatMoney($ticket['total_price']); ?></div>
        </div>
        
        <div class="barcode">
            * <?php echo strtoupper(substr($ticket['ticket_id'], 0, 16)); ?> *
        </div>
        
        <div class="footer">
            <p><strong>ÖNEMLİ BİLGİLER:</strong></p>
            <p>• Seyahatinizden en az 30 dakika önce terminalde olunuz.</p>
            <p>• Biletinizi ve kimlik belgenizi yanınızda bulundurunuz.</p>
            <p>• Kalkıştan 1 saat öncesine kadar biletinizi iptal edebilirsiniz.</p>
            <p style="margin-top: 20px;">İyi yolculuklar dileriz!</p>
            <p style="margin-top: 10px;"><small><?php echo APP_NAME; ?> - <?php echo date('Y'); ?></small></p>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
<?php
$html = ob_get_clean();
echo $html;
?>

