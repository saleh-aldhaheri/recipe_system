# شرح MVC مع AJAX - بالعربية

## التغييرات التي تمت

### 1. إنشاء View Helper Functions (`core/helper.php`)

تم إضافة الدوال التالية:

#### `isAjaxRequest($request)`
```php
// تتحقق إذا كان الطلب AJAX أم لا
// تبحث عن header: X-Requested-With: XMLHttpRequest
```

#### `view($view, $data, $layout)`
```php
// تحميل View مع Layout
// مثال: view('items.index', ['items' => $items])
```

#### `e($string)`
```php
// Escape HTML (منع XSS)
// مثال: e($userInput)
```

#### `url($path)` و `asset($path)`
```php
// إنشاء URLs و Asset URLs
```

---

### 2. إنشاء Layout System

**الملف:** `app/Views/layouts/main.php`

- Layout رئيسي يحتوي على:
  - HTML Structure
  - Navigation
  - Container للـ Content
  - JavaScript includes

**كيف يعمل:**
```php
// في Controller
$html = view('items.index', [
    'title' => 'إدارة العناصر',
    'content' => '...' // يتم إضافته تلقائياً
]);
// النتيجة: Layout + View Content
```

---

### 3. تحويل HTML إلى PHP Views

**البنية الجديدة:**
```
app/Views/
├── layouts/
│   └── main.php          ← Layout رئيسي
├── home/
│   └── index.php         ← الصفحة الرئيسية
├── items/
│   └── index.php         ← صفحة العناصر
└── ...
```

**مثال:** `app/Views/items/index.php`
```php
<!-- Page Header -->
<div class="page-header">
    <h1>إدارة العناصر</h1>
    <button onclick="openItemModal()">+ إضافة عنصر جديد</button>
</div>

<!-- يمكن استخدام PHP variables -->
<?php if (isset($items)): ?>
    <?php foreach ($items as $item): ?>
        <!-- Display item -->
    <?php endforeach; ?>
<?php endif; ?>
```

---

### 4. تعديل Controllers لدعم Views وAJAX

**قبل التعديل:**
```php
public function index() {
    // دائماً يرجع JSON
    return jsonResponse($response, ['data' => $items]);
}
```

**بعد التعديل:**
```php
public function index(Request $request, Response $response) {
    $items = Item::all();
    
    // التحقق من نوع الطلب
    if (isAjaxRequest($request)) {
        // AJAX request → JSON
        return jsonResponse($response, ['data' => $items]);
    } else {
        // Normal request → View (HTML)
        $html = view('items.index', [
            'title' => 'إدارة العناصر',
            'items' => $items
        ]);
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}
```

---

### 5. إنشاء Entry Points (نقاط الدخول)

**الملفات الجديدة:**
- `public/index.php` - الصفحة الرئيسية
- `public/items.php` - صفحة العناصر
- `public/recipes.php` - صفحة الوصفات (سيتم إنشاؤها)
- `public/ingredients.php` - صفحة المكونات (سيتم إنشاؤها)

**مثال:** `public/items.php`
```php
<?php
// تحميل التطبيق
require BASE.'bootstrap.php';

// إنشاء Request/Response
$request = createRequestFromGlobals();
$response = createResponse();

// استدعاء Controller
$controller = new ItemsController();
$response = $controller->index($request, $response);

// إخراج النتيجة
echo $response->getBody();
```

---

## كيف يعمل AJAX مع MVC؟

### التدفق الكامل:

```
┌─────────────────────────────────────────────────┐
│ 1. المستخدم يفتح المتصفح                        │
│    GET http://localhost:8881/items.php          │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 2. الخادم يستقبل الطلب                          │
│    public/items.php                             │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 3. items.php يستدعي Controller                  │
│    ItemsController::index()                     │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 4. Controller يتحقق من نوع الطلب                │
│    isAjaxRequest($request) ?                    │
│    → false (ليس AJAX)                           │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 5. Controller يرجع View (HTML)                  │
│    view('items.index', ['items' => $items])      │
│    → Layout + View Content                      │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 6. المستخدم يرى الصفحة (HTML)                   │
│    مع JavaScript loaded                         │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 7. JavaScript يرسل AJAX Request                 │
│    fetch('items.php', {                          │
│      headers: {                                  │
│        'X-Requested-With': 'XMLHttpRequest'     │
│      }                                           │
│    })                                            │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 8. Controller يستقبل AJAX Request               │
│    isAjaxRequest($request) ?                    │
│    → true (AJAX!)                                │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 9. Controller يرجع JSON                          │
│    return jsonResponse($response, [               │
│      'success' => true,                          │
│      'data' => $items                            │
│    ])                                            │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 10. JavaScript يستقبل JSON                      │
│     response.json()                              │
│     → updatePage(response.data)                  │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 11. الصفحة تتحدث بدون إعادة تحميل               │
│     (AJAX Magic!)                                │
└─────────────────────────────────────────────────┘
```

