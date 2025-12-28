<!-- Page Header -->
<div class="page-header">
    <h1>إدارة العناصر</h1>
    <button class="btn btn-primary" onclick="openItemModal()">+ إضافة عنصر جديد</button>
</div>

<!-- Search Box -->
<div class="search-box">
    <input type="text" id="searchInput" placeholder="ابحث عن العناصر بالاسم أو الاسم المختصر..." onkeyup="handleSearch()">
</div>

<!-- Items Table -->
<div class="card">
    <div id="itemsTableContainer">
        <div class="loading">
            <div class="spinner"></div>
            <p>جاري تحميل العناصر...</p>
        </div>
    </div>
</div>

<!-- Pagination -->
<div class="pagination" id="pagination"></div>

<!-- Item Modal (for Create/Edit) -->
<div id="itemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">إضافة عنصر جديد</h2>
            <button class="close" onclick="closeItemModal()">&times;</button>
        </div>
        <form id="itemForm" onsubmit="saveItem(event)">
            <input type="hidden" id="itemId">
            
            <div class="form-group">
                <label for="itemName">الاسم *</label>
                <input type="text" id="itemName" required minlength="3" maxlength="255">
            </div>

            <div class="form-group">
                <label for="itemShortName">الاسم المختصر *</label>
                <input type="text" id="itemShortName" required maxlength="50">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="itemBalance">الرصيد *</label>
                    <input type="number" id="itemBalance" step="0.001" min="0" required>
                </div>

                <div class="form-group">
                    <label for="itemUnit">الوحدة *</label>
                    <input type="text" id="itemUnit" required maxlength="20">
                </div>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" class="btn btn-secondary" onclick="closeItemModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ العنصر</button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript will be loaded via layout -->

