<?php
class Notification {
    public static function show($message, $type = 'success') {
        $icon = $type === 'success' ? '✓' : '✕';
        $color = $type === 'success' ? '#4CAF50' : '#f44336';
        ?>
        <div class="notification" style="
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background-color: white;
            border-left: 4px solid <?php echo $color; ?>;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 300px;
            max-width: 400px;
            animation: slideIn 0.3s ease-out;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            border-radius: 4px;
        ">
            <div style="display: flex; align-items: center; flex: 1;">
                <span style="
                    margin-right: 10px;
                    color: <?php echo $color; ?>;
                    font-weight: bold;
                    font-size: 18px;
                "><?php echo $icon; ?></span>
                <span style="color: #333; word-break: break-word;"><?php echo htmlspecialchars($message); ?></span>
            </div>
            <button onclick="this.parentElement.remove()" style="
                background: none;
                border: none;
                color: #666;
                cursor: pointer;
                font-size: 20px;
                padding: 0 5px;
                margin-left: 10px;
                transition: color 0.2s;
            " onmouseover="this.style.color='#333'" onmouseout="this.style.color='#666'">×</button>
        </div>
        <style>
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes fadeOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            .notification {
                transition: all 0.3s ease;
            }
            .notification:hover {
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            }
            .notification.fade-out {
                animation: fadeOut 0.3s ease-out forwards;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const notification = document.querySelector('.notification');
                if (notification) {
                    setTimeout(() => {
                        notification.classList.add('fade-out');
                        setTimeout(() => {
                            notification.remove();
                        }, 300);
                    }, 10000);
                }
            });
        </script>
        <?php
    }
} 