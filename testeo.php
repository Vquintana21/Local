<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Configuración de la API
$API_URL = 'https://3da5f7dc59b7f086569838076e7d7df5:698c0edbf95ddbde@ucampus.uchile.cl/api/0/medicina_mufasa/cursos_inscritos';
$RUT = '24949120';
$PERIODO = '2024.1';

// Función para obtener los cursos desde la API usando curl
function fetchCourses($apiUrl, $rut, $periodo) {
    $url = $apiUrl . "?rut=" . $rut . "&periodo=" . $periodo;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
        echo 'Error en la solicitud cURL: ' . curl_error($ch);
        return [];
    }
    
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo 'Error al decodificar la respuesta JSON: ' . json_last_error_msg();
        return [];
    }
    
    // Ordenar los cursos por código
    usort($data, function($a, $b) {
         return strcmp($a['codigo'], $b['codigo']);
    });
    
    return $data;
}



// Obtener los cursos
$courses = fetchCourses($API_URL, $RUT, $PERIODO);

// Convertir los cursos a JSON para usar en JavaScript
$coursesJson = json_encode($courses);

// Asegurarse de que no hubo errores en la codificación JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'Error al codificar los cursos a JSON: ' . json_last_error_msg();
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Justificación de Inasistencias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
	   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .course-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .selected {
            background-color: #e9ecef;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Justificación de Inasistencias</h1>
        
        <div class="row">
            <!-- Selección de Cursos -->
            <div class="col-md-4">
                <h2>Cursos</h2>
                <div id="courseList"></div>
            </div>

            <!-- Selección de Actividades -->
            <div class="col-md-4">
                <h2>Actividades</h2>
                <div id="activityList"></div>
            </div>

            <!-- Formulario de Justificación -->
            <div class="col-md-4">
                <h2>Justificación</h2>
                <form id="justificationForm">
                    <div class="mb-3">
                        <label for="justificationType" class="form-label">Tipo de Justificación</label>
                        <select id="justificationType" class="form-select" required>
                            <option value="">Selecciona un tipo</option>
                            <option value="medical">Médica</option>
                            <option value="family">Familiar</option>
                            <option value="transport">Transporte</option>
                            <option value="other">Otra</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="justificationDetails" class="form-label">Detalles</label>
                        <textarea id="justificationDetails" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="fileUpload" class="form-label">Adjuntar archivos</label>
                        <input type="file" id="fileUpload" class="form-control" multiple required>
                        <small id="fileHelp" class="form-text text-muted"></small>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Justificación</button>
                </form>
            </div>
        </div>

        <!-- JSON Output -->
        <div class="row mt-5">
            <div class="col-12">
                <h2>JSON de Salida</h2>
                <pre id="jsonOutput" class="bg-light p-3 border rounded"></pre>
            </div>
        </div>
    </div>

    <!-- Modal para agregar actividad manual -->
    <div class="modal fade" id="manualActivityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Actividad Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="manualActivityForm">
                    <div class="mb-3">
                        <label for="manualActivityType" class="form-label">Tipo de Actividad</label>
                        <select id="manualActivityType" class="form-select" required>
                            <option value="" disabled selected>Seleccione Tipo de Actividad</option>
                            <option value="20">Seminario</option>
                            <option value="21">Actividad práctica</option>
                            <option value="22">Certamen</option>
                            <option value="23">Control</option>
                            <option value="25">Presentación grupal o individual</option>
                            <option value="26">Internado o práctica profesional</option>
                            <option value="27">Taller</option>
                            <option value="28">Tutoría</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="manualDateStart" class="form-label">Fecha de Inicio</label>
                        <input type="date" id="manualDateStart" class="form-control" required>
                    </div>
                    <div class="mb-3" id="dateEndContainer" style="display: none;">
                        <label for="manualDateEnd" class="form-label">Fecha de Fin</label>
                        <input type="date" id="manualDateEnd" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="manualBlock" class="form-label">Bloque Horario</label>
                        <select id="manualBlock" class="form-select" required>
                            <option value="1">08:00 - 10:00</option>
                            <option value="2">10:00 - 12:00</option>
                            <option value="3">12:00 - 14:30</option>
                            <option value="4">14:30 - 16:30</option>
                            <option value="5">16:30 - 18:45</option>
                            <option value="AM">AM (Toda la mañana)</option>
                            <option value="PM">PM (Toda la tarde)</option>
                            <option value="FD">Todo el día</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="saveManualActivity">Guardar</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        
        let coursesData = <?php echo $coursesJson; ?>;
		const RUT = '<?php echo $RUT; ?>';
        const PERIODO = '<?php echo $PERIODO; ?>';
        let activitiesData = [];
        let selectedCourses = [];
        let selectedActivities = [];
        let manualActivities = [];

        const blockTimes = {
            "1": "08:00 - 10:00",
            "2": "10:00 - 12:00",
            "3": "12:00 - 14:30",
            "4": "14:30 - 16:30",
            "5": "16:30 - 18:45",
            "AM": "Toda la mañana",
            "PM": "Toda la tarde",
            "FD": "Todo el día"
        };

        // Función para obtener la fecha actual en formato YYYY-MM-DD
        function getCurrentDate() {
            return new Date().toISOString().split('T')[0];
        }

        // Renderizar cursos
        async function renderCourses() {
           
            const courseList = document.getElementById('courseList');
            courseList.innerHTML = coursesData.map(course => `
                <div class="card mb-2 course-card" data-course-id="${course.codigo}">
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input curso-checkbox" type="checkbox" role="switch" id="curso-${course.codigo}" value="${course.codigo}">
                            <label class="form-check-label" for="curso-${course.codigo}">
                                ${course.codigo}-${course.seccion} - ${course.nombre}
                            </label>
                        </div>
                    </div>
                </div>
            `).join('');

            document.querySelectorAll('.curso-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', (e) => {
                    const courseId = e.target.value;
                    const card = e.target.closest('.course-card');
                    if (e.target.checked) {
                        selectedCourses.push(courseId);
                        card.classList.add('selected');
                    } else {
                        selectedCourses = selectedCourses.filter(id => id !== courseId);
                        card.classList.remove('selected');
                    }
                    renderActivities();
                });
            });
        }

        // Renderizar actividades
       function renderActivities() {
    const activityList = document.getElementById('activityList');
    const currentDate = getCurrentDate();
    activityList.innerHTML = selectedCourses.map(courseId => {
        const course = coursesData.find(c => c.codigo === courseId);
        const activities = [
            ...activitiesData.filter(a => a.courseId === courseId && a.date <= currentDate),
            ...manualActivities.filter(a => a.courseId === courseId)
        ];
        return `
            <div class="card mb-3">
                <div class="card-header">${course.nombre}</div>
                <div class="card-body">
                    <ul class="list-group">
                        ${activities.map(activity => `
                            <li class="list-group-item">
                                <div class="form-check">
                                    <input class="form-check-input activity-checkbox" type="checkbox" 
                                        value="${activity.id}" id="activity${activity.id}"
                                        ${selectedActivities.some(a => a.courseId === courseId && a.activityId === activity.id) ? 'checked' : ''}>
                                    <label class="form-check-label" for="activity${activity.id}">
                                        ${formatActivityDate(activity)}
                                        ${activity.name}<br>
                                        <small class="text-muted">${blockTimes[activity.block]}</small>
                                    </label>
                                </div>
                            </li>
                        `).join('')}
                    </ul>
                    <button class="btn btn-sm btn-primary mt-2 add-manual-activity" data-course-id="${courseId}">
                        Agregar Actividad Manual
                    </button>
                </div>
            </div>
        `;
    }).join('');

    // Evento para checkbox de actividades
    document.querySelectorAll('.activity-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', (e) => {
            const activityId = e.target.value;
            const courseId = e.target.closest('.card').querySelector('.add-manual-activity').dataset.courseId;
            if (e.target.checked) {
                selectedActivities.push({ courseId, activityId });
            } else {
                selectedActivities = selectedActivities.filter(
                    a => !(a.courseId === courseId && a.activityId === activityId)
                );
            }
        });
    });

    // Evento para agregar actividad manual
    document.querySelectorAll('.add-manual-activity').forEach(button => {
        button.addEventListener('click', (e) => {
            const courseId = e.target.dataset.courseId;
            showManualActivityModal(courseId);
        });
    });
}

