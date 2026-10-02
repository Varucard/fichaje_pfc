-- Migración 002: limpieza de datos inconsistentes y DNI único.
-- Si falla por DNI duplicado, buscar los duplicados con:
--   SELECT dni, COUNT(*) FROM users GROUP BY dni HAVING COUNT(*) > 1;
-- corregirlos y volver a ejecutar bin/migrar.php.

-- Matriculaciones de usuarios o clases que ya no existen
DELETE uc FROM user_class uc
  LEFT JOIN users u ON u.id_user = uc.id_user
  LEFT JOIN classes c ON c.id_class = uc.id_class
  WHERE u.id_user IS NULL OR c.id_class IS NULL;

DELETE tc FROM teacher_class tc
  LEFT JOIN users u ON u.id_user = tc.id_user
  LEFT JOIN classes c ON c.id_class = tc.id_class
  WHERE u.id_user IS NULL OR c.id_class IS NULL;

-- Matriculaciones repetidas (se conserva la primera)
DELETE a FROM user_class a JOIN user_class b
  ON a.id_user = b.id_user AND a.id_class = b.id_class AND a.id_user_class > b.id_user_class;

DELETE a FROM teacher_class a JOIN teacher_class b
  ON a.id_user = b.id_user AND a.id_class = b.id_class AND a.id_teacher_class > b.id_teacher_class;

ALTER TABLE users ADD UNIQUE KEY uq_users_dni (dni);
