# 🔥 Firebase Cloud Messaging (FCM) Setup Guide

Dokumentasi lengkap untuk setup Firebase Cloud Messaging di Laravel Backend Commitech.

---

## 📋 Prerequisites

- ✅ Laravel 10+ installed
- ✅ PHP 8.1+ with Sodium extension enabled
- ✅ Composer installed
- ✅ Firebase project created
- ✅ Service Account Key JSON downloaded

---

## 🚀 Installation Steps

### **Step 1: Install Firebase PHP SDK**

Package sudah terinstall via Composer:

```bash
composer require kreait/firebase-php
```

**Verifikasi instalasi:**
```bash
composer show kreait/firebase-php
```

---

### **Step 2: Setup Service Account Key**

1. **Download Service Account Key dari Firebase Console:**
   - Firebase Console → Project Settings → Service Accounts
   - Click "Generate new private key"
   - Download file JSON

2. **Simpan file di Laravel:**
   ```
   storage/app/firebase/serviceAccountKey.json
   ```

3. **Set permissions (Linux/Mac):**
   ```bash
   chmod 600 storage/app/firebase/serviceAccountKey.json
   ```

4. **Tambahkan ke .gitignore:**
   ```
   storage/app/firebase/serviceAccountKey.json
   ```

---

### **Step 3: Configure Environment Variables**

Edit file `.env`:

```env
# Firebase Configuration
FIREBASE_PROJECT_ID=commitech-xxxxx
FIREBASE_CREDENTIALS=storage/app/firebase/serviceAccountKey.json
```

**Ganti `commitech-xxxxx` dengan Project ID Anda dari Firebase Console.**

---

### **Step 4: Run Migration**

Buat table `fcm_tokens`:

```bash
php artisan migrate
```

**Table schema:**
- `id` - Primary key
- `user_id` - Foreign key ke users table
- `fcm_token` - FCM device token (unique per user)
- `device_type` - android/ios
- `device_name` - Device name (optional)
- `created_at`, `updated_at` - Timestamps

---

## 📡 API Endpoints

### **1. Register FCM Token**

**Endpoint:** `POST /api/fcm/register`

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "fcm_token": "eXaMpLeToKeN123abc...",
  "device_type": "android",
  "device_name": "Samsung Galaxy S21"
}
```

**Response:**
```json
{
  "success": true,
  "message": "FCM token registered successfully"
}
```

---

### **2. Unregister FCM Token**

**Endpoint:** `DELETE /api/fcm/unregister`

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "fcm_token": "eXaMpLeToKeN123abc..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "FCM token unregistered successfully",
  "deleted": true
}
```

---

### **3. Get User Devices**

**Endpoint:** `GET /api/fcm/devices`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response:**
```json
{
  "success": true,
  "devices": [
    {
      "id": 1,
      "device_type": "android",
      "device_name": "Samsung Galaxy S21",
      "created_at": "2025-12-05T10:00:00.000000Z",
      "updated_at": "2025-12-05T10:00:00.000000Z"
    }
  ]
}
```

---

### **4. Delete Device**

**Endpoint:** `DELETE /api/fcm/devices/{id}`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Device removed successfully"
}
```

---

## 🔔 Sending Notifications

### **Using FirebaseService**

Inject `FirebaseService` ke controller Anda:

```php
use App\Services\FirebaseService;

