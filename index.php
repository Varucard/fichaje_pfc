<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/dark.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
 
</head>
<body>
    <div class="login">
        <h1>Bienvenido a nuestro sistema de razas de perros</h1>
        <h3>Por favor, inicie sesión</h3>
        <form method="post">
            <input type="text" name="username" placeholder="Usuario" required="required" />
            <input type="password" name="password" placeholder="contraseña" required="required" />
            <button type="submit" class="btn btn-primary btn-block btn-large">Entrar</button>
        </form>
        <h3>No posee una cuenta, cree una</h3>
        <a href="views/registro.php"><button type="submit" class="btn btn-primary btn-block btn-large">Registrarse</button></a>
    </div>
</body>
</html>