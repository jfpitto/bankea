<?php

class FuncionesGenerales {
  
  private $conexion;
  
  public function __construct($conexion) {
    $this->conexion = $conexion;
  }
  
  public function obtenerTotalCategorias() {
    $query = "SELECT COUNT(*) AS total_categorias FROM categorias";
    $result = mysqli_query($this->conexion, $query);
    $row = mysqli_fetch_assoc($result);
    $totalCategorias = $row['total_categorias'];
    return $totalCategorias;
  }
  public function obtenerTotalProducto() {
    $query = "SELECT nombre, cantidades FROM productos";
    $result = mysqli_query($this->conexion, $query);
    
    $productos = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $producto = array(
            'nombre' => $row['nombre'],
            'cantidades' => $row['cantidades']
        );
        $productos[] = $producto;
    }
    
    return $productos;
}
  public function obtenerTotalProveedor() {
    $query = "SELECT COUNT(*) AS total_proveedor FROM proveedores";
    $result = mysqli_query($this->conexion, $query);
    $row = mysqli_fetch_assoc($result);
    $totalProveedores = $row['total_proveedor'];
    return $totalProveedores;
  }
 
  
}

?>