<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for local_recompletion
 *
 * @package    local_recompletion
 * @copyright  2017 Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname']='Recuperación del curso';
$string['recompletion']='recompletar';
$string['editrecompletion']='Editar la configuración de finalización del curso';
$string['enablerecompletion']='Habilitar la recompletación';
$string['enablerecompletion_help']='El complemento de recompletación permite restablecer los detalles de finalización de un curso después de un período definido.';
$string['recompletionrange']='Período de recompletación';
$string['recompletionrange_help']='Establezca el período de tiempo antes de que se restablezcan los resultados de finalización de un usuario.';
$string['recompletionsettingssaved']='Se guardó la configuración de recompletado';
$string['recompletion:manage']='Permitir que se modifique la configuración de recompletación del curso';
$string['recompletion:resetmycompletion']='Restablecer mi propia finalización';
$string['resetmycompletion']='Restablecer la finalización de mi actividad';
$string['recompletiontask']='Compruebe si hay usuarios que necesiten volver a completar';
$string['completionnotenabled']='La finalización no está habilitada en este curso';
$string['recompletionnotenabled']='La recompletación no está habilitada en este curso';
$string['recompletionemailenable']='Enviar mensaje de recompletado';
$string['recompletionemailenable_help']='Habilite la mensajería de correo electrónico para notificar a los usuarios que es necesario volver a completar';
$string['recompletionemailsubject']='Asunto del mensaje de recompletado';
$string['recompletionemailsubject_help']='Se puede agregar un asunto de correo electrónico de recompletado personalizado como texto sin formato Los siguientes marcadores de posición se pueden incluir en el mensaje: * Nombre del curso {$a->coursename}* Nombre completo del usuario {$a->fullname}';
$string['recompletionemaildefaultsubject']='Curso {$a->coursename} se requiere recompletar';
$string['recompletionemailbody']='Cuerpo del mensaje de recompletado';
$string['recompletionemailbody_help']='Se puede agregar un asunto de correo electrónico de recompletado personalizado como texto sin formato o en formato Moodle-auto, incluidas las etiquetas HTML y las etiquetas de varios idiomas. {$a->coursename}* Enlace al curso {$a->link}* Enlace a la página de perfil del usuario {$a->profileurl}* Correo electrónico del usuario {$a->email}* Nombre completo del usuario {$a->fullname}';
$string['recompletionemaildefaultbody']='Hola, vuelve a completar el curso. {$a->coursename} {$a->link}';
$string['advancedrecompletiontitle']='Avanzado';
$string['deletegradedata']='Eliminar todas las calificaciones del usuario';
$string['deletegradedata_help']='Elimina los datos de finalización de calificaciones actuales de la tabla grade_grades. Los datos de recompletar calificaciones se eliminan permanentemente, pero los datos se conservan en la tabla de datos del historial de calificaciones.';
$string['archivecompletiondata']='Archivar datos de finalización';
$string['archivecompletiondata_help']='Escribe datos de finalización en las tablas local_recompletion_cc, local_recompletion_cc_cc y local_recompletion_cmc. Los datos de finalización se eliminarán de forma permanente si no se selecciona.';
$string['emailrecompletiontitle']='Configuración personalizada del mensaje de recompletado';
$string['eventrecompletion']='Recuperación del curso';
$string['assignattempts']='Asignar intentos';
$string['assignattempts_help']='Cómo manejar los intentos de asignación dentro del curso.';
$string['extraattempt']='Dar al estudiante intentos adicionales';
$string['quizattempts']='Intentos de prueba';
$string['quizattempts_help']='Qué hacer con los intentos de prueba existentes. Si se selecciona eliminar y archivar, los intentos de prueba antiguos se archivarán en las tablas de local_recompletion; si se configura para dar intentos adicionales, esto agregará una anulación de prueba para permitir que el usuario tenga establecido el número máximo de intentos permitidos.';
$string['scormattempts']='Intentos de SCORM';
$string['scormattempts_help']='Si se eliminan los intentos de SCORM existentes, si se selecciona el archivo, los intentos de SCORM anteriores se archivarán en la tabla local_recompletion_sst.';
$string['archive']='Archivar intentos antiguos';
$string['delete']='Eliminar intentos existentes';
$string['donothing']='Hacer nada';
$string['resetmycompletionconfirm']='¿Está seguro de que desea restablecer todos los datos de finalización de este curso? Advertencia: esto puede eliminar permanentemente parte del contenido enviado.';
$string['completionreset']='Su finalización en este curso se ha restablecido.';
$string['privacy:metadata:local_recompletion_cc']='Archivo de finalizaciones de cursos anteriores.';
$string['privacy:metadata:local_recompletion_cmc']='Archivo de finalizaciones de módulos de cursos anteriores.';
$string['privacy:metadata:local_recompletion_cc_cc']='Archivo de course_completion_crit_compl anterior';
$string['privacy:metadata:userid']='El ID de usuario vinculado a esta tabla.';
$string['privacy:metadata:course']='El ID del curso vinculado a esta tabla.';
$string['privacy:metadata:timecompleted']='La hora en que se completó el curso.';
$string['privacy:metadata:timeenrolled']='La hora en que el usuario estuvo inscrito en el curso.';
$string['privacy:metadata:timemodified']='La hora en que se modificó el registro';
$string['privacy:metadata:timestarted']='La hora en que se inició el curso.';
$string['privacy:metadata:coursesummary']='Almacena los datos de finalización del curso de un usuario.';
$string['privacy:metadata:gradefinal']='Calificación final recibida por la finalización del curso';
$string['privacy:metadata:overrideby']='El ID de usuario de la persona que anuló la finalización de la actividad.';
$string['privacy:metadata:reaggregate']='Si la finalización del curso se volvió a agregar.';
$string['privacy:metadata:unenroled']='Si el usuario ha sido dado de baja del curso';
$string['privacy:metadata:quiz_attempts']='Detalles archivados sobre cada intento de una prueba.';
$string['privacy:metadata:quiz_attempts:attempt']='El número de intento.';
$string['privacy:metadata:quiz_attempts:currentpage']='La página actual en la que se encuentra el usuario.';
$string['privacy:metadata:quiz_attempts:preview']='Si se trata de una vista previa del cuestionario.';
$string['privacy:metadata:quiz_attempts:state']='El estado actual del intento.';
$string['privacy:metadata:quiz_attempts:sumgrades']='La suma de calificaciones en el intento.';
$string['privacy:metadata:quiz_attempts:timecheckstate']='La hora en que se controló el estado.';
$string['privacy:metadata:quiz_attempts:timefinish']='La hora en que se completó el intento.';
$string['privacy:metadata:quiz_attempts:timemodified']='La hora a la que se actualizó el intento.';
$string['privacy:metadata:quiz_attempts:timemodifiedoffline']='La hora a la que se actualizó el intento mediante una actualización sin conexión.';
$string['privacy:metadata:quiz_attempts:timestart']='La hora en que se inició el intento.';
$string['privacy:metadata:quiz_grades']='Detalles archivados sobre la calificación general de intentos anteriores de cuestionarios.';
$string['privacy:metadata:quiz_grades:grade']='La calificación general de este cuestionario.';
$string['privacy:metadata:quiz_grades:quiz']='El cuestionario que se calificó.';
$string['privacy:metadata:quiz_grades:timemodified']='La hora en que se modificó la calificación.';
$string['privacy:metadata:quiz_grades:userid']='El usuario que fue calificado.';
$string['privacy:metadata:scoes_track:element']='El nombre del elemento que se va a rastrear';
$string['privacy:metadata:scoes_track:value']='El valor del elemento dado';
$string['privacy:metadata:coursemoduleid']='El ID de actividad';
$string['privacy:metadata:completionstate']='Si la actividad se ha completado';
$string['privacy:metadata:viewed']='Si la actividad fue vista';
$string['privacy:metadata:attempt']='El número de intento';
$string['privacy:metadata:scorm_scoes_track']='Archivo de los datos rastreados de los SCOes pertenecientes a la actividad';
$string['noassigngradepermission']='Se restableció su finalización, pero este curso contiene una tarea que no se pudo restablecer. Pídale a su maestro que lo haga por usted si es necesario.';
