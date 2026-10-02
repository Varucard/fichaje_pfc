-- Migración 003: claves foráneas e índices únicos de matriculación.
-- Garantizan en la base lo que antes solo validaba el código.

ALTER TABLE user_class
  ADD UNIQUE KEY uq_user_class (id_user, id_class),
  ADD CONSTRAINT fk_user_class_usuario FOREIGN KEY (id_user) REFERENCES users (id_user),
  ADD CONSTRAINT fk_user_class_clase FOREIGN KEY (id_class) REFERENCES classes (id_class) ON DELETE CASCADE;

ALTER TABLE teacher_class
  ADD UNIQUE KEY uq_teacher_class (id_user, id_class),
  ADD CONSTRAINT fk_teacher_class_usuario FOREIGN KEY (id_user) REFERENCES users (id_user),
  ADD CONSTRAINT fk_teacher_class_clase FOREIGN KEY (id_class) REFERENCES classes (id_class) ON DELETE CASCADE;

ALTER TABLE payments
  ADD CONSTRAINT fk_payments_usuario FOREIGN KEY (id_user) REFERENCES users (id_user);

ALTER TABLE incomes
  ADD CONSTRAINT fk_incomes_usuario FOREIGN KEY (id_user) REFERENCES users (id_user);

ALTER TABLE users
  ADD CONSTRAINT fk_users_tipo FOREIGN KEY (type_user) REFERENCES types_users (id_type_user);
