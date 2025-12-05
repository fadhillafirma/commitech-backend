# 🧪 POSTMAN TEST - Session Check

## 📋 SETUP

### **1. Import ke Postman**

**Method:** `GET`  
**URL:** `http://localhost:8000/api/session/check`

**Headers:**
```
Authorization: Bearer 48|GOlgVMHE0RNmH9czfV4xcDADt1Kw2pqbiIBGZka1cf4667dc
Accept: application/json
Content-Type: application/json
```

---

## 🔍 TEST STEPS

### **Step 1: Check Laravel Log**

Buka file log Laravel:
```
c:\Users\user\AndroidStudioProjects\TB PTB\Commitech-backend\storage\logs\laravel.log
```

Atau run command:
```bash
cd "c:\Users\user\AndroidStudioProjects\TB PTB\Commitech-backend"
php artisan tail
```

### **Step 2: Test di Postman**

1. ✅ Buat request baru di Postman
2. ✅ Set method: `GET`
3. ✅ Set URL: `http://localhost:8000/api/session/check`
4. ✅ Add header: `Authorization: Bearer 48|GOlgVMHE0RNmH9czfV4xcDADt1Kw2pqbiIBGZka1cf4667dc`
5. ✅ Add header: `Accept: application/json`
6. ✅ Click **Send**

### **Step 3: Check Response**

**Expected (200 OK):**
```json
{
  "isValid": true,
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com"
  },
  "daysRemaining": 7,
  "expiresAt": "2025-12-12 19:27:00"
}
```

**If 401 Unauthorized:**
```json
{
  "isValid": false,
  "message": "Invalid session"
}
```

### **Step 4: Check Laravel Log**

Look for these debug messages:
```
[timestamp] local.DEBUG: SessionController: Checking session {"token":"48|GOlgVMHE0RNmH9cz...","sessionId":"48"}
[timestamp] local.DEBUG: SessionController: Session found {"sessionId":"48","user_id":1,"created_at":"1733400000","last_activity":"1733400000"}
```

**If you see:**
```
SessionController: Session not found in database {"sessionId":"48"}
```
→ Session ID 48 tidak ada di database!

---

## 🐛 DEBUGGING

### **Problem 1: Session Not Found**

**Check database:**
```sql
SELECT * FROM sessions WHERE id = 48;
```

**If empty:**
- Session sudah dihapus atau tidak pernah dibuat
- **Solution:** Logout & login lagi

### **Problem 2: created_at is NULL**

**Check database:**
```sql
SELECT id, user_id, created_at, last_activity FROM sessions WHERE id = 48;
```

**If created_at is NULL:**
- Session dibuat sebelum migration
- **Solution:** Logout & login lagi

### **Problem 3: Wrong Session ID**

**Check Laravel log:**
```
SessionController: Checking session {"token":"48|GOlgVMHE0RNmH9cz...","sessionId":"???"}
```

**If sessionId is wrong:**
- Bug di explode logic
- **Solution:** Report ke saya

---

## 🎯 QUICK FIX

**Jika masih error, coba ini:**

1. ✅ **Logout** dari app
2. ✅ **Login** lagi (create fresh session)
3. ✅ **Copy token baru** dari Logcat
4. ✅ **Test** di Postman dengan token baru
5. ✅ **Report** hasil ke saya

---

## 📊 EXPECTED LARAVEL LOG

```
[2025-12-05 19:27:56] local.DEBUG: SessionController: Checking session {"token":"48|GOlgVMHE0RNmH9cz...","sessionId":"48"}
[2025-12-05 19:27:56] local.DEBUG: SessionController: Session found {"sessionId":"48","user_id":1,"created_at":"1733400000","last_activity":"1733400000"}
```

**If you see this:** ✅ Session found, check next step in code

**If you see "Session not found":** ❌ Session 48 tidak ada di database

---

**Test sekarang dan beri tahu hasilnya!**
