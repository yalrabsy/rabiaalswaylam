// تأكيد الحذف
function confirmDelete(message = 'هل أنت متأكد من الحذف؟') {
    return confirm(message);
}

// معاينة الصورة قبل الرفع
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // إنشاء عنصر الصورة إذا لم يكن موجوداً
            let preview = document.getElementById('image-preview');
            if (!preview) {
                preview = document.createElement('img');
                preview.id = 'image-preview';
                preview.style.cssText = 'max-width: 200px; margin-top: 10px; border-radius: 8px;';
                input.parentElement.appendChild(preview);
            }
            preview.src = e.target.result;
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// إضافة مستمع للصور
document.addEventListener('DOMContentLoaded', function() {
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            previewImage(this);
        });
    });
});

// تحديث حالة الطلب
function updateOrderStatus(orderId, status) {
    if (!confirm('هل تريد تحديث حالة الطلب؟')) {
        return false;
    }
    
    fetch('update-order-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_id: orderId,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ: ' + data.message);
        }
    });
}

// فلترة الجداول
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    const filter = input.value.toUpperCase();
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell) {
                const text = cell.textContent || cell.innerText;
                if (text.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}

// تصدير الجدول إلى CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length - 1; j++) { // تخطي عمود الإجراءات
            row.push(cols[j].innerText);
        }
        
        csv.push(row.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// طباعة الصفحة
function printPage() {
    window.print();
}

// رسم بياني بسيط للمبيعات (يمكن استخدام Chart.js للمزيد)
function drawSimpleChart(canvasId, data) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    const maxValue = Math.max(...data);
    const barWidth = width / data.length;
    
    ctx.clearRect(0, 0, width, height);
    
    data.forEach((value, index) => {
        const barHeight = (value / maxValue) * height;
        const x = index * barWidth;
        const y = height - barHeight;
        
        ctx.fillStyle = '#2563eb';
        ctx.fillRect(x, y, barWidth - 5, barHeight);
        
        // عرض القيمة
        ctx.fillStyle = '#000';
        ctx.font = '12px Arial';
        ctx.fillText(value, x + 5, y - 5);
    });
}

// رسائل Toast
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 15px 30px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideDown 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideUp 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// تحميل الإحصائيات بشكل ديناميكي
async function loadStats() {
    try {
        const response = await fetch('api/get-stats.php');
        const data = await response.json();
        
        // تحديث البطاقات
        if (data.orders) {
            document.querySelector('.stat-card:nth-child(1) h3').textContent = data.orders;
        }
        if (data.products) {
            document.querySelector('.stat-card:nth-child(2) h3').textContent = data.products;
        }
        // ... وهكذا
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// تفعيل الرسوم المتحركة عند التمرير
function animateOnScroll() {
    const elements = document.querySelectorAll('.admin-card, .stat-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease';
            }
        });
    });
    
    elements.forEach(el => observer.observe(el));
}

// تهيئة عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // تفعيل الرسوم المتحركة
    animateOnScroll();
    
    // إخفاء الرسائل تلقائياً بعد 5 ثواني
    const messages = document.querySelectorAll('.message');
    messages.forEach(msg => {
        setTimeout(() => {
            msg.style.animation = 'slideUp 0.3s ease';
            setTimeout(() => msg.remove(), 300);
        }, 5000);
    });
});

// أنيميشن CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideDown {
        from {
            transform: translateX(-50%) translateY(-100%);
            opacity: 0;
        }
        to {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideUp {
        from {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        to {
            transform: translateX(-50%) translateY(-100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);