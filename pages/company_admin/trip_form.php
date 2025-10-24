<?php
$pageTitle = "Sefer Ekle/Düzenle";
require_once __DIR__ . '/../../includes/header.php';

requireRole(ROLE_COMPANY_ADMIN);

$currentUser = getCurrentUser();
$tripId = input('id');
$isEdit = !empty($tripId);

// Düzenleme modunda seferi getir
$trip = null;
if ($isEdit) {
    $trip = fetchOne("SELECT * FROM Trips WHERE id = ? AND company_id = ?", [$tripId, $currentUser['company_id']]);
    if (!$trip) {
        redirect('/pages/company_admin/trips.php', 'Sefer bulunamadı veya size ait değil.', 'error');
    }
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departureCity = trim(input('departure_city'));
    $destinationCity = trim(input('destination_city'));
    $departureDate = input('departure_date');
    $departureTime = input('departure_time');
    $arrivalDate = input('arrival_date');
    $arrivalTime = input('arrival_time');
    $price = floatval(input('price'));
    $capacity = intval(input('capacity'));
    
    // Validasyon
    $errors = [];
    
    if (empty($departureCity)) $errors[] = 'Kalkış şehri boş olamaz';
    if (empty($destinationCity)) $errors[] = 'Varış şehri boş olamaz';
    if ($departureCity === $destinationCity) $errors[] = 'Kalkış ve varış şehri aynı olamaz';
    if (empty($departureDate) || empty($departureTime)) $errors[] = 'Kalkış tarihi ve saati boş olamaz';
    if (empty($arrivalDate) || empty($arrivalTime)) $errors[] = 'Varış tarihi ve saati boş olamaz';
    if ($price <= 0) $errors[] = 'Fiyat 0\'dan büyük olmalıdır';
    if ($capacity <= 0 || $capacity > 60) $errors[] = 'Kapasite 1-60 arasında olmalıdır';
    
    $departureDateTime = $departureDate . ' ' . $departureTime . ':00';
    $arrivalDateTime = $arrivalDate . ' ' . $arrivalTime . ':00';
    
    if (strtotime($arrivalDateTime) <= strtotime($departureDateTime)) {
        $errors[] = 'Varış tarihi, kalkış tarihinden sonra olmalıdır';
    }
    
    if (empty($errors)) {
        try {
            if ($isEdit) {
                // Güncelle
                execute("
                    UPDATE Trips SET 
                        departure_city = ?,
                        destination_city = ?,
                        departure_time = ?,
                        arrival_time = ?,
                        price = ?,
                        capacity = ?
                    WHERE id = ? AND company_id = ?
                ", [$departureCity, $destinationCity, $departureDateTime, $arrivalDateTime, $price, $capacity, $tripId, $currentUser['company_id']]);
                
                redirect('/pages/company_admin/trips.php', 'Sefer başarıyla güncellendi.');
            } else {
                // Yeni ekle
                $newTripId = generateUUID();
                insert("
                    INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ", [$newTripId, $currentUser['company_id'], $departureCity, $destinationCity, $departureDateTime, $arrivalDateTime, $price, $capacity]);
                
                redirect('/pages/company_admin/trips.php', 'Sefer başarıyla eklendi.');
            }
        } catch (Exception $e) {
            $errors[] = 'Bir hata oluştu: ' . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            $_SESSION['flash_message'] = $error;
            $_SESSION['flash_type'] = 'error';
            break;
        }
    }
}

$cities = ['İstanbul', 'Ankara', 'İzmir', 'Antalya', 'Bursa', 'Adana', 'Gaziantep', 'Konya', 'Kayseri', 'Trabzon', 'Samsun', 'Mersin', 'Eskişehir', 'Denizli'];
?>

<div class="row">
    <div class="col-12">
        <h2>
            <i class="fas fa-<?php echo $isEdit ? 'edit' : 'plus'; ?>"></i> 
            <?php echo $isEdit ? 'Sefer Düzenle' : 'Yeni Sefer Ekle'; ?>
        </h2>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-body">
                <form action="" method="POST" data-validate id="tripForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="departure_city" class="form-label">Kalkış Şehri *</label>
                            <select class="form-select" id="departure_city" name="departure_city" required>
                                <option value="">Şehir seçin</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?php echo e($city); ?>" 
                                            <?php echo ($trip && $trip['departure_city'] === $city) ? 'selected' : ''; ?>>
                                        <?php echo e($city); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="destination_city" class="form-label">Varış Şehri *</label>
                            <select class="form-select" id="destination_city" name="destination_city" required>
                                <option value="">Şehir seçin</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?php echo e($city); ?>" 
                                            <?php echo ($trip && $trip['destination_city'] === $city) ? 'selected' : ''; ?>>
                                        <?php echo e($city); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="departure_date" class="form-label">Kalkış Tarihi *</label>
                            <input type="date" class="form-control" id="departure_date" name="departure_date" 
                                   value="<?php echo $trip ? date('Y-m-d', strtotime($trip['departure_time'])) : ''; ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="departure_time" class="form-label">Kalkış Saati *</label>
                            <input type="time" class="form-control" id="departure_time" name="departure_time" 
                                   value="<?php echo $trip ? date('H:i', strtotime($trip['departure_time'])) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="arrival_date" class="form-label">Varış Tarihi *</label>
                            <input type="date" class="form-control" id="arrival_date" name="arrival_date" 
                                   value="<?php echo $trip ? date('Y-m-d', strtotime($trip['arrival_time'])) : ''; ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="arrival_time" class="form-label">Varış Saati *</label>
                            <input type="time" class="form-control" id="arrival_time" name="arrival_time" 
                                   value="<?php echo $trip ? date('H:i', strtotime($trip['arrival_time'])) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Fiyat (₺) *</label>
                            <input type="number" class="form-control" id="price" name="price" 
                                   value="<?php echo $trip ? $trip['price'] : ''; ?>" 
                                   step="0.01" min="0" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="capacity" class="form-label">Koltuk Kapasitesi *</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" 
                                   value="<?php echo $trip ? $trip['capacity'] : '40'; ?>" 
                                   min="1" max="60" required>
                            <div class="form-text">Maksimum 60 koltuk</div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/pages/company_admin/trips.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Geri
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $isEdit ? 'Güncelle' : 'Kaydet'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

