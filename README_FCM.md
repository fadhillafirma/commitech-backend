# 🚀 Quick Start: FCM Implementation

## ✅ Yang Sudah Selesai

### **1. Package Installed**
```bash
✅ kreait/firebase-php v7.24.0
✅ Extension sodium enabled
```

### **2. Files Created**
```
✅ config/firebase.php - Firebase configuration
✅ app/Services/FirebaseService.php - FCM service class
✅ app/Http/Controllers/FCMController.php - API endpoints
✅ database/migrations/xxxx_create_fcm_tokens_table.php - Database schema
✅ routes/api.php - Routes updated
✅ FIREBASE_SETUP.md - Complete documentation
```

---

## 📝 Yang Perlu Anda Lakukan

### **Step 1: Download Service Account Key**

1. Buka Firebase Console: https://console.firebase.google.com/
2. Pilih project Anda (Commitech)
3. Settings ⚙️ → Project Settings → Service Accounts
4. Click "Generate new private key"
5. Download file JSON

### **Step 2: Simpan File**

```bash
# Buat folder
mkdir storage/app/firebase

# Copy file yang didownload ke:
storage/app/firebase/serviceAccountKey.json
```

### **Step 3: Update .env**

Tambahkan di file `.env`:

```env
FIREBASE_PROJECT_ID=commitech-xxxxx
FIREBASE_CREDENTIALS=storage/app/firebase/serviceAccountKey.json
```

**Ganti `commitech-xxxxx` dengan Project ID Anda!**

### **Step 4: Run Migration**

```bash
php artisan migrate
```

### **Step 5: Test**

```bash
# Start server
php artisan serve

# Test endpoint (via Postman/curl)
POST http://localhost:8000/api/fcm/register
Authorization: Bearer {your_token}

Body:
{
  "fcm_token": "test_token",
  "device_type": "android"
}
```

---

## 🔔 Cara Kirim Notifikasi

### **Contoh 1: Di Controller**

```php
use App\Services\FirebaseService;

class YourController extends Controller
{
    public function __construct(protected FirebaseService $firebase) {}

    public function updateSchedule($id)
    {
        // ... update logic

        // Kirim notifikasi ke semua admin
        $this->firebase->sendToAllAdmins(
            'Jadwal Diubah',
            'Jadwal wawancara telah diperbarui',
            ['type' => 'SCHEDULE_CHANGED', 'schedule_id' => $id]
        );
    }
}
```

### **Contoh 2: Quick Send**

```php
// Kirim ke semua admin
app(FirebaseService::class)->sendToAllAdmins(
    'Title',
    'Body',
    ['data' => 'value']
);

// Kirim ke user tertentu
app(FirebaseService::class)->sendToUser(
    userId: 123,
    title: 'Title',
    body: 'Body',
    data: []
);
```

---

## 📡 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/fcm/register` | Register FCM token |
| DELETE | `/api/fcm/unregister` | Unregister token |
| GET | `/api/fcm/devices` | Get user devices |
| DELETE | `/api/fcm/devices/{id}` | Delete device |

---

## 🔍 Troubleshooting

### **Error: Firebase credentials file not found**
```bash
# Check file exists
ls storage/app/firebase/serviceAccountKey.json

# Check .env
cat .env | grep FIREBASE
```

### **Error: Class 'Kreait\Firebase\Factory' not found**
```bash
composer dump-autoload
```

### **Check Logs**
```bash
tail -f storage/logs/laravel.log
```

---

## 📚 Full Documentation

Lihat file `FIREBASE_SETUP.md` untuk dokumentasi lengkap.

---

**Next:** Setelah backend setup selesai, lanjut ke Android implementation!
