<?php

namespace Controllers;
use MVC\Router;
use Model\Propiedad;
use Model\Vendedor;
use Intervention\Image\ImageManagerStatic as Image;

class PropiedadController {

    public static function index(Router $router) {

        $propiedades = Propiedad::all();
        $vendedores = Vendedor::all();
        $resultado = $_GET["resultado"] ?? null;

        $router->render("propiedades/admin", [
            "propiedades" => $propiedades,
            "vendedores" => $vendedores,
            "resultado" => $resultado
        ]);
    }

    public static function crear(Router $router) {

        $propiedad = new Propiedad;
        $vendedores = Vendedor::all();
        $errores = Propiedad::getErrores();

        if($_SERVER["REQUEST_METHOD"] === "POST") {
            $propiedad = new Propiedad($_POST["propiedad"]);

            $nombreImagen = md5( uniqid( rand(), true)) . ".jpg";
    
            if($_FILES["propiedad"]["tmp_name"]["imagen"]){
                //Realiza un resize a la imagen con Intervention
                $image = Image::make($_FILES["propiedad"]["tmp_name"]["imagen"])->fit(800,600);
                $propiedad->setImagen($nombreImagen);
            }
    
            $errores = $propiedad->validar();
    
            //Revisar errores
            if (empty($errores)) {
    
    
                if (!is_dir(CARPETA_IMAGENES)) {
                    mkdir(CARPETA_IMAGENES);
                }
    
                $propiedad->guardar();
    
                //Guarda la imagen en el servidor
                if ($_FILES['propiedad']['tmp_name']['imagen']){
                    $image->save(CARPETA_IMAGENES . $nombreImagen);
                }
            }
        }
        
        $router->render("propiedades/crear", [
            "propiedad" => $propiedad,
            "vendedores" => $vendedores,
            "errores" => $errores
        ]);
    }

    public static function actualizar(Router $router) {
        
        $id = validarORedireccionar("/admin");
        $propiedad = Propiedad::find($id);
        $errores = Propiedad::getErrores();
        $vendedores = Vendedor::all();

        //Enviar a la bd la información rellenada por el usuario
        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            //Asignar los atributos
            $args = $_POST["propiedad"];

            $propiedad->sincronizar($args);

            $errores = $propiedad->validar();

            $nombreImagen = md5( uniqid( rand(), true)) . ".jpg";

            if($_FILES["propiedad"]["tmp_name"]["imagen"]){
                //Realiza un resize a la imagen con Intervention
                $image = Image::make($_FILES["propiedad"]["tmp_name"]["imagen"])->fit(800,600);
                $propiedad->setImagen($nombreImagen);
            }

            //Revisar errores
            if (empty($errores)) {
                $propiedad->guardar();
                $image->save(CARPETA_IMAGENES . $nombreImagen);
            }
        }

        $router->render("/propiedades/actualizar", [
            "propiedad" => $propiedad,
            "vendedores" => $vendedores,
            "errores" => $errores
        ]);
    }

    public static function eliminar(Router $router) {
        if($_SERVER["REQUEST_METHOD"] === "POST"){
            $id = $_POST["id"];
            $id = filter_var($id, FILTER_VALIDATE_INT);
    
            if ($id) {
                $tipo = $_POST["tipo"];
                if(validarTipoContenido($tipo)){
                    $propiedad = Propiedad::find($id);
                    $propiedad->eliminar();
                }
            }
        }
    }
}