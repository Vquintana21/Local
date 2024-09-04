<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Justificación de Inasistencias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .badge-block {
            font-size: 0.8em;
            padding: 0.35em 0.65em;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Resumen de Justificación de Inasistencias</h1>
        
        <!-- Información del Estudiante -->
        <div id="infoEstudiante" class="card mb-4"></div>

        <!-- Resumen de Justificaciones por Curso -->
        <h2 class="mb-3">Justificaciones por Curso</h2>
        <div id="justificacionesPorCurso" class="mb-4"></div>

        <!-- Documentos Adjuntos -->
        <h2 class="mb-3">Documentos Adjuntos</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nombre Original</th>
                    <th>Nombre del Documento</th>
                    <th>Fecha de Subida</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="documentosAdjuntos"></tbody>
        </table>

        <!-- Justificativo -->
        <div id="justificativo" class="card mt-4"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JSON de entrada
        const jsonData = {
            "IDMASTER": 1725402516069,
            "RUT": "12345678-9",
            "justificaciones": [
                {
                    "IDCURSO": 1,
                    "TIPOACT": "A",
                    "IDTIPOACT": "22",
                    "JUSTIFICATIVO": "holaaaaaa",
                    "CATEGORIA": "medical",
                    "FECHA_SOLICITUD": "2024-09-03",
                    "ESTADO": "Pendiente",
                    "FASE": "Inicial",
                    "FECHA_ACTIVIDAD": "2023-09-05",
                    "BLOQUE_HORARIO": "2"
                },
                {
                    "IDCURSO": 1,
                    "TIPOACT": "A",
                    "IDTIPOACT": "27",
                    "JUSTIFICATIVO": "holaaaaaa",
                    "CATEGORIA": "medical",
                    "FECHA_SOLICITUD": "2024-09-03",
                    "ESTADO": "Pendiente",
                    "FASE": "Inicial",
                    "FECHA_ACTIVIDAD": "2023-09-12",
                    "BLOQUE_HORARIO": "4"
                },
                {
                    "IDCURSO": 1,
                    "TIPOACT": "M",
                    "IDTIPOACT": "20",
                    "JUSTIFICATIVO": "holaaaaaa",
                    "CATEGORIA": "medical",
                    "FECHA_SOLICITUD": "2024-09-03",
                    "ESTADO": "Pendiente",
                    "FASE": "Inicial",
                    "FECHA_ACTIVIDAD": "2024-08-26",
                    "BLOQUE_HORARIO": "2"
                },
                {
                    "IDCURSO": 3,
                    "TIPOACT": "A",
                    "IDTIPOACT": "25",
                    "JUSTIFICATIVO": "holaaaaaa",
                    "CATEGORIA": "medical",
                    "FECHA_SOLICITUD": "2024-09-03",
                    "ESTADO": "Pendiente",
                    "FASE": "Inicial",
                    "FECHA_ACTIVIDAD": "2023-09-14",
                    "BLOQUE_HORARIO": "5"
                },
                {
                    "IDCURSO": 3,
                    "TIPOACT": "M",
                    "IDTIPOACT": "20",
                    "JUSTIFICATIVO": "holaaaaaa",
                    "CATEGORIA": "medical",
                    "FECHA_SOLICITUD": "2024-09-03",
                    "ESTADO": "Pendiente",
                    "FASE": "Inicial",
                    "FECHA_ACTIVIDAD": "2024-09-02",
                    "BLOQUE_HORARIO": "1"
                },
                {
                    "IDCURSO": 3,
                    "TIPOACT": "M",
                    "IDTIPOACT": "21",
                    "JUSTIFICATIVO": "holaaaaaa",
                    "CATEGORIA": "medical",
                    "FECHA_SOLICITUD": "2024-09-03",
                    "ESTADO": "Pendiente",
                    "FASE": "Inicial",
                    "FECHA_ACTIVIDAD": "2024-09-02",
                    "BLOQUE_HORARIO": "AM"
                }
            ],
            "documentos": [
                {
                    "ID": 1,
                    "ID_maestro": 1725402516069,
                    "NOMBRE_DOC": "documento_1.pdf",
                    "NOMBRE_ORIGINAL": "BCEBRO6222 (5).pdf",
                    "FECHA_SUBIDA": "2024-09-03"
                },
                {
                    "ID": 2,
                    "ID_maestro": 1725402516069,
                    "NOMBRE_DOC": "documento_2.pdf",
                    "NOMBRE_ORIGINAL": "BCEBRO6222 (4).pdf",
                    "FECHA_SUBIDA": "2024-09-03"
                }
            ]
        };

        // Mapeo de cursos (en una implementación real, esto vendría de la base de datos)
        const cursos = {
            1: { nombre: "Matemáticas", codigo: "MAT101" },
            3: { nombre: "Programación", codigo: "PRO301" }
        };

        // Mapeo de bloques horarios
        const bloqueHorario = {
            "1": "08:00 - 10:00",
            "2": "10:00 - 12:00",
            "3": "12:00 - 14:30",
            "4": "14:30 - 16:30",
            "5": "16:30 - 18:45",
            "AM": "Toda la mañana",
            "PM": "Toda la tarde",
            "FD": "Todo el día"
        };

        // Mapeo de tipos de actividad
        const tiposActividad = {
            "20": "Seminario",
            "21": "Actividad práctica",
            "22": "Certamen",
            "23": "Control",
            "25": "Presentación grupal o individual",
            "26": "Internado o práctica profesional",
            "27": "Taller",
            "28": "Tutoría"
        };

        // Función para renderizar la información del estudiante
        function renderInfoEstudiante() {
            const infoEstudiante = document.getElementById('infoEstudiante');
            infoEstudiante.innerHTML = `
                <div class="card-header bg-primary text-white">
                    Información del Estudiante
                </div>
                <div class="card-body">
                    <p><strong>RUT:</strong> ${jsonData.RUT}</p>
                    <p><strong>ID de Solicitud:</strong> ${jsonData.IDMASTER}</p>
                    <p><strong>Fecha de Solicitud:</strong> ${jsonData.justificaciones[0].FECHA_SOLICITUD}</p>
                    <p><strong>Estado:</strong> <span class="badge bg-warning">${jsonData.justificaciones[0].ESTADO}</span></p>
                    <p><strong>Fase:</strong> <span class="badge bg-info">${jsonData.justificaciones[0].FASE}</span></p>
                    <p><strong>Categoría:</strong> <span class="badge bg-secondary">${jsonData.justificaciones[0].CATEGORIA}</span></p>
                </div>
            `;
        }

        // Función para renderizar las justificaciones por curso
        function renderJustificaciones() {
            const justificacionesPorCurso = document.getElementById('justificacionesPorCurso');
            const justificacionesPorCursoMap = new Map();

            jsonData.justificaciones.forEach(justificacion => {
                if (!justificacionesPorCursoMap.has(justificacion.IDCURSO)) {
                    justificacionesPorCursoMap.set(justificacion.IDCURSO, []);
                }
                justificacionesPorCursoMap.get(justificacion.IDCURSO).push(justificacion);
            });

            justificacionesPorCursoMap.forEach((justificaciones, idCurso) => {
                const curso = cursos[idCurso];
                const justificacionesHTML = justificaciones.map(j => `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ${tiposActividad[j.IDTIPOACT]} ${j.TIPOACT === 'M' ? '(Manual)' : ''}
                        <span>
                            <span class="badge bg-primary rounded-pill me-2">${j.FECHA_ACTIVIDAD}</span>
                            <span class="badge badge-block bg-success">${bloqueHorario[j.BLOQUE_HORARIO]}</span>
                        </span>
                    </li>
                `).join('');

                justificacionesPorCurso.innerHTML += `
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            ${curso.nombre} (${curso.codigo})
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                ${justificacionesHTML}
                            </ul>
                        </div>
                    </div>
                `;
            });
        }

        // Función para renderizar los documentos adjuntos
        function renderDocumentos() {
            const documentosAdjuntos = document.getElementById('documentosAdjuntos');
            documentosAdjuntos.innerHTML = jsonData.documentos.map(doc => `
                <tr>
                    <td>${doc.NOMBRE_ORIGINAL}</td>
                    <td>${doc.NOMBRE_DOC}</td>
                    <td>${doc.FECHA_SUBIDA}</td>
                    <td><button class="btn btn-sm btn-primary" onclick="alert('Descargando ${doc.NOMBRE_DOC}')">Ver/Descargar</button></td>
                </tr>
            `).join('');
        }

        // Función para renderizar el justificativo
        function renderJustificativo() {
            const justificativo = document.getElementById('justificativo');
            justificativo.innerHTML = `
                <div class="card-header bg-info text-white">
                    Justificativo
                </div>
                <div class="card-body">
                    <p>${jsonData.justificaciones[0].JUSTIFICATIVO}</p>
                </div>
            `;
        }

        // Inicializar la página
        renderInfoEstudiante();
        renderJustificaciones();
        renderDocumentos();
        renderJustificativo();
    </script>
</body>
</html>