class YourController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function yourMethod()
    {
        // Send to single device
        $this->firebase->sendToDevice(
            token: 'device_token_here',
            title: 'Notification Title',
            body: 'Notification body text',
            data: [
                'type' => 'CUSTOM_TYPE',
                'extra_data' => 'value'
            ]
        );

        // Send to multiple devices
        $this->firebase->sendToMultipleDevices(
            tokens: ['token1', 'token2', 'token3'],
            title: 'Notification Title',
            body: 'Notification body text',
            data: ['type' => 'BROADCAST']
        );

        // Send to all admins
        $this->firebase->sendToAllAdmins(
            title: 'Admin Notification',
            body: 'Important message for all admins',
            data: ['type' => 'ADMIN_ALERT'],
            exceptUserId: auth()->id() // Exclude current user
        );

        // Send to specific user (all their devices)
        $this->firebase->sendToUser(
            userId: 123,
            title: 'Personal Notification',
            body: 'Message for specific user',
            data: ['type' => 'PERSONAL']
        );
    }
}
```

---

## 📝 Example: Notify When Schedule Changed

**File:** `app/Http/Controllers/ScheduleController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        
        // Store old values for notification
        $oldTime = $schedule->scheduled_time;
        
        // Update schedule
        $schedule->update($request->all());

        // Send FCM notification to all admins except who changed it
        $this->firebase->sendToAllAdmins(
            title: 'Jadwal Wawancara Diubah',
            body: "Jadwal {$schedule->participant_name} diubah dari {$oldTime} ke {$schedule->scheduled_time}",
            data: [
                'type' => 'SCHEDULE_CHANGED',
                'schedule_id' => (string) $schedule->id,
                'participant_name' => $schedule->participant_name,
                'old_time' => $oldTime,
                'new_time' => $schedule->scheduled_time,
                'click_action' => 'OPEN_SCHEDULE_DETAIL'
            ],
            exceptUserId: auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Schedule updated and notification sent',
            'data' => $schedule
        ]);
    }
}
```

---

## 📝 Example: Notify When Interview Completed

**File:** `app/Http/Controllers/HasilWawancaraController.php`

```php
public function simpan(Request $request)
{
    // ... validation and save logic

    // Send notification to all admins
    app(FirebaseService::class)->sendToAllAdmins(
        title: 'Wawancara Selesai',
        body: "{$request->nama_peserta} telah menyelesaikan wawancara",
        data: [
            'type' => 'INTERVIEW_COMPLETED',
            'participant_name' => $request->nama_peserta,
            'interview_id' => (string) $hasilWawancara->id,
            'click_action' => 'OPEN_INTERVIEW_RESULT'
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Hasil wawancara berhasil disimpan dan notifikasi terkirim'
    ]);
}
```

---

## 🧪 Testing FCM

### **Test dari Firebase Console**

1. Buka Firebase Console → Cloud Messaging
2. Click "Send your first message"
3. Fill notification details
4. Click "Send test message"
5. Paste FCM token dari Android app
6. Click "Test"

### **Test dari Postman**

1. **Register Token:**
   ```
   POST http://localhost:8000/api/fcm/register
   Authorization: Bearer {your_token}
   
   Body:
   {
     "fcm_token": "test_token_123",
     "device_type": "android"
   }
   ```

2. **Trigger Notification:**
   - Update schedule via API
   - Submit interview result
   - Check Android device for notification

---

## 🔍 Debugging

### **Check Logs**

```bash
tail -f storage/logs/laravel.log
```

**Log entries:**
- `FCM sent successfully` - Notification sent
- `FCM Error` - Failed to send
- `Removed invalid FCM token` - Token cleaned up

### **Common Issues**

| Error | Cause | Solution |
|-------|-------|----------|
| `Firebase credentials file not found` | serviceAccountKey.json missing | Download from Firebase Console |
| `Authentication failed` | Invalid service account key | Re-download key file |
| `Token not found` | Invalid/expired FCM token | Token will be auto-removed |
| `Class 'Kreait\Firebase\Factory' not found` | Package not installed | Run `composer require kreait/firebase-php` |

---

## 📊 Monitoring

### **Check Token Count**

```php
use App\Services\FirebaseService;

$firebase = app(FirebaseService::class);

// Get admin token count
$adminCount = $firebase->getAdminTokenCount();

// Get user token count
$userCount = $firebase->getUserTokenCount($userId);
```

### **Database Queries**

```sql
-- Total registered devices
SELECT COUNT(*) FROM fcm_tokens;

-- Devices per user
SELECT user_id, COUNT(*) as device_count 
FROM fcm_tokens 
GROUP BY user_id;

-- Admin devices
SELECT u.name, COUNT(f.id) as device_count
FROM users u
LEFT JOIN fcm_tokens f ON u.id = f.user_id
WHERE u.role = 'admin'
GROUP BY u.id;
```

---

## 🔐 Security Best Practices

1. **Never commit serviceAccountKey.json**
   ```bash
   # Add to .gitignore
   storage/app/firebase/serviceAccountKey.json
   ```

2. **Use environment variables**
   ```env
   FIREBASE_PROJECT_ID=your-project-id
   FIREBASE_CREDENTIALS=storage/app/firebase/serviceAccountKey.json
   ```

3. **Validate tokens before sending**
   - FirebaseService automatically removes invalid tokens
   - Check logs for failed deliveries

4. **Rate limiting**
   - FCM has rate limits (1 million messages/day for free tier)
   - Implement batching for large broadcasts

---

## 📚 Resources

- **Firebase PHP SDK:** https://github.com/kreait/firebase-php
- **FCM Documentation:** https://firebase.google.com/docs/cloud-messaging
- **Laravel Documentation:** https://laravel.com/docs

---

## ✅ Checklist

- [ ] Package `kreait/firebase-php` installed
- [ ] Service Account Key downloaded and saved
- [ ] `.env` configured with FIREBASE_PROJECT_ID
- [ ] Migration run successfully
- [ ] Routes registered in `api.php`
- [ ] Test notification sent from Firebase Console
- [ ] Test API endpoints with Postman
- [ ] Integrate with existing controllers
- [ ] Monitor logs for errors
- [ ] Document for team members

---

**Setup completed! 🎉**

For questions or issues, check the logs or contact the development team.
