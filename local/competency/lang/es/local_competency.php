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
 * Strings for component 'local_competency', language 'en'
 *
 * @package    local_competency
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname']='Competencia';
$string['actions']='Comportamiento';
$string['activities']='Ocupaciones';
$string['addcohorts']='Agregar cohortes';
$string['addcohortstosync']='Agregar cohortes para sincronizar';
$string['addcompetency']='Agregar competencia';
$string['addcoursecompetencies']='Agregar competencias al curso';
$string['addcrossreferencedcompetency']='Agregar competencia con referencias cruzadas';
$string['addingcompetencywillresetparentrule']='Agregar una nueva competencia eliminará la regla establecida en \'{$a}\'. ¿Quieres continuar?';
$string['addnewcompetency']='Agregar nueva competencia';
$string['addnewcompetencyframework']='Agregar un nuevo marco de competencias';
$string['addnewplan']='Agregar nuevo plan de aprendizaje';
$string['addnewtemplate']='Agregar nueva plantilla de plan de aprendizaje';
$string['addnewuserevidence']='Agregar nueva evidencia';
$string['addtemplatecompetencies']='Agregar competencias a la plantilla del plan de aprendizaje';
$string['aisrequired']='\'{$a}\' es requerido';
$string['aplanswerecreated']='{$a} Se crearon planes de aprendizaje.';
$string['aplanswerecreatedmoremayrequiresync']=' {$a} se crearon planes de aprendizaje; se crearán más durante la próxima sincronización.';
$string['assigncohorts']='Asignar cohortes';
$string['averageproficiencyrate']='La tasa de competencia promedio para los planes de aprendizaje completados basados ​​en esta plantilla es {$a}%';
$string['cancelreviewrequest']='Cancelar solicitud de revisión';
$string['cannotaddrules']='Esta competencia no se puede configurar.';
$string['cannotcreateuserplanswhentemplateduedateispassed']='No se pueden crear nuevos planes de aprendizaje. La fecha de vencimiento de la plantilla ha caducado o está a punto de caducar.';
$string['cannotcreateuserplanswhentemplatehidden']='No se pueden crear nuevos planes de aprendizaje mientras esta plantilla esté oculta.';
$string['category']='Categoría';
$string['chooserating']='Elija una calificación ...';
$string['cohortssyncedtotemplate']='Cohortes sincronizadas con esta plantilla de plan de aprendizaje';
$string['competenciesforframework']='Competencias para {$a}';
$string['competenciesmostoftennotproficient']='Las competencias a menudo no son competentes en los planes de aprendizaje completados';
$string['competenciesmostoftennotproficientincourse']='Competencias que a menudo no son competentes en este curso';
$string['competencycannotbedeleted']='La competencia \'{$a}\'no se puede eliminar';
$string['competencycreated']='Competencia creada';
$string['competencycrossreferencedcompetencies']='{$a}competencias con referencias cruzadas';
$string['competencyframework']='Marco de competencias';
$string['competencyframeworkcreated']='Se creó el marco de competencias.';
$string['competencyframeworkname']='Nombre';
$string['competencyframeworkroot']='Sin padre (competencia de nivel superior)';
$string['competencyframeworks']='Marcos de competencia';
$string['competencyframeworksrepository']='Repositorio de marcos de competencias';
$string['competencyframeworkupdated']='Marco de competencias actualizado.';
$string['competencyoutcome_complete']='Marcar como completo';
$string['competencyoutcome_evidence']='Adjunte una evidencia';
$string['competencyoutcome_none']='Ninguna';
$string['competencyoutcome_recommend']='Recomendar la competencia';
$string['competencypicker']='Selector de competencias';
$string['competencyrule']='Regla de competencia';
$string['competencyupdated']='Competencia actualizada';
$string['completeplan']='Completa este plan de aprendizaje';
$string['completeplanconfirm']='Establecer el plan de aprendizaje \'{$a}\' para completar? Si es así, se registrará el estado actual de las competencias de todos los usuarios y el plan pasará a ser de solo lectura.';
$string['configurecoursecompetencysettings']='Configurar las competencias del curso';
$string['configurescale']='Configurar escalas';
$string['coursecompetencies']='Competencias del curso';
$string['coursecompetencyratingsarenotpushedtouserplans']='Las calificaciones de competencia en este curso no afectan los planes de aprendizaje.';
$string['coursecompetencyratingsarepushedtouserplans']='Las calificaciones de competencia en este curso se actualizan inmediatamente en los planes de aprendizaje.';
$string['coursecompetencyratingsquestion']='Cuando se califica una competencia de un curso, ¿la calificación actualiza la competencia en los planes de aprendizaje o solo se aplica al curso?';
$string['coursesusingthiscompetency']='Cursos vinculados a esta competencia';
$string['coveragesummary']=' {$a->competenciescoveredcount} de {$a->competenciescount} las competencias están cubiertas ({$a->coveragepercentage} %)';
$string['createplans']='Crea planes de aprendizaje';
$string['createlearningplans']='Crea planes de aprendizaje';
$string['crossreferencedcompetencies']='Competencias con referencias cruzadas';
$string['default']='Defecto';
$string['deletecompetency']='Eliminar competencia \'{$a}\'?';
$string['deletecompetencyframework']='Eliminar el marco de competencias \'{$a}\'?';
$string['deletecompetencyparenthasrule']='Eliminar competencia \'{$a}\'? Esto también eliminará el conjunto de reglas para su padre.';
$string['deleteplan']='Eliminar plan de aprendizaje \'{$a}\'?';
$string['deleteplans']='Eliminar los planes de aprendizaje';
$string['deletetemplate']='Eliminar plantilla de plan de aprendizaje \'{$a}\'?';
$string['deletetemplatewithplans']='Esta plantilla tiene planes de aprendizaje asociados. Tienes que indicar cómo procesar esos planes.';
$string['deletethisplan']='Eliminar este plan de aprendizaje';
$string['deletethisuserevidence']='Eliminar esta evidencia';
$string['deleteuserevidence']='Eliminar la evidencia de aprendizaje previo \'{$a}\'?';
$string['description']='Descripción';
$string['duedate']='Fecha de vencimiento';
$string['duedate_help']='La fecha en la que se debe completar un plan de aprendizaje.';
$string['editcompetency']='Editar competencia';
$string['editcompetencyframework']='Editar el marco de competencias';
$string['editplan']='Editar plan de aprendizaje';
$string['editrating']='Editar calificación';
$string['edittemplate']='Editar plantilla de plan de aprendizaje';
$string['editthisplan']='Editar este plan de aprendizaje';
$string['editthisuserevidence']='Edita esta evidencia';
$string['edituserevidence']='Editar evidencia';
$string['evidence']='Evidencia';
$string['findcourses']='Encuentra cursos';
$string['frameworkcannotbedeleted']='El marco de competencias \'{$a}\'no se puede eliminar';
$string['hidden']='Oculto';
$string['hiddenhint']='(oculto)';
$string['idnumber']='número de identificación';
$string['inheritfromframework']='Heredar del marco de competencias (predeterminado)';
$string['itemstoadd']='Elementos para agregar';
$string['jumptocompetency']='Ir a la competencia';
$string['jumptouser']='Ir al usuario';
$string['learningplancompetencies']='Competencias del plan de aprendizaje';
$string['learningplans']='Planes de aprendizaje';
$string['levela']='Nivel{$a}';
$string['linkcompetencies']='Vincular competencias';
$string['linkcompetency']='Competencia de enlace';
$string['linkedcompetencies']='Competencias vinculadas';
$string['linkedcourses']='Cursos vinculados';
$string['linkedcourseslist']='Cursos vinculados:';
$string['listcompetencyframeworkscaption']='Lista de marcos de competencias';
$string['listofevidence']='Lista de pruebas';
$string['listplanscaption']='Lista de planes de aprendizaje';
$string['listtemplatescaption']='Lista de plantillas de planes de aprendizaje';
$string['loading']='Cargando...';
$string['locatecompetency']='Localizar competencia';
$string['managecompetenciesandframeworks']='Administrar competencias y marcos';
$string['modcompetencies']='Competencias del curso';
$string['modcompetencies_help']='Competencias del curso vinculadas a esta actividad.';
$string['move']='Moverse';
$string['movecompetency']='Mover competencia';
$string['movecompetencyafter']='Mueva la competencia después de \'{$a}\'';
$string['movecompetencyframework']='Mover el marco de competencias';
$string['movecompetencytochildofselfwillresetrules']='Mover la competencia eliminará su propia regla y las reglas establecidas para su padre y destino. ¿Quieres continuar?';
$string['movecompetencywillresetrules']='Mover la competencia eliminará las reglas establecidas para su padre y destino. ¿Quieres continuar?';
$string['moveframeworkafter']='Mover el marco de competencias después de \'{$a}\'';
$string['movetonewparent']='Trasladarse';
$string['myplans']='Mis planes de aprendizaje';
$string['nfiles']='{$a} archivo (s)';
$string['noactivities']='Sin actividades';
$string['nocompetencies']='No se han creado competencias en este marco.';
$string['nocompetenciesincourse']='No se han vinculado competencias a este curso.';
$string['nocompetenciesinevidence']='No se han vinculado competencias a esta evidencia.';
$string['nocompetenciesinlearningplan']='No se han vinculado competencias a este plan de aprendizaje.';
$string['nocompetenciesintemplate']='No se ha vinculado ninguna competencia a esta plantilla de plan de aprendizaje.';
$string['nocompetencyframeworks']='Aún no se han creado marcos de competencias.';
$string['nocompetencyselected']='No se seleccionó ninguna competencia';
$string['nocrossreferencedcompetencies']='No se han hecho referencias cruzadas a otras competencias con esta competencia.';
$string['noevidence']='Sin evidencia';
$string['nofiles']='Sin archivos';
$string['nolinkedcourses']='Ningún curso está vinculado a esta competencia';
$string['noparticipants']='No se encontraron participantes.';
$string['noplanswerecreated']='No se crearon planes de aprendizaje.';
$string['notemplates']='Aún no se han creado plantillas de planes de aprendizaje.';
$string['nourl']='Sin URL';
$string['nouserevidence']='Aún no se ha agregado evidencia de aprendizaje previo.';
$string['nouserplans']='Aún no se han creado planes de aprendizaje.';
$string['oneplanwascreated']='Se creó un plan de aprendizaje';
$string['outcome']='Salir';
$string['path']='Camino:';
$string['parentcompetency']='Padre';
$string['parentcompetency_edit']='Editar padre';
$string['parentcompetency_help']='Defina el padre bajo el cual se agregará la competencia. Puede ser otra competencia dentro del mismo marco o la raíz del marco de competencias para una competencia de nivel superior.';
$string['planapprove']='Activar';
$string['plancompleted']='Plan de aprendizaje completado';
$string['plancreated']='Plan de aprendizaje creado';
$string['plandescription']='Descripción';
$string['planname']='Nombre';
$string['plantemplate']='Seleccionar plantilla de plan de aprendizaje';
$string['plantemplate_help']='Un plan de aprendizaje creado a partir de una plantilla contendrá una lista de competencias que coinciden con la plantilla. Las actualizaciones de la plantilla se reflejarán en cualquier plan creado a partir de esa plantilla.';
$string['planunapprove']='Enviar de nuevo al borrador';
$string['planupdated']='Plan de aprendizaje actualizado';
$string['points']='Puntos';
$string['pointsgivenfor']='Puntos otorgados por \'{$a}\'';
$string['proficient']='Competente';
$string['progress']='Progreso';
$string['rate']='Velocidad';
$string['ratecomment']='Notas de evidencia';
$string['rating']='Clasificación';
$string['ratingaffectsonlycourse']='La calificación de una competencia solo actualiza la competencia en este curso';
$string['ratingaffectsuserplans']='La calificación de una competencia también actualiza la competencia en todos los planes de aprendizaje.';
$string['reopenplan']='Reabrir este plan de aprendizaje';
$string['reopenplanconfirm']='Reabrir el plan de aprendizaje \'{$a}\'? Si es así, el estado de las competencias de los usuarios que se registró en el momento en que se completó previamente el plan se eliminará y el plan se activará nuevamente.';
$string['requestreview']='Solicitar revisión';
$string['reviewer']='Crítico';
$string['reviewstatus']='Estado de revisión';
$string['savechanges']='Guardar cambios';
$string['scale']='Escala';
$string['scale_help']='Una escala determina cómo se mide la competencia en una competencia. Después de seleccionar una escala, es necesario configurarla. 
* El elemento seleccionado como \'Predeterminado \' es la calificación que se otorga cuando una competencia se completa automáticamente. 
* Los elementos seleccionados como \'Competente \' indican cuál (es) Los valores marcarán las competencias como competentes cuando se califiquen.';
$string['scalevalue']='Valor de escala';
$string['search']='Buscar...';
$string['selectcohortstosync']='Seleccionar cohortes para sincronizar';
$string['selectcompetencymovetarget']='Seleccione una ubicación para mover esta competencia a:';
$string['selectedcompetency']='Competencia seleccionada';
$string['selectuserstocreateplansfor']='Seleccionar usuarios para crear planes de aprendizaje';
$string['sendallcompetenciestoreview']='Envíe todas las competencias en revisión para obtener evidencia de aprendizaje previo \'{$a}\'';
$string['sendcompetenciestoreview']='Enviar competencias para revisión';
$string['shortname']='Nombre';
$string['sitedefault']='(Sitio predeterminado)';
$string['startreview']='Iniciar revisión';
$string['state']='Estado';
$string['status']='Estado';
$string['stopreview']='Revision terminada';
$string['stopsyncingcohort']='Dejar de sincronizar la cohorte';
$string['taxonomies']='Taxonomías';
$string['taxonomy_add_behaviour']='Agregar comportamiento';
$string['taxonomy_add_competency']='Agregar competencia';
$string['taxonomy_add_concept']='Agregar concepto';
$string['taxonomy_add_domain']='Agregar dominio';
$string['taxonomy_add_indicator']='Agregar indicador';
$string['taxonomy_add_level']='Agregar nivel';
$string['taxonomy_add_outcome']='Agregar resultado';
$string['taxonomy_add_practice']='Agregar práctica';
$string['taxonomy_add_proficiency']='Agregar competencia';
$string['taxonomy_add_skill']='Agregar habilidad';
$string['taxonomy_add_value']='Añadir valor';
$string['taxonomy_edit_behaviour']='Editar comportamiento';
$string['taxonomy_edit_competency']='Editar competencia';
$string['taxonomy_edit_concept']='Editar concepto';
$string['taxonomy_edit_domain']='Editar dominio';
$string['taxonomy_edit_indicator']='Indicador de edición';
$string['taxonomy_edit_level']='Nivel de edición';
$string['taxonomy_edit_outcome']='Editar resultado';
$string['taxonomy_edit_practice']='Editar práctica';
$string['taxonomy_edit_proficiency']='Editar competencia';
$string['taxonomy_edit_skill']='Editar habilidad';
$string['taxonomy_edit_value']='Editar valor';
$string['taxonomy_parent_behaviour']='Comportamiento de los padres';
$string['taxonomy_parent_competency']='Competencia de los padres';
$string['taxonomy_parent_concept']='Concepto de padre';
$string['taxonomy_parent_domain']='Dominio principal';
$string['taxonomy_parent_indicator']='Indicador principal';
$string['taxonomy_parent_level']='Nivel de padres';
$string['taxonomy_parent_outcome']='Resultado de los padres';
$string['taxonomy_parent_practice']='Práctica de los padres';
$string['taxonomy_parent_proficiency']='Competencia de los padres';
$string['taxonomy_parent_skill']='Habilidad de los padres';
$string['taxonomy_parent_value']='Valor de los padres';
$string['taxonomy_selected_behaviour']='Comportamiento seleccionado';
$string['taxonomy_selected_competency']='Competencia seleccionada';
$string['taxonomy_selected_concept']='Concepto seleccionado';
$string['taxonomy_selected_domain']='Dominio seleccionado';
$string['taxonomy_selected_indicator']='Indicador seleccionado';
$string['taxonomy_selected_level']='Nivel seleccionado';
$string['taxonomy_selected_outcome']='Resultado seleccionado';
$string['taxonomy_selected_practice']='Práctica seleccionada';
$string['taxonomy_selected_proficiency']='Competencia seleccionada';
$string['taxonomy_selected_skill']='Habilidad seleccionada';
$string['taxonomy_selected_value']='Valor seleccionado';
$string['template']='Plantilla de plan de aprendizaje';
$string['templatebased']='Basado en plantillas';
$string['templatecohortnotsyncedwhileduedateispassed']='Las cohortes no se sincronizarán si ha pasado la fecha de vencimiento de la plantilla.';
$string['templatecohortnotsyncedwhilehidden']='Las cohortes no se sincronizarán mientras esta plantilla esté oculta.';
$string['templatecompetencies']='Competencias de la plantilla del plan de aprendizaje';
$string['templatecreated']='Plantilla de plan de aprendizaje creada';
$string['templatename']='Nombre';
$string['templates']='Plantillas de planes de aprendizaje';
$string['templateupdated']='Plantilla de plan de aprendizaje actualizada';
$string['totalrequiredtocomplete']='Total requerido para completar';
$string['unlinkcompetencycourse']='Desvincular la competencia \'{$a}\' del curso?';
$string['unlinkcompetencyplan']='Desvincular la competencia \'{$a}\' del plan de aprendizaje?';
$string['unlinkcompetencytemplate']='Desvincular la competencia \'{$a}\' de la plantilla del plan de aprendizaje?';
$string['unlinkplanstemplate']='Desvincular los planes de aprendizaje de su plantilla';
$string['unlinkplantemplate']='Desvincular de la plantilla del plan de aprendizaje';
$string['unlinkplantemplateconfirm']='Desvincular el plan de aprendizaje \'{$a}\' de su plantilla? Cualquier cambio realizado en la plantilla ya no se aplicará al plan. Esta acción no se puede deshacer.';
$string['uponcoursecompletion']='Al finalizar el curso:';
$string['uponcoursemodulecompletion']='Al finalizar la actividad:';
$string['usercompetencyfrozen']='Este registro ahora está congelado. Refleja el estado de la competencia del usuario cuando su plan de aprendizaje se marcó como completo.';
$string['userevidence']='Evidencia de aprendizaje previo';
$string['userevidencecreated']='Evidencia de aprendizaje previo creado';
$string['userevidencedescription']='Descripción';
$string['userevidencefiles']='Archivos';
$string['userevidencename']='Nombre';
$string['userevidencesummary']='Resumen';
$string['userevidenceupdated']='Evidencia de aprendizaje previo actualizada';
$string['userevidenceurl']='URL';
$string['userevidenceurl_help']='La URL debe comenzar con \'http: // \' o \'https: // \'.';
$string['viewdetails']='Ver detalles';
$string['visible']='Visible';
$string['visible_help']='Un marco de competencias se puede ocultar mientras se configura o se actualiza a una nueva versión.';
$string['when']='Cuando';
$string['xcompetencieslinkedoutofy']='{$a->x} fuera de {$a->y} competencias vinculadas a los cursos';
$string['xcompetenciesproficientoutofy']='{$a->x} fuera de {$a->y} las competencias son competentes';
$string['xcompetenciesproficientoutofyincourse']='Eres competente en {$a->x} fuera de {$a->y} competencias en este curso.';
$string['xplanscompletedoutofy']=' {$a->x} fuera de {$a->y} planes de aprendizaje completados para esta plantilla';
$string['nocompetency']='No hay competencia disponible';
$string['competencyview']='competencia';
$string['local_competencyview_desc']='Dos vistas,<br/>

Una es la vista básica, que muestra solo la competencia asignada por el usuario en breve.

</br>Otra vista avanzada, muestra la lista de cursos y la lista de actividades asignadas a la competencia';
$string['left_menu_mycompetency']='Mis competencias';
$string['competencyparents']='padres';
$string['activitiesassignedtocompetency']='Actividades asignadas a la competencia';
$string['availableactivitiesinthecourse']='Actividades disponibles en el curso';