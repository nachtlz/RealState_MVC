<main class="contenedor seccion">
    <h1>Registrar Vendedor</h1>

    <?php 
    foreach($errores as $error) {
    ?>
        <div class="alerta error">
            <?php echo $error; ?>
        </div>
    <?php
    } 
    ?>

    <form class="formulario" method="POST">

        <?php include __DIR__ . "/formulario.php"; ?>

        <input type="submit" value="Registrar Vendedor" class="boton boton-verde">
    </form>

    <a href="/admin" class="boton boton-verde">Volver</a>

</main>