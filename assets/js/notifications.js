// Notifikasi Pesanan Baru untuk Kasir/Admin
let lastNotifCount = 0;
let notifSound = null;

// Inisialisasi suara notifikasi (opsional)
function initNotifSound() {
    // Buat audio context untuk suara notifikasi
    // Menggunakan Web Audio API untuk suara sederhana
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        const audioContext = new AudioContext();
        
        notifSound = function() {
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        };
    } catch (e) {
        console.log('Audio notification not supported');
    }
}

// Fungsi untuk load notifikasi
function loadNotifications() {
    $.ajax({
        url: '/api/get_notifications.php',
        type: 'GET',
        dataType: 'json',
        timeout: 5000, // Timeout 5 detik
        cache: false,
        success: function(response) {
            console.log('Notifikasi loaded:', response); // Debug
            const count = response.count;
            const notifications = response.notifications;
            
            // Update badge count
            if (count > 0) {
                $('#notif-count').text(count).show();
                $('#notif-header').text(count + ' Pesanan Baru');
                
                // Play sound jika ada pesanan baru
                if (count > lastNotifCount && lastNotifCount > 0) {
                    if (notifSound) {
                        notifSound();
                    }
                    // Tampilkan browser notification
                    showBrowserNotification(count);
                }
                
                lastNotifCount = count;
                
                // Render list notifikasi
                let html = '';
                notifications.forEach(function(notif) {
                    html += `
                        <a href="pesanan_detail.php?id=${notif.id}" class="dropdown-item">
                            <i class="fas fa-shopping-cart mr-2"></i> ${notif.kode}
                            <span class="float-right text-muted text-sm">${notif.time}</span>
                            <br>
                            <small class="text-muted">
                                ${notif.pelanggan} - Rp ${notif.total} (${notif.metode})
                            </small>
                        </a>
                        <div class="dropdown-divider"></div>
                    `;
                });
                $('#notif-list').html(html);
                
                // Animasi bell icon
                $('#notif-bell i').addClass('fa-shake');
                setTimeout(function() {
                    $('#notif-bell i').removeClass('fa-shake');
                }, 1000);
                
            } else {
                $('#notif-count').hide();
                $('#notif-header').text('0 Pesanan Baru');
                $('#notif-list').html(`
                    <a href="#" class="dropdown-item text-center text-muted">
                        <small>Tidak ada pesanan baru</small>
                    </a>
                `);
                lastNotifCount = 0;
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading notifications:', status, error);
            console.error('Response:', xhr.responseText);
        }
    });
}

// Browser notification
function showBrowserNotification(count) {
    if ("Notification" in window && Notification.permission === "granted") {
        new Notification("Pesanan Baru!", {
            body: `Ada ${count} pesanan baru yang menunggu konfirmasi`,
            icon: 'assets/logo.png',
            badge: 'assets/logo.png'
        });
    }
}

// Request notification permission
function requestNotificationPermission() {
    if ("Notification" in window && Notification.permission === "default") {
        Notification.requestPermission();
    }
}

// Inisialisasi saat dokumen ready
$(document).ready(function() {
    // Cek apakah user adalah kasir atau admin
    if ($('#notif-bell').length > 0) {
        // Load notifikasi pertama kali
        loadNotifications();
        
        // Auto refresh setiap 1 menit (60 detik) - lebih hemat resource
        setInterval(loadNotifications, 60000);
        
        // Inisialisasi suara
        initNotifSound();
        
        // Request browser notification permission
        requestNotificationPermission();
        
        // Refresh saat dropdown dibuka
        $('#notif-bell').on('click', function() {
            loadNotifications();
        });
    }
});

// CSS Animation untuk shake effect
const style = document.createElement('style');
style.textContent = `
    @keyframes fa-shake {
        0% { transform: rotate(0deg); }
        10% { transform: rotate(14deg); }
        20% { transform: rotate(-8deg); }
        30% { transform: rotate(14deg); }
        40% { transform: rotate(-4deg); }
        50% { transform: rotate(10deg); }
        60% { transform: rotate(0deg); }
        100% { transform: rotate(0deg); }
    }
    .fa-shake {
        animation: fa-shake 0.5s ease-in-out;
    }
`;
document.head.appendChild(style);
