-- Fix User Roles Script
-- Update user roles based on their role_id

-- First, let's see what we have
SELECT 
    u.id,
    u.username,
    u.nama_lengkap,
    u.role,
    u.role_id,
    r.nama_role as role_name
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
ORDER BY u.id;

-- Update user roles based on role_id
UPDATE users u
JOIN roles r ON u.role_id = r.id
SET u.role = r.nama_role
WHERE u.role IS NULL OR u.role = '';

-- Verify the update
SELECT 
    u.id,
    u.username,
    u.nama_lengkap,
    u.role,
    r.nama_role as role_name
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
ORDER BY u.id;
