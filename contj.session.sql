-- ✅ 1. تأكد من وجود الأدوار
INSERT IGNORE INTO roles (role_id, role_name)
VALUES 
    (1, 'artisan'),
    (2, 'job_owner');

-- ✅ 2. أضف مستخدمين
INSERT IGNORE INTO users (user_id, user_type, name, email, password, role_id, status, is_approved, created_at, updated_at)
VALUES 
    (1, 'artisan', 'Mohammed', 'mohammed@example.com', '$2y$10$abcdefghijklmnopqrstuv', 1, 'active', 1, NOW(), NOW()),
    (2, 'job_owner', 'Aya', 'aya@example.com', '$2y$10$abcdefghijklmnopqrstuv', 2, 'active', 1, NOW(), NOW());

-- ✅ 3. أضف منشور عمل
INSERT IGNORE INTO jobposts (job_id, title, description, budget, location, deadline, image, status, current_status, user_id, created_at, updated_at)
VALUES 
    (1, 'Test Job', 'Test description for review testing', 150.00, 'Nablus', '2025-06-30', NULL, 'Open', 'Pending', 2, NOW(), NOW());
