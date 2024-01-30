<?php

namespace Model;

class ActiveRecord {
    protected static $db;
    protected static $columnasDB = [];
    protected static $errores = [];
    protected static $tabla = "";

    public $id;

    //Definir la conexión a la bd
    public static function setDB($database) {
        self::$db = $database;
    }

    public function guardar() {
        if(!is_null($this->id)){

            //Si el id existe, actualizamos
            $this->actualizar();
        } else {

            //Sino, lo creamos
            $this->crear();
        }
    }

    public function crear() {

        //Sanitizar datos
        $atributos = $this->sanitizarAtributos();

        //Insertar base de datos
        $query = "INSERT INTO " . static::$tabla . " ( ";
        $query .= join(", ", array_keys($atributos));
        $query .= " ) VALUES ('";
        $query .= join("', '", array_values($atributos)) . "');";


        $resultado = self::$db->query($query);
        
        if ($resultado) {
            //Redireccionar al usuario
            header("Location: /admin?resultado=1"); 
        }
    }

    public function actualizar() {
        //Sanitizar datos
        $atributos = $this->sanitizarAtributos();

        $valores = [];
        foreach($atributos as $key => $value){
            $valores[] = "$key='$value'";
        }

        $query = "UPDATE " . static::$tabla . " SET ";
        $query .= join(", ", $valores);
        $query .= " WHERE id= '" . self::$db->escape_string($this->id) . "'";
        $query .= " LIMIT 1";
        
        $resultado = self::$db->query($query);
 
        if ($resultado) {
            $this->borrarImagen();
            //Redireccionar al usuario
            header("Location: /admin?resultado=2"); 
        }
    }

    public function eliminar() {
        //Eliminar propiedad
        $query = "DELETE FROM " . static::$tabla . " WHERE id='" . self::$db->escape_string($this->id) . "' LIMIT 1";
        $resultado = self::$db->query($query);

        if($resultado) {
            $this->borrarImagen();
            header("Location: /admin?resultado=3");
        }
    }

    public function atributos() {
        $atributos = [];
        foreach(static::$columnasDB as $columna) {
            if ($columna === "id") continue;
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    public function sanitizarAtributos() {
        $atributos = $this->atributos();
        $sanitizado = [];

        foreach($atributos as $key => $value) {
            $sanitizado[$key] = self::$db->escape_string($value);
        }

        return $sanitizado;
    }

    //Subida de archivo
    public function setImagen($imagen) {

        if(!is_null($this->id)) {
            $this->borrarImagen();
        }

        if($imagen) {
            $this->imagen = $imagen;
        }
    }

    public function borrarImagen() {
        //Existe el archivo
        if(file_exists(CARPETA_IMAGENES . $this->imagen)){
            unlink(CARPETA_IMAGENES . $this->imagen);
        }
    }

    //Validación
    public static function getErrores() {
        return static::$errores;
    }

    public function validar() {
        static::$errores = [];

        return static::$errores;
    }

    //Lista todas las propiedades
    public static function all() {
        $query = "SELECT * FROM " . static::$tabla;
        $resultado = self::consultarSQL($query);

        return $resultado;
    }

    //Obtiene determinado numero de registros
    public static function get($cantidad) {
        $query = "SELECT * FROM " . static::$tabla . " LIMIT " . $cantidad;
        $resultado = self::consultarSQL($query);

        return $resultado;
    }

    public static function find($id) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE id=$id";

        $resultado = self::consultarSQL($query);

        return array_shift($resultado);
    }

    public static function consultarSQL($query) {
        //Consultar la DB
        $resultado = self::$db->query($query);


        //Iterar los resultados
        $array = [];
        while ($registro = $resultado->fetch_assoc()){
            $array[] = static::crearObjeto($registro);
        }

        //Liberar la memoria
        $resultado->free();


        //Retonar los resultados
        return $array;
    }

    protected static function crearObjeto($registro) {
        $objeto = new static;

        foreach($registro as $key => $value) {
            if (property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }
        return $objeto;
    }

    //Sincroniza el objeto en memoria con los cambios realizados por el usuario
    public function sincronizar($args = []) {
        foreach($args as $key => $value) {
            if(property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }
}