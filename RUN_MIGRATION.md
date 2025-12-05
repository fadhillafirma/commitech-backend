# 🚀 RUN MIGRATION

## ⚠️ CRITICAL: Run migration dulu sebelum test!

Kita sudah tambah field `created_at` ke table `sessions`.

---

## 📝 COMMAND

```bash
cd "c:\Users\user\AndroidStudioProjects\TB PTB\Commitech-backend"
php artisan migrate
```

---

## ✅ EXPECTED OUTPUT

```
Migration table created successfully.
Migrating: 2025_12_05_122200_add_created_at_to_sessions_table
Migrated:  2025_12_05_122200_add_created_at_to_sessions_table (XX.XXms)
```

---

## 🧪 AFTER MIGRATION

1. ✅ **Logout** dari app (clear old session)
2. ✅ **Login** lagi (create new session dengan created_at)
3. ✅ **Kill app**
4. ✅ **Open app** lagi
5. ✅ **Check**: Masih login? ✅

---

**Run migration sekarang!**
