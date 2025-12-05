# 🔍 DEBUG SESSION

## 1️⃣ Check Database

Buka MySQL/phpMyAdmin dan run query ini:

```sql
SELECT * FROM sessions WHERE id = 48;
```

Expected result:
```
id: 48
user_id: 1
ip_address: ...
user_agent: ...
last_activity: ...
created_at: ...  ← HARUS ADA!
device_name: ...
device_type: ...
device_id: ...
location: ...
```

**Jika `created_at` = NULL:** Session ini dibuat sebelum migration. Logout & login lagi.

---

## 2️⃣ Test di Postman

### **Endpoint:** GET `/api/session/check`

**Headers:**
```
Authorization: Bearer 48|GOlgVMHE0RNmH9czfV4xcDADt1Kw2pqbiIBGZka1cf4667dc
Accept: application/json
```

**Expected Response (200 OK):**
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
Check response body untuk error message.

---

## 3️⃣ Debug Steps

1. ✅ Check database: `SELECT * FROM sessions WHERE id = 48`
2. ✅ Verify `created_at` field exists and has value
3. ✅ Test di Postman dengan token yang sama
4. ✅ Report hasil ke saya

---

**Kemungkinan masalah:**
- Session `48` tidak ada di database
- `created_at` masih NULL (old session)
- Ada error di SessionController

**Solution:**
- Logout & login lagi untuk create fresh session
