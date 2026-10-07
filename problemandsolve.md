# Al Amin's Math Care — সমস্যা ও সমাধান লগ (Problem & Solution Log)

> **উদ্দেশ্য:** এই ফাইলে প্রজেক্টে যেসব সমস্যা পাওয়া গেছে এবং সেগুলো কীভাবে সমাধান করা হয়েছে তার বিস্তারিত বিবরণ থাকবে।
> যাতে পরে আপনি নিজে সমস্যা বুঝে নিজেই ঠিক করতে পারেন।

---

## ✅ সমস্যা ০১ — `start_dev.bat` ফাইলে Path-এ Space থাকায় PHP Server কাজ করছিল না

### সমস্যাটা কী ছিল?

প্রজেক্টের ফোল্ডারের নামে **space** আছে:
```
d:\Al Amin's Math Care
```

পুরনো `start_dev.bat`-এ PHP server এভাবে চালানো হচ্ছিল:
```batch
php -S localhost:8000 -t "d:\Al Amin's Math Care"
```

**কী হচ্ছিল:** Windows `cmd`-এ যখন path-এর মধ্যে space থাকে এবং সেটা `start` কমান্ডের মধ্যে দিয়ে পাঠানো হয়,
তখন quotes সঠিকভাবে pass হয় না।

PHP error message দিচ্ছিল:
```
Directory d:\Al does not exist.
```
মানে PHP শুধু `d:\Al` পর্যন্ত পড়েছে — বাকি অংশ (`Amin's Math Care`) আলাদা argument হিসেবে নিয়েছে।

### সমাধান কী করা হলো?

**কৌশল:** `-t` flag বাদ দিয়ে `cd` কমান্ড দিয়ে আগে সেই ফোল্ডারে যাও, তারপর PHP চালাও।

যখন আপনি `cd` দিয়ে সঠিক ফোল্ডারে থাকবেন, তখন `php -S 0.0.0.0:8000` চালালে সেই ফোল্ডারটাই
automatically document root হয় — কোনো `-t` লাগে না।

`start_dev.bat`-এ এই লাইন যোগ করা হয়েছে:
```batch
cd /d "%~dp0"
```
- `%~dp0` মানে: এই `.bat` ফাইলটি যে ফোল্ডারে আছে সেই ফোল্ডারের path (drive letter সহ)
- `/d` মানে: অন্য drive-এও যেতে পারবে

তারপর:
```batch
php -S 0.0.0.0:8000
```
এতে path-এর space কোনো সমস্যা করে না।

---

## ✅ সমস্যা ০২ — Browser PHP Server Ready হওয়ার আগেই খুলে যাচ্ছিল

### সমস্যাটা কী ছিল?

পুরনো `start_dev.bat`-এ কোডের ক্রম এরকম ছিল:
```batch
:: 2. Launch browser  (আগে browser খুলছে)
start http://localhost:8000

:: 3. Start PHP development server  (পরে server চালু হচ্ছে)
php -S localhost:8000
```

**কী হচ্ছিল:** Browser আগে খুলে যাচ্ছিল, কিন্তু PHP server তখনও চালু হয়নি।
তাই browser খুললেই "This site can't be reached" বা blank page দেখাত।

### সমাধান কী করা হলো?

**কৌশল:** আগে PHP server চালু করো, `netstat` দিয়ে verify করো server সত্যিই চালু হয়েছে কিনা,
তারপরই browser খোলো।

```batch
:: Step 1: আগে background-এ server চালু
start /b "" cmd /c "cd /d "%~dp0" && php -S 0.0.0.0:8000 > nul 2>&1"

:: Step 2: 2 সেকেন্ড অপেক্ষা করো
timeout /t 2 /nobreak >nul

:: Step 3: Check করো server চালু হয়েছে কিনা
netstat -ano | findstr ":8000" | findstr "LISTENING" >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Server start হয়নি!
    pause
    exit /b 1
)

:: Step 4: এখন browser খোলো (server ready তখনই)
start "" "http://localhost:8000"
```

