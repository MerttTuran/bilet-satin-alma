<?php
// init_db.php - Veritabanı Başlatma Scripti

require_once 'config.php';
require_once 'database.php';

echo "Veritabanı oluşturuluyor...\n";

$db = getDB();

// Tabloları oluştur
$db->exec("
    -- User Tablosu
    CREATE TABLE IF NOT EXISTS User (
        id TEXT PRIMARY KEY,
        full_name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        role TEXT NOT NULL,
        password TEXT NOT NULL,
        company_id TEXT,
        balance REAL DEFAULT 800,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES Bus_Company(id) ON DELETE SET NULL
    );

    -- Bus_Company Tablosu
    CREATE TABLE IF NOT EXISTS Bus_Company (
        id TEXT PRIMARY KEY,
        name TEXT UNIQUE NOT NULL,
        logo_path TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Trips Tablosu
    CREATE TABLE IF NOT EXISTS Trips (
        id TEXT PRIMARY KEY,
        company_id TEXT NOT NULL,
        destination_city TEXT NOT NULL,
        arrival_time DATETIME NOT NULL,
        departure_time DATETIME NOT NULL,
        departure_city TEXT NOT NULL,
        price REAL NOT NULL,
        capacity INTEGER NOT NULL,
        created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES Bus_Company(id) ON DELETE CASCADE
    );

    -- Tickets Tablosu
    CREATE TABLE IF NOT EXISTS Tickets (
        id TEXT PRIMARY KEY,
        trip_id TEXT NOT NULL,
        user_id TEXT NOT NULL,
        status TEXT DEFAULT 'active' NOT NULL,
        total_price REAL NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trip_id) REFERENCES Trips(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
    );

    -- Booked_Seats Tablosu
    CREATE TABLE IF NOT EXISTS Booked_Seats (
        id TEXT PRIMARY KEY,
        ticket_id TEXT NOT NULL,
        seat_number INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ticket_id) REFERENCES Tickets(id) ON DELETE CASCADE
    );

    -- Coupons Tablosu
    CREATE TABLE IF NOT EXISTS Coupons (
        id TEXT PRIMARY KEY,
        code TEXT NOT NULL,
        discount REAL NOT NULL,
        usage_limit INTEGER,
        expire_date DATETIME,
        company_id TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES Bus_Company(id) ON DELETE CASCADE
    );

    -- User_Coupons Tablosu
    CREATE TABLE IF NOT EXISTS User_Coupons (
        id TEXT PRIMARY KEY,
        coupon_id TEXT NOT NULL,
        user_id TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (coupon_id) REFERENCES Coupons(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
    );

    -- İndeksler
    CREATE INDEX IF NOT EXISTS idx_user_email ON User(email);
    CREATE INDEX IF NOT EXISTS idx_user_role ON User(role);
    CREATE INDEX IF NOT EXISTS idx_trips_company ON Trips(company_id);
    CREATE INDEX IF NOT EXISTS idx_trips_departure ON Trips(departure_time);
    CREATE INDEX IF NOT EXISTS idx_tickets_user ON Tickets(user_id);
    CREATE INDEX IF NOT EXISTS idx_tickets_trip ON Tickets(trip_id);
    CREATE INDEX IF NOT EXISTS idx_coupons_code ON Coupons(code);
");

echo "Tablolar oluşturuldu.\n";

// Varsayılan Admin kullanıcısı oluştur
$adminId = generateUUID();
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);

$db->exec("INSERT OR IGNORE INTO User (id, full_name, email, role, password, balance) 
           VALUES ('$adminId', 'Sistem Admin', 'admin@busticket.com', 'admin', '$adminPassword', 10000)");

echo "Admin kullanıcısı oluşturuldu (Email: admin@busticket.com, Şifre: admin123)\n";

// Örnek otobüs firmaları ekle
$companies = [
    ['id' => generateUUID(), 'name' => 'Metro Turizm'],
    ['id' => generateUUID(), 'name' => 'Pamukkale Turizm'],
    ['id' => generateUUID(), 'name' => 'Kamil Koç']
];

foreach ($companies as $company) {
    $db->exec("INSERT OR IGNORE INTO Bus_Company (id, name) VALUES ('{$company['id']}', '{$company['name']}')");
}

echo "Örnek firmalar oluşturuldu.\n";

// Her firma için bir Firma Admin oluştur
$companyIndex = 1;
foreach ($companies as $company) {
    $adminId = generateUUID();
    $email = "admin" . $companyIndex . "@" . strtolower(str_replace(' ', '', $company['name'])) . ".com";
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $db->exec("INSERT OR IGNORE INTO User (id, full_name, email, role, password, company_id, balance) 
               VALUES ('$adminId', '{$company['name']} Admin', '$email', 'company.admin', '$password', '{$company['id']}', 5000)");
    
    echo "Firma Admin oluşturuldu: {$email} (Şifre: admin123)\n";
    $companyIndex++;
}

// Örnek kullanıcı oluştur
$userId = generateUUID();
$userPassword = password_hash('user123', PASSWORD_DEFAULT);
$db->exec("INSERT OR IGNORE INTO User (id, full_name, email, role, password, balance) 
           VALUES ('$userId', 'Test Kullanıcı', 'user@test.com', 'user', '$userPassword', 1500)");

echo "Örnek kullanıcı oluşturuldu (Email: user@test.com, Şifre: user123)\n";

// Örnek seferler ekle
$cities = ['İstanbul', 'Ankara', 'İzmir', 'Antalya', 'Bursa', 'Adana'];
$tripCount = 0;

foreach ($companies as $company) {
    for ($i = 0; $i < 3; $i++) {
        $from = $cities[array_rand($cities)];
        $to = $cities[array_rand($cities)];
        
        if ($from === $to) continue;
        
        $tripId = generateUUID();
        $departureTime = date('Y-m-d H:i:s', strtotime('+' . rand(1, 30) . ' days +' . rand(8, 20) . ' hours'));
        $arrivalTime = date('Y-m-d H:i:s', strtotime($departureTime . ' +' . rand(3, 12) . ' hours'));
        $price = rand(150, 500);
        $capacity = rand(30, 45);
        
        $db->exec("INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity) 
                   VALUES ('$tripId', '{$company['id']}', '$from', '$to', '$departureTime', '$arrivalTime', $price, $capacity)");
        
        $tripCount++;
    }
}

echo "$tripCount örnek sefer oluşturuldu.\n";

// Örnek kuponlar oluştur
$coupons = [
    ['code' => 'WELCOME20', 'discount' => 20, 'usage_limit' => 100, 'company_id' => null], // Tüm firmalar için
    ['code' => 'SUMMER30', 'discount' => 30, 'usage_limit' => 50, 'company_id' => null],
];

foreach ($coupons as $coupon) {
    $couponId = generateUUID();
    $expireDate = date('Y-m-d H:i:s', strtotime('+90 days'));
    $companyIdValue = $coupon['company_id'] ? "'{$coupon['company_id']}'" : 'NULL';
    
    $db->exec("INSERT OR IGNORE INTO Coupons (id, code, discount, usage_limit, expire_date, company_id) 
               VALUES ('$couponId', '{$coupon['code']}', {$coupon['discount']}, {$coupon['usage_limit']}, '$expireDate', $companyIdValue)");
}

echo "Örnek kuponlar oluşturuldu (WELCOME20, SUMMER30).\n";

echo "\n✅ Veritabanı başarıyla oluşturuldu!\n";
echo "\nGiriş Bilgileri:\n";
echo "================\n";
echo "Admin: admin@busticket.com / admin123\n";
echo "Kullanıcı: user@test.com / user123\n";
echo "Firma Adminler: admin1@metroturizm.com, admin2@pamukkaletürizm.com, admin3@kamilkoç.com / admin123\n";
?>

