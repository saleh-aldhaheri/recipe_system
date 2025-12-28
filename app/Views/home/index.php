<!-- Welcome Section -->
<div class="welcome-section">
    <h1>نظام إدارة الوصفات</h1>
    <p>إدارة وصفاتك وعناصرك ومكوناتك بسهولة</p>
</div>

<!-- Features -->
<div class="features">
    <div class="feature-card">
        <h3>📦 إدارة العناصر</h3>
        <p>إنشاء وتحديث وإدارة عناصر المخزون. تتبع الأرصدة والوحدات لكل عنصر.</p>
        <a href="<?= url('items.php') ?>" class="btn btn-primary">انتقل إلى العناصر</a>
    </div>

    <div class="feature-card">
        <h3>📅 تقويم الوصفات</h3>
        <p>عرض الوصفات في شكل تقويم. انقر على أي يوم لإدارة الوصفات واستيراد من ملفات Excel.</p>
        <a href="<?= url('recipes.php') ?>" class="btn btn-primary">انتقل إلى التقويم</a>
    </div>

    <div class="feature-card">
        <h3>🥘 إدارة المكونات</h3>
        <p>إنشاء وإدارة مكونات الوصفات. تتبع أرصدة العناصر تلقائياً عند استخدام المكونات.</p>
        <a href="<?= url('ingredients.php') ?>" class="btn btn-primary">انتقل إلى المكونات</a>
    </div>
</div>

<!-- Quick Info -->
<div class="card" style="margin-top: 3rem;">
    <h2 style="color: #2c3e50; margin-bottom: 1rem;">كيف يعمل النظام</h2>
    <div style="line-height: 2;">
        <p><strong>1. إدارة العناصر:</strong> أولاً، أضف عناصر المخزون مع أرصدتها ووحداتها.</p>
        <p><strong>2. إنشاء الوصفات:</strong> استخدم التقويم لإنشاء وصفات لتواريخ محددة. أضف مكونات لكل وصفة.</p>
        <p><strong>3. تتبع الاستخدام:</strong> عند إنشاء أو تحديث المكونات، يخصم النظام تلقائياً الكميات من أرصدة العناصر.</p>
        <p><strong>4. استيراد الوصفات:</strong> استورد ملفات Excel متعددة دفعة واحدة لإضافة الوصفات والمكونات بسرعة.</p>
    </div>
</div>

<style>
.welcome-section {
    text-align: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    margin-bottom: 3rem;
}
.welcome-section h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
}
.welcome-section p {
    font-size: 1.2rem;
    opacity: 0.9;
}
.features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}
.feature-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}
.feature-card:hover {
    transform: translateY(-5px);
}
.feature-card h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}
.feature-card p {
    color: #7f8c8d;
    margin-bottom: 1.5rem;
}
</style>

