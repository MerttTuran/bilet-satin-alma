#!/bin/bash
# docker-entrypoint.sh - Container başlangıç scripti

set -e

echo "Otobüs Bilet Satış Platformu başlatılıyor..."

# Veritabanı var mı kontrol et
if [ ! -f /var/www/html/database/bus_tickets.db ]; then
    echo "Veritabanı bulunamadı, oluşturuluyor..."
    cd /var/www/html
    php init_db.php
    echo "Veritabanı başarıyla oluşturuldu!"
else
    echo "Veritabanı mevcut, devam ediliyor..."
fi

# İzinleri ayarla
chown -R www-data:www-data /var/www/html/database
chown -R www-data:www-data /var/www/html/uploads
chmod -R 777 /var/www/html/database
chmod -R 777 /var/www/html/uploads

echo "Uygulama hazır! http://localhost:8080 adresinden erişebilirsiniz."
echo ""
echo "Giriş Bilgileri:"
echo "================"
echo "Admin: admin@busticket.com / admin123"
echo "Kullanıcı: user@test.com / user123"
echo "Firma Admin: admin1@metroturizm.com / admin123"
echo ""

# Apache'yi başlat
exec "$@"