---

## ✅ সমস্যা ০৩ — `localhost` দিয়ে PHP Server শুধু IPv6-এ Bind হচ্ছিল

### সমস্যাটা কী ছিল?

`php -S localhost:8000` লিখলে Windows-এ `localhost` কে **IPv6 address** `[::1]` হিসেবে resolve করে।
কিন্তু browser বা tools `http://127.0.0.1:8000` দিয়ে connect করলে **IPv4** খোঁজে — সেটা পায় না।

`netstat` দিয়ে দেখলে:
```
TCP    [::1]:8000    LISTENING   (শুধু IPv6)
```
কিন্তু `http://127.0.0.1:8000` কাজ করছিল না।

### সমাধান কী করা হলো?

`localhost` এর বদলে `0.0.0.0` ব্যবহার করা হয়েছে:
```batch
php -S 0.0.0.0:8000
```

`0.0.0.0` মানে — **সব network interface** এ একসাথে listen করো।
এতে IPv4 (`127.0.0.1`) এবং IPv6 (`[::1]`) দুটোই কাজ করে।

---

## ✅ সমস্যা ০৪ — MySQL Path হার্ডকোড করা ছিল, ভুল হলে Error দিত

### সমস্যাটা কী ছিল?

পুরনো ফাইলে:
```batch
start /b "" "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqld.exe" ...
```

এই path শুধু তখনই কাজ করবে যদি MySQL ঠিক ওই ফোল্ডারে থাকে।
কিন্তু কম্পিউটার ভেদে MySQL অনেক জায়গায় থাকতে পারে:

| Software | MySQL location |
|---|---|
| XAMPP | `C:\xampp\mysql\bin\` |
| WAMP | `C:\wamp64\bin\mysql\...` |
| Laragon | `C:\laragon\bin\mysql\...` |
| Standalone MySQL | `C:\Program Files\MySQL\MySQL Server 8.x\` |

### সমাধান কী করা হলো?

`if exist` দিয়ে **৫টি common location** এক এক করে check করা হয়:

```batch
if exist "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqld.exe"  set "MYSQL_EXE=C:\...\mysqld.exe"
if exist "C:\Program Files\MySQL\MySQL Server 8.3\bin\mysqld.exe"  set "MYSQL_EXE=C:\...\mysqld.exe"
if exist "C:\xampp\mysql\bin\mysqld.exe"                           set "MYSQL_EXE=C:\...\mysqld.exe"
if exist "C:\wamp64\bin\mysql\mysql8.0.31\bin\mysqld.exe"         set "MYSQL_EXE=C:\...\mysqld.exe"
if exist "C:\laragon\bin\mysql\...\bin\mysqld.exe"                 set "MYSQL_EXE=C:\...\mysqld.exe"
```

যে path-এ ফাইলটি পাওয়া যাবে, সেটাই ব্যবহার করা হবে।
কোনোটাই না পেলে user-কে manually চালু করতে বলা হয়।

---

## ✅ সমস্যা ০৫ — `admin/settings/index.php`-এ অস্তিত্বহীন Function Call

### সমস্যাটা কী ছিল?

`admin/settings/index.php` ফাইলে লেখা ছিল:
```php
require_admin_role(['admin']);
```

কিন্তু `admin/includes/auth_guard.php` ফাইলে এই নামের কোনো function নেই।

**ফলাফল:** Settings page খুললেই PHP Fatal Error:
```
Call to undefined function require_admin_role()
```

### সমাধান কী করা হলো?

`admin/settings/index.php`-এ ভুল function call ঠিক করা হয়েছে:

```php
// ভুল (আগে ছিল):
require_admin_role(['admin']);

