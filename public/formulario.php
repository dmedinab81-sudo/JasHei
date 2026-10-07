<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/utils/Validador.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de paciente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        h2 {
            margin-bottom: 20px;
        }
        .tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .tab {
            background: #eef2ff;
            border: 1px solid #dbeafe;
            color: #1f2937;
            border-radius: 8px;
            padding: 10px 14px;
            cursor: pointer;
            font-weight: bold;
        }
        .tab.active {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(250px, 1fr));
            gap: 16px;
        }
        .full {
            grid-column: 1 / -1;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #374151;
        }
        input, select, textarea, button {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
        }
        textarea {
            min-height: 90px;
            resize: vertical;
        }
        .actions {
            margin-top: 20px;
        }
        .btn {
            background: #198754;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-left: 10px;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Formulario de paciente y atención</h2>

        <form id="formularioPaciente" action="guardar_paciente.php" method="POST">
            <div class="tabs">
                <button type="button" class="tab active" data-target="paciente">Paciente</button>
                <button type="button" class="tab" data-target="anamnesis">Anamnesis</button>
                <button type="button" class="tab" data-target="signos">Signos vitales</button>
                <button type="button" class="tab" data-target="examen">Examen</button>
                <button type="button" class="tab" data-target="diagnostico">Diagnóstico</button>
                <button type="button" class="tab" data-target="medicacion">Medicación</button>
            </div>

            <div id="paciente" class="tab-content active">
                <div class="form-grid">
                    <div>
                        <label>Nombres</label>
                        <input type="text" name="nombres" required>
                    </div>
                    <div>
                        <label>Apellidos</label>
                        <input type="text" name="apellidos" required>
                    </div>
                    <div>
                        <label>Número de cédula</label>
                        <input type="text" name="numero_cedula" id="cedula" maxlength="10" required>
                    </div>
                    <div>
                        <label>Fecha de nacimiento</label>
                        <input type="date" name="fecha_nacimiento" required>
                    </div>
                    <div>
                        <label>Lugar de nacimiento</label>
                        <input type="text" name="lugar_nacimiento">
                    </div>
                    <div>
                        <label>Dirección</label>
                        <input type="text" name="direccion">
                    </div>
                    <div>
                        <label>Referencia domiciliaria</label>
                        <input type="text" name="referencia_domiciliaria">
                    </div>
                    <div>
                        <label>Teléfono</label>
                        <input type="text" name="telefono" maxlength="10">
                    </div>
                    <div>
                        <label>Sexo</label>
                        <select name="sexo" required>
                            <option value="">Seleccione</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="anamnesis" class="tab-content">
                <div class="form-grid">
                    <div>
                        <label>Condición de llegada</label>
                        <select name="condicion_llegada">
                            <option value="Estable">Estable</option>
                            <option value="Urgente">Urgente</option>
                            <option value="Emergencia">Emergencia</option>
                            <option value="Consulta programada">Consulta programada</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label>Motivo de consulta</label>
                        <input type="text" name="motivo_consulta">
                    </div>
                    <div class="full">
                        <label>Descripción del motivo</label>
                        <textarea name="descripcion_motivo"></textarea>
                    </div>
                    <div class="full">
                        <label>Alergias</label>
                        <textarea name="alergias"></textarea>
                    </div>
                    <div class="full">
                        <label>Antecedentes patológicos</label>
                        <textarea name="antecedentes_patologicos"></textarea>
                    </div>
                    <div class="full">
                        <label>Antecedentes familiares</label>
                        <textarea name="antecedentes_familiares"></textarea>
                    </div>
                </div>
            </div>

            <div id="signos" class="tab-content">
                <div class="form-grid">
                    <div>
                        <label>Temperatura</label>
                        <input type="number" step="0.1" name="temperatura">
                    </div>
                    <div>
                        <label>Tipo de temperatura</label>
                        <select name="temperatura_tipo">
                            <option value="Axilar">Axilar</option>
                            <option value="Oral">Oral</option>
                            <option value="Rectal">Rectal</option>
                            <option value="Frontal">Frontal</option>
                        </select>
                    </div>
                    <div>
                        <label>Frecuencia cardiaca</label>
                        <input type="number" name="frecuencia_cardiaca">
                    </div>
                    <div>
                        <label>Frecuencia respiratoria</label>
                        <input type="number" name="frecuencia_respiratoria">
                    </div>
                    <div>
                        <label>Presión sistólica</label>
                        <input type="number" name="presion_sistolica">
                    </div>
                    <div>
                        <label>Presión diastólica</label>
                        <input type="number" name="presion_diastolica">
                    </div>
                    <div>
                        <label>Saturación O2</label>
                        <input type="number" step="0.1" name="saturacion_o2">
                    </div>
                    <div>
                        <label>Peso (kg)</label>
                        <input type="number" step="0.01" name="peso">
                    </div>
                    <div>
                        <label>Talla (m)</label>
                        <input type="number" step="0.01" name="talla">
                    </div>
                    <div>
                        <label>Perímetro cefálico</label>
                        <input type="number" step="0.01" name="perimetro_cefalico">
                    </div>
                </div>
            </div>

            <div id="examen" class="tab-content">
                <div class="form-grid">
                    <div class="full">
                        <label>Enfermedad o problema actual</label>
                        <textarea name="enfermedad_o_problema_actual"></textarea>
                    </div>
                    <div class="full">
                        <label>Examen físico</label>
                        <textarea name="examen_fisico"></textarea>
                    </div>
                    <div class="full">
                        <label>Plan de tratamiento</label>
                        <textarea name="plan_tratamiento"></textarea>
                    </div>
                </div>
            </div>

            <div id="diagnostico" class="tab-content">
                <div class="form-grid">
                    <div>
                        <label>Código CIE-10</label>
                        <input type="text" name="codigo_cie10">
                    </div>
                    <div>
                        <label>Tipo de diagnóstico</label>
                        <select name="tipo_diagnostico">
                            <option value="Definitivo">Definitivo</option>
                            <option value="Presuntivo">Presuntivo</option>
                            <option value="Confirmado">Confirmado</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    <div class="full">
                        <label>Descripción del diagnóstico</label>
                        <textarea name="descripcion"></textarea>
                    </div>
                </div>
            </div>

            <div id="medicacion" class="tab-content">
                <div class="form-grid">
                    <div>
                        <label>Medicamento</label>
                        <input type="text" name="nombre_medicamento">
                    </div>
                    <div>
                        <label>Dosis</label>
                        <input type="text" name="dosis">
                    </div>
                    <div>
                        <label>Frecuencia</label>
                        <input type="text" name="frecuencia">
                    </div>
                    <div>
                        <label>Vía de administración</label>
                        <input type="text" name="via_administracion">
                    </div>
                    <div>
                        <label>Duración</label>
                        <input type="text" name="duracion">
                    </div>
                    <div>
                        <label>Examen ordenado</label>
                        <input type="text" name="nombre_examen">
                    </div>
                    <div>
                        <label>Estudio de imagen</label>
                        <input type="text" name="nombre_estudio">
                    </div>
                    <div>
                        <label>Tipo de imagen</label>
                        <select name="tipo_imagen">
                            <option value="Radiografía">Radiografía</option>
                            <option value="Ecografía">Ecografía</option>
                            <option value="TAC">TAC</option>
                            <option value="Resonancia">Resonancia</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="full">
                        <label>Indicaciones</label>
                        <textarea name="indicaciones"></textarea>
                    </div>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn">Guardar</button>
                <a href="index.php" class="btn btn-secondary">Volver</a>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('.tab').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.tab').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
                button.classList.add('active');
                document.getElementById(button.dataset.target).classList.add('active');
            });
        });

        const cedulaInput = document.getElementById('cedula');
        cedulaInput.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });

        document.getElementById('formularioPaciente').addEventListener('submit', function (event) {
            const cedula = cedulaInput.value;
            if (cedula.length !== 10) {
                event.preventDefault();
                alert('La cédula debe tener 10 dígitos.');
                return;
            }

            const provincia = parseInt(cedula.substring(0, 2), 10);
            if (provincia < 1 || provincia > 24) {
                event.preventDefault();
                alert('La cédula ecuatoriana no es válida.');
                return;
            }

            const coef = [2, 3, 4, 5, 6, 7, 8, 9, 2];
            let suma = 0;
            for (let i = 0; i < 9; i++) {
                let valor = parseInt(cedula.charAt(i), 10) * coef[i];
                if (valor >= 10) {
                    valor = Math.floor(valor / 10) + (valor % 10);
                }
                suma += valor;
            }
            const digitoVerificador = (10 - (suma % 10)) % 10;
            if (parseInt(cedula.charAt(9), 10) !== digitoVerificador) {
                event.preventDefault();
                alert('La cédula ecuatoriana no es válida.');
            }
        });
    </script>
</body>
</html>
