// assets/js/main.js

// Koltuk seçimi
let selectedSeats = [];

function initSeatSelection() {
    const seats = document.querySelectorAll('.seat:not(.booked)');
    const selectedSeatsInput = document.getElementById('selectedSeats');
    const seatCountDisplay = document.getElementById('seatCount');
    const totalPriceDisplay = document.getElementById('totalPrice');
    const basePriceElement = document.getElementById('basePrice');
    
    if (!basePriceElement) return;
    
    const basePrice = parseFloat(basePriceElement.value);
    
    seats.forEach(seat => {
        seat.addEventListener('click', function() {
            const seatNumber = parseInt(this.dataset.seat);
            
            if (this.classList.contains('selected')) {
                // Seçimi kaldır
                this.classList.remove('selected');
                selectedSeats = selectedSeats.filter(s => s !== seatNumber);
            } else {
                // Seç
                this.classList.add('selected');
                selectedSeats.push(seatNumber);
            }
            
            // Sıralı göster
            selectedSeats.sort((a, b) => a - b);
            
            // Güncelle
            selectedSeatsInput.value = selectedSeats.join(',');
            seatCountDisplay.textContent = selectedSeats.length;
            
            const total = basePrice * selectedSeats.length;
            totalPriceDisplay.textContent = formatMoney(total);
            
            updateSubmitButton();
        });
    });
}

function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.disabled = selectedSeats.length === 0;
    }
}

// Kupon uygulama
function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value.trim();
    const tripId = document.getElementById('tripId').value;
    
    if (!couponCode) {
        showAlert('Lütfen bir kupon kodu girin.', 'warning');
        return;
    }
    
    // AJAX isteği
    fetch('/api/validate_coupon.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            code: couponCode,
            trip_id: tripId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            const basePrice = parseFloat(document.getElementById('basePrice').value);
            const discount = data.coupon.discount;
            const discountedPrice = basePrice - (basePrice * discount / 100);
            
            document.getElementById('basePrice').value = discountedPrice;
            document.getElementById('appliedCoupon').value = couponCode;
            document.getElementById('couponDiscount').textContent = discount + '%';
            
            // Toplam fiyatı güncelle
            const total = discountedPrice * selectedSeats.length;
            document.getElementById('totalPrice').textContent = formatMoney(total);
            
            showAlert(`Kupon uygulandı! %${discount} indirim kazandınız.`, 'success');
            document.getElementById('couponCode').disabled = true;
            document.querySelector('.apply-coupon-btn').disabled = true;
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        showAlert('Bir hata oluştu.', 'danger');
        console.error('Error:', error);
    });
}

// Alert gösterme
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    // 5 saniye sonra otomatik kapat
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Para formatı
function formatMoney(amount) {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY'
    }).format(amount);
}

// Bilet iptal onayı
function confirmCancelTicket(ticketId) {
    if (confirm('Bu bileti iptal etmek istediğinize emin misiniz? İptal edilen biletlerin ücreti hesabınıza iade edilecektir.')) {
        window.location.href = `/api/cancel_ticket.php?ticket_id=${ticketId}`;
    }
}

// Silme onayı
function confirmDelete(message = 'Silmek istediğinize emin misiniz?') {
    return confirm(message);
}

// Form validasyonu
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    // Koltuk seçimi varsa başlat
    if (document.querySelector('.seat-container')) {
        initSeatSelection();
    }
    
    // Form validasyonu
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this.id)) {
                e.preventDefault();
                showAlert('Lütfen tüm gerekli alanları doldurun.', 'warning');
            }
        });
    });
    
    // Tarih seçicileri için minimum tarih ayarla
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    dateInputs.forEach(input => {
        if (!input.hasAttribute('data-allow-past')) {
            input.setAttribute('min', today);
        }
    });
    
    // Saat seçicileri için formatı ayarla
    const timeInputs = document.querySelectorAll('input[type="time"]');
    timeInputs.forEach(input => {
        if (!input.value) {
            input.value = '12:00';
        }
    });
});

// Otomatik alert kapatma
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);