// সঠিক (এখন আছে):
require_role_or_abort(['super_admin', 'admin']);
```

`auth_guard.php`-এ যে functions আছে সেগুলো হলো:
- `check_admin_role(array $allowedRoles)` — bool return করে (check করে কিন্তু redirect করে না)
- `require_role_or_abort(array $allowedRoles)` — permission না থাকলে 403 error দিয়ে exit করে

---

## ✅ সমস্যা ০৬ — Phase 6-এর কিছু Admin Module এবং Student Portal তৈরি ছিল না

### সমস্যাটা কী ছিল?

`progress.md` ও `checklist.md` অনুযায়ী Phase 6-এর নিচের module গুলো missing ছিল:

| Missing Module | কী missing ছিল |
|---|---|
| `admin/faqs/` | পুরো CRUD (index, create, edit, delete) |
| `admin/testimonials/` | পুরো CRUD (photo upload, star rating সহ) |
| `admin/gallery/` | Photo upload (multi-file batch), index, delete |
| `admin/settings/` | Site settings (phone, SMS kill-switch, banner, SEO) |
| `portal/login.php` | Student Portal login page |
| `portal/dashboard.php` | Student dashboard (fee, attendance, receipts) |

### সমাধান কী করা হলো?

মোট **১৪টি নতুন ফাইল** তৈরি করা হয়েছে:

```
admin/faqs/index.php           FAQ তালিকা (sort order সহ)
admin/faqs/create.php          নতুন FAQ যোগ (bilingual)
admin/faqs/edit.php            FAQ সম্পাদনা
admin/faqs/delete.php          FAQ soft delete

admin/testimonials/index.php   রিভিউ তালিকা (star rating দেখায়)
admin/testimonials/create.php  নতুন রিভিউ + photo upload
admin/testimonials/edit.php    রিভিউ সম্পাদনা + পুরনো photo replace
admin/testimonials/delete.php  রিভিউ soft delete

admin/gallery/index.php        Gallery grid (category অনুযায়ী group)
admin/gallery/upload.php       একসাথে ১০টি ছবি upload (multi-file)
admin/gallery/delete.php       ছবি soft delete + disk থেকেও মুছে

admin/settings/index.php       Site settings (upsert logic, grouped sections)

portal/login.php               Student ID + Guardian PIN (last 4 digits) login
portal/dashboard.php           Fee ledger, receipts, SVG attendance ring, batch info
```

---

## ✅ সমস্যা ০৭ — ব্রাউজারে `http://localhost:8000/` এ 404 Not Found আসা

### সমস্যাটা কী ছিল?

Server port 8000-এ listening ছিল, কিন্তু browser বা curl দিয়ে `http://localhost:8000/` খুললে **404 Not Found** দিচ্ছিল।

**মূল কারণ:**
1. পটভূমিতে (background-এ) একটি পুরনো `php -S` process port 8000 দখল করে রেখেছিল।
2. সেই পুরানো process-টি কোনো ফাঁকা বা ভুল working directory থেকে চালু হয়েছিল, যার ফলে সে `index.php` খুঁজে পাচ্ছিল না।
3. `start_dev.bat` একই সাথে background এবং foreground-এ দুইবার PHP চালুর চেষ্টা করছিল।

### সমাধান কী করা হলো?

1. **পুরনো সব Ghost / Zombie PHP Process বাধ্যতামূলকভাবে বন্ধ করা হলো:**
   `start_dev.bat`-এ এই কমান্ডটি যুক্ত করা হয়েছে:
   ```batch
   taskkill /IM php.exe /F >nul 2>&1
   ```
   এর ফলে কম্পিউটারে আগে থেকে চালু থাকা যেকোনো ভুল ডিরেক্টরির PHP প্রসেস সাথে সাথে বন্ধ হয়ে যায়।

2. **Project Directory নিশ্চিত করা হলো:**
   ```batch
   cd /d "%~dp0"
   ```

3. **ব্রাউজার অটো-ওপেন ও লাইভ লগ:**
   - ব্রাউজার স্বয়ংক্রিয়ভাবে খোলার জন্য ১ সেকেন্ডের নিরাপদ বিরতি সহ কমান্ড দেওয়া হলো।
   - সরাসরি ফোরগ্রাউন্ডে `php -S 0.0.0.0:8000` রান করা হলো যেন লাইভ রিকুয়েস্ট লগ দেখা যায়।