// Función auxiliar para formatear la fecha de la actividad
function formatActivityDate(activity) {
    if (activity.dateStart) {
        // Actividad manual
        if (activity.idTipoAct === '26' && activity.dateEnd) {
            return `${activity.dateStart} - ${activity.dateEnd}: `;
        } else {
            return `${activity.dateStart}: `;
        }
    } else {
        // Actividad automática
        return `${activity.date}: `;
    }
}

        // Modal para agregar actividad manual
 function showManualActivityModal(courseId) {
        const modal = new bootstrap.Modal(document.getElementById('manualActivityModal'));
        const manualDateStartInput = document.getElementById('manualDateStart');
        const manualDateEndInput = document.getElementById('manualDateEnd');
        const manualActivityType = document.getElementById('manualActivityType');
        const dateEndContainer = document.getElementById('dateEndContainer');
        
        // Limpiar los campos del formulario
        document.getElementById('manualActivityForm').reset();
        
        // Establecer la fecha máxima como la fecha actual para la fecha de inicio
        manualDateStartInput.max = getCurrentDate();
        
        // Mostrar u ocultar el campo de fecha de fin según el tipo de actividad
        manualActivityType.addEventListener('change', function() {
            if (this.value === '26') {
                dateEndContainer.style.display = 'block';
                manualDateEndInput.min = ''; // Permitir fechas futuras solo para la fecha de fin de internados
            } else {
                dateEndContainer.style.display = 'none';
            }
        });
        
        modal.show();

        document.getElementById('saveManualActivity').onclick = () => {
            const activityType = manualActivityType.value;
            const dateStart = manualDateStartInput.value;
            const dateEnd = manualDateEndInput.value;
            const block = document.getElementById('manualBlock').value;

            if (activityType && dateStart && block && (activityType !== '26' || dateEnd)) {
                const newActivity = {
                    id: `M${manualActivities.length + 1}`,
                    courseId: courseId,
                    dateStart: dateStart,
                    dateEnd: activityType === '26' ? dateEnd : null,
                    name: manualActivityType.options[manualActivityType.selectedIndex].text,
                    type: 'M',  // Manual
                    block: block,
                    idTipoAct: activityType
                };
                manualActivities.push(newActivity);
                selectedActivities.push({ courseId, activityId: newActivity.id });
                renderActivities();
                modal.hide();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Campos incompletos',
                    text: 'Por favor, completa todos los campos requeridos.'
                });
            }
        };
    }

    // Manejar envío de justificación
    document.getElementById('justificationForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const justificationType = document.getElementById('justificationType').value;
        const justificationDetails = document.getElementById('justificationDetails').value;
        const files = document.getElementById('fileUpload').files;

        if (selectedActivities.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Selección incompleta',
                text: 'Por favor, selecciona al menos una actividad para justificar.'
            });
            return;
        }

        // Validar el número de archivos según el tipo de justificación
        let requiredFiles;
        switch(justificationType) {
            case 'medical':
            case 'family':
                requiredFiles = 2;
                break;
            case 'transport':
                requiredFiles = 3;
                break;
            case 'other':
                requiredFiles = 1;
                break;
            default:
                requiredFiles = 0;
        }

        if (files.length !== requiredFiles) {
            Swal.fire({
                icon: 'error',
                title: 'Documentos faltantes',
                text: `Por favor, adjunte ${requiredFiles} documento(s) para este tipo de justificación.`
            });
            return;
        }

        const justificationData = {
            IDMASTER: Date.now(),  // Usamos timestamp como ID único
            RUT: RUT,
            justificaciones: selectedActivities.map(sa => {
                const activity = [...activitiesData, ...manualActivities].find(a => a.id == sa.activityId);
                return {
                    IDCURSO: sa.courseId,
                    TIPOACT: activity.type,
                    IDTIPOACT: activity.idTipoAct,
                    JUSTIFICATIVO: justificationDetails,
                    CATEGORIA: justificationType,
                    FECHA_SOLICITUD: getCurrentDate(),
                    ESTADO: "Pendiente",
                    FASE: "Inicial",
                    FECHA_INICIO: activity.dateStart || activity.date,
                    FECHA_FIN: activity.idTipoAct === '26' ? activity.dateEnd : null,
                    BLOQUE_HORARIO: activity.block
                };
            }),
            documentos: Array.from(files).map((file, index) => ({
                ID: index + 1,
                ID_maestro: Date.now(),
                NOMBRE_DOC: `documento_${index + 1}.pdf`,
                NOMBRE_ORIGINAL: file.name,
                FECHA_SUBIDA: getCurrentDate()
            }))
        };

        // Mostrar JSON en la página
        document.getElementById('jsonOutput').textContent = JSON.stringify(justificationData, null, 2);

        console.log('Justificación enviada', justificationData);
        
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: 'Justificación enviada con éxito!'
        });
    });

        // Inicialización
        renderCourses();
    </script>
</body>
</html>