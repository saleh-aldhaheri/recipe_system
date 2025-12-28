# دليل حل المشاكل - Troubleshooting Guide

## المشكلة 1: خطأ Slim - Route لا يرجع Response

### الخطأ:
```
Return value must be of type Psr\Http\Message\ResponseInterface, null returned
```

### السبب:
في Slim Framework، **كل route يجب أن يرجع Response object**. لا يمكن استخدام `echo` فقط.

### ❌ الكود الخاطئ:
```php
$app->get('/', function() {  
    echo "welcome";  // خطأ! لا يرجع Response
});
```

### ✅ الكود الصحيح:
```php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/', function(Request $request, Response $response) {  
    $response->getBody()->write("welcome");
    return $response;  // مهم جداً! يجب إرجاع Response
});
```

### القاعدة الذهبية:
- **كل route في Slim يجب أن:**
  1. يأخذ `Request` و `Response` كـ parameters
  2. يكتب البيانات في `$response->getBody()->write()`
  3. **يرجع `$response`** في النهاية

---

## المشكلة 2: Eloquent لا ينشئ Items

### الأسباب المحتملة:

#### 1. ملف `.env` غير موجود أو غير صحيح
**الحل:** أنشئ ملف `.env` في جذر المشروع:
```env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_database_name
DB_USER=your_username
DB_PASSWORD=your_password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
DB_PREFIX=
```

#### 2. قاعدة البيانات غير موجودة
**الحل:** أنشئ قاعدة البيانات:
```sql
CREATE DATABASE your_database_name;
```

#### 3. الجداول غير موجودة
**الحل:** استورد الـ schema:
```bash
mysql -u your_username -p your_database_name < database/schema.sql
```

#### 4. الكود يعمل قبل تحميل Routes
**المشكلة:** وضع `Item::create()` مباشرة في `index.php` قد يعمل قبل تحميل الـ database connection.

**الحل:** استخدم route لاختبار Eloquent:
```php
// في app/routes.php
$app->get('/test-eloquent', function(Request $request, Response $response) {
    try {
        $item = Item::create([
            'name' => 'Test Item',
            'short_name' => 'TEST',
            'balance' => 20,
            'unit' => 'kg'
        ]);
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'item' => $item
        ], JSON_PRETTY_PRINT));
        
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $response->getBody()->write(json_encode([
            'error' => $e->getMessage()
        ], JSON_PRETTY_PRINT));
        return $response->withStatus(500);
    }
});
```

ثم افتح: `http://localhost:8881/test-eloquent`

---

## كيفية اختبار Eloquent

### الطريقة 1: استخدام Route (موصى به)
```bash
# افتح المتصفح أو استخدم curl
curl http://localhost:8881/test-eloquent
```

### الطريقة 2: استخدام Controller
```php
// في ItemsController
public function store(Request $request, Response $response): Response
{
    $data = json_decode($request->getBody()->getContents(), true);
    
    $item = Item::create([
        'name' => $data['name'],
        'short_name' => $data['short_name'],
        'balance' => $data['balance'],
        'unit' => $data['unit'],
    ]);
    
    $response->getBody()->write(json_encode($item, JSON_PRETTY_PRINT));
    return $response->withStatus(201);
}
```

### الطريقة 3: استخدام cURL
```bash
curl -X POST http://localhost:8881/items \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Item",
    "short_name": "TEST",
    "balance": 100,
    "unit": "KG"
  }'
```

---

## خطوات التحقق من الاتصال بالـ Database

### 1. تحقق من ملف `.env`
```bash
cat .env
```

### 2. تحقق من الاتصال
أنشئ route للتحقق:
```php
$app->get('/test-db', function(Request $request, Response $response) {
    try {
        $capsule = \Illuminate\Database\Capsule\Manager::connection();
        $capsule->getPdo();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Database connected successfully!'
        ], JSON_PRETTY_PRINT));
        
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_PRETTY_PRINT));
        
        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'application/json');
    }
});
```

### 3. تحقق من الجداول
```sql
SHOW TABLES;
SELECT * FROM items;
```

---

## ملخص القواعد المهمة في Slim

1. **كل route يجب أن يرجع Response**
2. **استخدم `$response->getBody()->write()` بدلاً من `echo`**
3. **استخدم `return $response` في نهاية كل route**
4. **استخدم try-catch للتعامل مع الأخطاء**
5. **استخدم routes لاختبار Eloquent، لا تضع الكود مباشرة في index.php**

---

## الروابط المفيدة

- **الرئيسية:** `http://localhost:8881/`
- **اختبار Eloquent:** `http://localhost:8881/test-eloquent`
- **جميع Items:** `http://localhost:8881/items`
- **إنشاء Item:** `POST http://localhost:8881/items`