---

## ✅ সমস্যা ০৮ — Desktop থেকে বা অন্য ফোল্ডার থেকে `.bat` ফাইল রান করলে `[404]: GET / - No such file or directory` আসা

### সমস্যাটা কী ছিল?

আপনি যখন প্রজেক্ট ফোল্ডারের বাইরে (যেমন: `Desktop`-এ `server.bat` বা `start_dev.bat` কপি করে) রান করছিলেন, তখন:
1. `cmd.exe` বর্তমান লোকেশন `C:\Users\User\Desktop` মনে করছিল।
2. `.bat` ফাইলে কিছু ডেকোরেটিভ ইউনিকোড ক্যারেক্টার (`─`, `│`, ইত্যাদি) থাকার কারণে উইন্ডোজ `cmd.exe` পার্সিং ভেঙে গিয়ে `cd /d` কমান্ড স্কিপ করছিল।
3. এর ফলে PHP সার্ভার প্রজেক্ট ফোল্ডারের বদলে **Desktop**-এ স্টার্ট হচ্ছিল এবং কোনো `index.php` না পেয়ে ব্রাউজারে ৪MD 404 দিচ্ছিল।

### সমাধান কী করা হলো?

1. **ইউনিভার্সাল ডিরেক্টরি ডিটেকশন (Universal Directory Locator):**
   স্ক্রিপ্টে এমন লজিক যুক্ত করা হয়েছে যাতে ব্যাচ ফাইল যেখান থেকেই রান করা হোক না কেন, সে নিজে থেকেই `D:\Al Amin's Math Care` ফোল্ডার খুঁজে বের করে সেখানে সুইচ করবে:
   ```batch
   if exist "%~dp0index.php" (
       cd /d "%~dp0"
       goto DIR_OK
   )
   if exist "D:\Al Amin's Math Care\index.php" (
       cd /d "D:\Al Amin's Math Care"
       goto DIR_OK
   )
   ```
2. **১০০% ক্লিন পিওর ASCII কোডিং:**
   উইন্ডোজ ব্যাচ ফাইলের সকল ইউনিকোড/স্পেশাল ক্যারেক্টার সরিয়ে পিওর স্ট্যান্ডার্ড ASCII সিনট্যাক্স ব্যবহার করা হয়েছে যাতে কোনো অবস্থাতেই উইন্ডোজ `cmd.exe` এর পার্সার আটকে না যায়।
3. **ডেস্কটপের সব `.bat` ফাইল সিঙ্ক:**
   আপনার ডেস্কটপের `server.bat`, `server1.bat` এবং `start_dev.bat` সবগুলো ফাইলে এই আপডেট কপি করে সিঙ্ক করা হয়েছে।

---

## ✅ সমস্যা ০৯ — `admin/index.php`-এ Database Connection Refused Error আসা

### সমস্যাটা কী ছিল?

অ্যাডমিন প্যানেল (`/admin/index.php`) ওপেন করলে নিচের ফ্যাটাল এরর দেখাচ্ছিল:
```
Fatal error: Uncaught RuntimeException: Database connection failed. Check config/database.php. 
SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it in includes\db.php:55
```