---

## الفرق بين الطريقتين

### الطريقة القديمة (API فقط):
```
Frontend (HTML) → AJAX → Backend (JSON فقط)
```

**المشاكل:**
- ❌ لا SEO (الصفحات فارغة)
- ❌ بطء في التحميل الأولي
- ❌ يحتاج JavaScript دائماً

### الطريقة الجديدة (MVC + AJAX):
```
Frontend (PHP View) → AJAX → Backend (JSON أو HTML)
```

**المميزات:**
- ✅ SEO Friendly (HTML كامل)
- ✅ تحميل أولي سريع
- ✅ يعمل بدون JavaScript (للقراءة)
- ✅ AJAX للتفاعل (للتحديثات)

---

## مثال عملي

### عندما تفتح `items.php`:

**1. الطلب الأول (Normal Request):**
```http
GET /items.php
Headers: (لا يوجد X-Requested-With)
```

**النتيجة:**
```html
<!DOCTYPE html>
<html>
<head>...</head>
<body>
    <nav>...</nav>
    <div class="container">
        <h1>إدارة العناصر</h1>
        <table>
            <!-- البيانات من PHP -->
        </table>
    </div>
    <script src="js/items.js"></script>
</body>
</html>
```

**2. AJAX Request (من JavaScript):**
```http
GET /items.php?page=2&search=test
Headers: X-Requested-With: XMLHttpRequest
```

**النتيجة:**
```json
{
    "success": true,
    "data": [...],
    "pagination": {...}
}
```

---

## الملفات المطلوبة للتشغيل

### 1. Entry Points (في `public/`):
- ✅ `index.php` - الصفحة الرئيسية
- ✅ `items.php` - صفحة العناصر
- ⏳ `recipes.php` - صفحة الوصفات (قريباً)
- ⏳ `ingredients.php` - صفحة المكونات (قريباً)

### 2. Views (في `app/Views/`):
- ✅ `layouts/main.php` - Layout رئيسي
- ✅ `home/index.php` - الصفحة الرئيسية
- ✅ `items/index.php` - صفحة العناصر
- ⏳ `recipes/calendar.php` - تقويم الوصفات
- ⏳ `ingredients/index.php` - صفحة المكونات

### 3. Controllers (معدلة):
- ✅ `ItemsController` - يدعم Views وAJAX
- ⏳ `RecipesController` - يحتاج تعديل
- ⏳ `IngredientsController` - يحتاج تعديل

---

## كيفية التشغيل

```bash
# من المجلد الرئيسي
cd /home/saleh/Desktop/PHP/Projects/xlsx

# تشغيل الخادم
php -S localhost:8881 -t public

# افتح المتصفح
http://localhost:8881/index.php
http://localhost:8881/items.php
```

---

## ملاحظات مهمة

1. **AJAX Detection:**
   - JavaScript يرسل header: `X-Requested-With: XMLHttpRequest`
   - Controller يكتشفه بـ `isAjaxRequest()`

2. **URLs:**
   - في MVC Mode: استخدم `items.php` مباشرة
   - في API Mode: استخدم `/items` route

3. **JavaScript:**
   - تم تعديل `api.js` لإضافة AJAX header تلقائياً
   - تم تعديل `items.js` لدعم MVC mode

4. **Routes:**
   - Routes في `app/routes.php` لا تزال تعمل للـ API
   - Entry Points (`*.php`) تعمل للـ Views

---

## الخطوات التالية

1. ✅ Items Page - مكتمل
2. ⏳ Recipes Page - يحتاج إنشاء
3. ⏳ Ingredients Page - يحتاج إنشاء
4. ⏳ تعديل باقي Controllers

هل تريد المتابعة مع باقي الصفحات؟

