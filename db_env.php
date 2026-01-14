<?php
/**
 * Cấu hình môi trường database
 *
 * Cách dùng:
 * - Khi chạy trên localhost (XAMPP): đặt 'mode' => 'local'
 * - Khi chạy trên server thật:      đặt 'mode' => 'production'
 *
 * Sau đó toàn bộ project (frontend + backend) sẽ tự dùng cấu hình tương ứng.
 */

return [
    // Thay 'local' ↔ 'production' để đổi nhanh giữa localhost và server
    'mode' => 'local', // 'local' hoặc 'production'

    // Cấu hình MySQL trên localhost (XAMPP)
    'local' => [
        'db_host'     => 'localhost',
        'db_name'     => 'vnmt_db', // TODO: đổi đúng tên database bạn tạo trong phpMyAdmin
        'db_user'     => 'root',
        'db_password' => '',
    ],

    // Cấu hình MySQL trên hosting (sao chép từ cấu hình cũ)
    'production' => [
        'db_host'     => 'localhost',
        'db_name'     => 'np7fqxw1yb9s_vnmt_db',
        'db_user'     => 'np7fqxw1yb9s_admin',
        'db_password' => 'Sang17092001',
    ],
];


