<div class="login-container">
  <img src="<?= asset('img/logo.png') ?>" alt="Palillo Fight Club">
  <h1>Iniciar Sesión</h1>

  <form action="<?= url('/login') ?>" method="post">
    <?= csrf_field() ?>
    <div>
      <label for="dni">Nro. de Documento:</label>
      <input type="text" name="dni" id="dni" inputmode="numeric" autocomplete="username" value="<?= e(old('dni')) ?>" required autofocus>
    </div>
    <div>
      <label for="password">Contraseña:</label>
      <input type="password" name="password" id="password" autocomplete="current-password" required>
    </div>
    <button type="submit">Iniciar Sesión</button>
  </form>
</div>
