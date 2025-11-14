// إضافة منتج إلى السلة
async function addToCart(productId) {
    try {
        const response = await fetch('api/add-to-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ product_id: productId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('تم إضافة المنتج إلى السلة', 'success');
            updateCartCount();
        } else {
            if (data.message === 'not_logged_in') {
                window.location.href = 'login.php';
            } else {
                showNotification(data.message || 'حدث خطأ', 'error');
            }
        }
    } catch (error) {
        showNotification('حدث خطأ في الاتصال', 'error');
    }
}

// تحديث عدد المنتجات في السلة
async function updateCartCount() {
    try {
        const response = await fetch('api/cart-count.php');
        const data = await response.json();
        
        const cartBadge = document.querySelector('.cart-badge');
        if (cartBadge) {
            cartBadge.textContent = data.count;
            if (data.count > 0) {
                cartBadge.style.display = 'flex';
            } else {
                cartBadge.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
}

// عرض إشعار
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 15px 30px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 1000;
        animation: slideDown 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideUp 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// التحقق من صحة النموذج
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = 'red';
            
            // إزالة التحديد الأحمر عند الكتابة
            input.addEventListener('input', function() {
                this.style.borderColor = '';
            });
        }
    });
    
    if (!isValid) {
        showNotification('يرجى ملء جميع الحقول المطلوبة', 'error');
    }
    
    return isValid;
}

// معاينة الصورة قبل الرفع
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// تأكيد الحذف
function confirmDelete(message = 'هل أنت متأكد من الحذف؟') {
    return confirm(message);
}

// تحديث الكمية في السلة
function updateQuantity(cartId, quantity) {
    if (quantity < 1) return;
    
    fetch('api/update-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ cart_id: cartId, quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification(data.message || 'حدث خطأ', 'error');
        }
    })
    .catch(error => {
        showNotification('حدث خطأ في الاتصال', 'error');
    });
}

// البحث المباشر
let searchTimeout;
function liveSearch(query) {
    clearTimeout(searchTimeout);
    
    if (query.length < 2) {
        document.getElementById('search-results').innerHTML = '';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch(`api/search.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                displaySearchResults(data.results);
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    }, 300);
}

function displaySearchResults(results) {
    const container = document.getElementById('search-results');
    
    if (results.length === 0) {
        container.innerHTML = '<p>لا توجد نتائج</p>';
        return;
    }
    
    let html = '<ul class="search-results-list">';
    results.forEach(product => {
        html += `
            <li>
                <a href="product.php?id=${product.id}">
                    <img src="${product.image || 'assets/images/no-image.jpg'}" alt="${product.name}">
                    <div>
                        <h4>${product.name}</h4>
                        <p>${product.price} ر.س</p>
                    </div>
                </a>
            </li>
        `;
    });
    html += '</ul>';
    
    container.innerHTML = html;
}

// تهيئة الصفحة عند التحميل
document.addEventListener('DOMContentLoaded', function() {
    // تحديث عدد السلة
    updateCartCount();
    
    // إضافة أنيميشن للعناصر عند الظهور
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease';
            }
        });
    });
    
    document.querySelectorAll('.product-card, .category-card').forEach(el => {
        observer.observe(el);
    });
});

// إضافة أنيميشن CSS
const style = document.createElement('style');
style.textContent = `
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
`;
document.head.appendChild(style);