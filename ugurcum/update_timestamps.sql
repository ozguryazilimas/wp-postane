
UPDATE wp_ugurcum
SET created_at = ADDTIME(created_at, '3:00:00');

UPDATE wp_ugurcum
SET updated_at = ADDTIME(updated_at, '3:00:00');

UPDATE wp_ugurcum_user_reads
SET read_time = ADDTIME(read_time, '3:00:00');

