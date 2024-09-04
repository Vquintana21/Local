<?php
// Conexión a la base de datos
$conn = new mysqli('localhost', 'dpimeduchile', 'Zo)g[lH-MqFhBoMa~n', 'dpimeduc_calendario');
mysqli_set_charset($conn,"utf8");

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Recibir datos del formulario
//$curso = $_POST['cursoId'];
$curso = 7802;


$fechaActual = date('Y-m-d');
$fecha30DiasAtras = date('Y-m-d', strtotime('-30 days'));

// Consulta a la base de datos
$sql = "SELECT `pcl_TipoSesion`, pcl_SubTipoSesion, `pcl_tituloActividad`, `pcl_Fecha`,  Bloque , `pcl_Inicio`, `pcl_Termino`
        FROM planclases
        WHERE pcl_Periodo = '20241'
        AND pcl_TipoSesion IN ('Actividad Grupal','Trabajo Práctico','Evaluación','Examen')
        AND pcl_Semana >= 1
        AND DATE(pcl_Fecha) BETWEEN '$fecha30DiasAtras' AND '$fechaActual'
        AND cursos_idcursos = '$curso'";
$result = $conn->query($sql);


// Mostrar resultados
if ($result->num_rows > 0) {
    echo '<table class="table table-sm">';
    echo '<thead><tr><th>Seleccionar</th><th>Actividad</th><th>Fecha</th><th>Horario</th></tr></thead>';
    echo '<tbody>';

    while($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td><div class="form-check form-switch"> <input class="form-check-input" type="checkbox" role="switch" name="seleccionar[]" value="' . $row["id"] . '" data-fecha="' . $row["pcl_Fecha"] . '" data-hora="' . $row["pcl_Inicio"] . ' - ' . $row["pcl_Termino"] . '"></div></td>';
        echo '<td>' . $row["pcl_TipoSesion"] . '</td>';
        echo '<td>' . date("d-m-Y", strtotime($row["pcl_Fecha"])) . '</td>';
        echo '<td>' . $row["pcl_Inicio"] .' - ' . $row["pcl_Termino"] . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
} else {
    echo "No se encontraron resultados";
}


$conn->close();
?>