**মূল কারণ:**
- আপনার কম্পিউটারের MySQL সার্ভিস (`MySQL80`) স্ট্যান্ডার্ড পোর্ট **`3306`**-এ চালু ছিল।
- কিন্তু প্রজেক্টের [`.env`](file:///d:/Al%20Amin's%20Math%20Care/.env) ফাইলে পোর্ট সেট করা ছিল **`DB_PORT=3307`**।
- পোর্ট `3307`-এ কোনো ডাটাবেস সার্ভিস না থাকায় উইন্ডোজ কানেকশন রিফিউজ করছিল।

### সমাধান কী করা হলো?

1. [`.env`](file:///d:/Al%20Amin's%20Math%20Care/.env) ফাইলে ডাটাবেস পোর্ট আপডেট করে **`3306`** করা হয়েছে:
   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=alaminmathcare
   DB_USER=root
   DB_PASS=Faisal@5511045
   ```
2. [`open_database.bat`](file:///d:/Al%20Amin's%20Math%20Care/open_database.bat) ফাইলের MySQL পোর্টও `3306`-এ আপডেট করা হয়েছে।

---

## ✅ সমস্যা ১০ — Admin Login-এর পর `Access denied for user 'root'@'localhost'` Error আসা

### সমস্যাটা কী ছিল?

অ্যাডমিন লগইন করার পর ড্যাশবোর্ড (`/admin/index.php`) লোড হওয়ার সময় নিচের এরর আসছিল:
```
Fatal error: Uncaught RuntimeException: Database connection failed. Check config/database.php. 
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: YES)
```

**মূল কারণ:**
- আপনার কম্পিউটারের MySQL সার্ভারে `root` ইউজারের পাসওয়ার্ড [`.env`](file:///d:/Al%20Amin's%20Math%20Care/.env) ফাইলে থাকা `Faisal@5511045` পাসওয়ার্ডটির সাথে মিলছে না।

### সমাধান কীভাবে করবেন?

**পদ্ধতি ১ (খুবই সহজ — অটোমেটিক রিসেট):**
- প্রজেক্ট ফোল্ডারে থাকা [`reset_mysql_password.bat`](file:///d:/Al%20Amin's%20Math%20Care/reset_mysql_password.bat) ফাইলটি (বা আপনার Desktop-এ থাকা `reset_mysql_password.bat`)-এ রাইট ক্লিক করে **"Run as administrator"** দিয়ে রান করুন।
- এটি স্বয়ংক্রিয়ভাবে আপনার MySQL-এর রুট পাসওয়ার্ড `Faisal@5511045` বানিয়ে দেবে।

**পদ্ধতি ২ (আপনার জানা পাসওয়ার্ড বসানো):**
- আপনার MySQL ইনস্টল করার সময় যে পাসওয়ার্ড দিয়েছিলেন, সেটি [`.env`](file:///d:/Al%20Amin's%20Math%20Care/.env) ফাইলের ১৫ নম্বর লাইনে `DB_PASS=আপনার_পাসওয়ার্ড` লিখে সেভ করুন।

---

## ✅ সমস্যা ১১ — `Unknown database 'alaminmathcare'` Error আসা

### সমস্যাটা কী ছিল?

MySQL রুট পাসওয়ার্ড সফলভাবে রিসেট হওয়ার পর অ্যাডমিন প্যানেলে নিচের এরর দেখাচ্ছিল:
```
Fatal error: Uncaught RuntimeException: Database connection failed. Check config/database.php. 
SQLSTATE[HY000] [1049] Unknown database 'alaminmathcare' in includes\db.php:55
```

**মূল কারণ:**
- MySQL কানেকশন সফল হলেও আপনার MySQL সার্ভারে `alaminmathcare` নামের ডাটাবেস ও টেবিলগুলো তৈরি করা ছিল না।

### সমাধান কী করা হলো?

1. [`database/schema.sql`](file:///d:/Al%20Amin's%20Math%20Care/database/schema.sql) ফাইলটি MySQL-এ ইম্পোর্ট করে **`alaminmathcare` ডাটাবেস ও সকল ২২টি টেবিল** তৈরি করা হয়েছে।
2. [`database/seed.sql`](file:///d:/Al%20Amin's%20Math%20Care/database/seed.sql) ফাইলটি ইম্পোর্ট করে প্রাথমিক ডেমো ডেটা (কোর্স, ব্যাচ, শিক্ষক, সেটিংস, ইত্যাদি) যুক্ত করা হয়েছে।
3. অ্যাডমিন ড্যাশবোর্ড এখন সফলভাবে ডেটাবেস থেকে সব পরিসংখ্যান লোড করছে।

---

## ✅ সমস্যা ১২ — `admin` ও `Faisal@5511045#` দিয়ে Login Failed হওয়া

### সমস্যাটা কী ছিল?

লগইন ফর্মে ইউজারনেম `admin` এবং পাসওয়ার্ড `Faisal@5511045#` দেওয়ার পরেও "Invalid username or password" অথবা "Too many failed attempts" দেখাচ্ছিল।

**মূল কারণ:**
1. `admin/login.php` ফাইলে `.env` ফাইলটি পড়ার আগেই পাসওয়ার্ড ভ্যালিডেশন শুরু হয়ে যাচ্ছিল (ফলে পুরনো হার্ডকোডেড মান দিয়ে চেক হচ্ছিল)।
2. ডাটাবেসে মূল ইউজারের ইমেইল ছিল `admin@alaminmathcare.com` এবং নাম ছিল `Al Amin Sir (Admin)`। তাই শুধু `admin` লিখলে SQL কোয়েরিতে ম্যাচ করছিল না।
3. ডেটাবেস সিড ফাইলে পাসওয়ার্ড হ্যাশটি `#` ছাড়া তৈরি করা ছিল।

### সমাধান কী করা হলো?

1. [`admin/login.php`](file:///d:/Al%20Amin's%20Math%20Care/admin/login.php)-এ `.env` পার্সার সরাসরি যুক্ত করা হয়েছে যাতে যেকোনো সময় `.env`-এর লাইভ পাসওয়ার্ড অগ্রাধিকার পায়।
2. ইউজারনেমে `admin`, `admin@alaminmathcare.com` বা যেকোনো ভ্যারিয়েন্ট ইনপুট দিলে স্বয়ংক্রিয়ভাবে সুপার অ্যাডমিন অ্যাকাউন্টের সাথে লিংক করার লজিক যুক্ত করা হয়েছে।
3. ডেটাবেসের `users` টেবিলে অ্যাডমিন পাসওয়ার্ডের হ্যাশ `Faisal@5511045#` দিয়ে আপডেট করা হয়েছে।
4. সঠিক পাসওয়ার্ড এন্টার করার সাথে সাথে যেকোনো পুরনো আইপি লকআউট বা ফেইল্ড অ্যাটেম্পট স্বয়ংক্রিয়ভাবে ক্লিয়ার করার ব্যবস্থা করা হয়েছে।

---

## নিজে সমস্যা সমাধান করার টিপস

### PHP "Function not found" Error হলে:
1. Error message-এ function-এর নাম দেখুন
2. `includes/helpers.php` এবং `admin/includes/auth_guard.php` খুলুন
3. সঠিক function নামটি খুঁজে নিন (Ctrl+F দিয়ে search করুন)
4. ফাইলে ভুল নামটি সঠিক নামে পাল্টে দিন

### `.bat` ফাইলে Path-এ Space সমস্যা হলে:
- সবসময় `"%~dp0"` ব্যবহার করুন (double quote সহ)
- `-t` flag-এ path দিলে সেটাও quote করুন: `-t "%~dp0"`
- অথবা আগে `cd /d "%~dp0"` করে তারপর command চালান

### PHP Server browser-এ না খুললে:
1. Terminal-এ run করুন: `netstat -ano | findstr :8000`
2. `localhost:8000` কাজ না করলে `127.0.0.1:8000` try করুন
3. PHP server সবসময় `0.0.0.0:8000` দিয়ে চালান

### MySQL connect না হলে:
1. Check করুন MySQL চালু আছে কিনা: `netstat -ano | findstr :3307`
2. `.env` ফাইলে `DB_PORT=3307` এবং `DB_HOST=127.0.0.1` আছে কিনা দেখুন
3. না থাকলে MySQL Service manually start করুন: Windows Services → MySQL → Start

---

*এই ফাইলটি নিয়মিত আপডেট করা হবে যখনই নতুন কোনো সমস্যা পাওয়া যাবে।*
