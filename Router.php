<?php

namespace MVC;

class Router {

    public $rutasGET = [];
    public $rutasPOST = [];

    public function get($url, $fn) {
        $this->rutasGET[$url] = $fn;
    }

    public function post($url, $fn) {
        $this->rutasPOST[$url] = $fn;
    }

    public function comprobarRutas() {

        session_start();

        $auth = $_SESSION["login"] ?? null;

        //Array de rutas protegidas
        $rutas_protegidas = ["/admin", "/propiedades/crear", "/propiedades/actualizar", "/propiedades/eliminar",
        "/vendedores/crear", "/vendedores/actualizar", "/vendedores/eliminar"];


        $urlActual = $_SERVER["PATH_INFO"] ?? "/";
        $metodo = $_SERVER["REQUEST_METHOD"];
        $fn = null;

        if($metodo === "GET") {
            $fn = $this->rutasGET[$urlActual] ?? null;
        } else {
            $fn = $this->rutasPOST[$urlActual] ?? null;
        }

        //Proteger las rutas
        if(in_array($urlActual, $rutas_protegidas) && !$auth) {
            header("Location: /");
        }

        if($fn) {
            //La URL existe y hay una función asociada
            call_user_func($fn, $this);
        } else {
            echo "Página no encontrada";
        }
    }

    public function render($view, $datos = []) {

        foreach($datos as $key => $value) {
            $$key = $value;
        }
        
        //Almacenamiento en memoria durante un momento...
        ob_start(); 
        include_once __DIR__ . "/views/$view.php";

        //Limpia el buffer
        $contenido = ob_get_clean();

        include_once __DIR__ . "/views/layout.php";
    }
}