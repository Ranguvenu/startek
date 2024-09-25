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
 * Language file.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $OUTPUT;
defined('MOODLE_INTERNAL') || die();
$string['selecttype']	='Seleccione tipo';
$string['Course']	='Curso';
$string['Competencies']	='Competencias';
$string['Classrooms']	='Aulas';
$string['learning plans']	='planes de aprendizaje';
$string['badgetype']	='Tipo de insignia';
$string['invalidformatpoints']	='Los puntos deben estar en formato numérico';
$string['nobadgesavailiable']	='No hay insignias activas';
$string['Redeem']	='Canjea';
$string['Myredeem']	='Mis redime';
$string['badgegroupidmissing']	='El grupo Bagde no puede estar vacío';
$string['nobadgemessage']	='(Aún no se han creado insignias)';
$string['notactive']	='Active la insignia para lograrlo';
$string['noimage']	='No se seleccionó ninguna imagen de insignia';
$string['badgenamemissing']	='El nombre de la insignia no puede estar vacío';
$string['badgeshortnamemissing']	='El nombre corto de la insignia no puede estar vacío';
$string['pointsmissing']	='Los puntos no pueden estar vacíos';
$string['duration_help']	='Ayuda a calcular los puntos para una duración determinada';
$string['duration']	='Duración';
$string['active_help']	='Active la insignia de modo que pueda obtenerse después de obtener los puntos requeridos';
$string['leaderboard_context']	='Nivel de clasificación';
$string['leaderboard_context_help']	='Seleccione el nivel en el que se debe mostrar el nivel';
$string['fromtime_error']	='La fecha no puede ser superior a la hora actual';
$string['leaderboardsettings']	='Configuración de gamificación';
$string['leaderboardlevel']	='Nivel de clasificación';
$string['shortname']	='Nombre corto';
$string['active']	='Activo';
$string['badgegroup']	='Grupo de insignias';
$string['badgename']	='Placa de identificación';
$string['points']	='Puntos';
$string['badgeimage']	='Imagen de la insignia';
$string['settings']	='Configuración general';
$string['addevents']	='Agregar eventos';
$string['badgeview']	='Insignias';
$string['addbadges']	='Agregar insignias';
$string['editbadges']	='Editar insignias';
$string['gamification_dashboard']	='Panel de gamificación';
$string['pointsrewarded']	='Eventos';
$string['badges']	='Insignias';
$string['createevents']	='Crear eventos';
$string['viewbadges']	='Ver insignias';
$string['submit']	='Enviar';
$string['navleaderboard']	='Configuraciones de gamificación';
$string['actions']	='Comportamiento';
$string['activitycompletion']	='Finalización de actividad';
$string['activitycompletionis'] = '<span class=\'custom_gamification_rule ruletype_completetion\'>{$a} Está marcada completa
<span class=\'error_completion_setting alert alert-danger hidden\'>Necesaria.</span></span>';
$string['activityoresourceis']	='La actividad o recurso es{$a}';
$string['addacondition']	='Agregar una condición';
$string['addarule']	='Agregar una regla';
$string['admindefaultrulesintro']	='Las siguientes reglas se utilizarán por defecto para los cursos en los que se agrega el bloque.';
$string['admindefaultsettingsintro']	='La configuración a continuación se utilizará como predeterminada cuando el bloque se agregue por primera vez a un curso.';
$string['admindefaultsettingsintro'] = 'The settings below will be used as defaults when the block is newly added to a course.';
$string['admindefaultvisualsintro']	='Lo siguiente se usará como predeterminado cuando el bloque se agregue nuevamente a un curso.';
$string['anonymity']	='Anonimato';
$string['anonymity_help']	='Esta configuración controla si los participantes pueden ver el nombre y el avatar de los demás.';
$string['awardaxpwhen'] = '<strong>{$a}</strong> los puntos se ganan cuando:';
$string['basexp']	='Base de algoritmo';
$string['blockappearance']	='Apariencia de bloque';
$string['cachedef_filters']	='Filtros de nivel';
$string['cachedef_ruleevent_eventslist']	='Lista de algunos eventos';
$string['cannotshowblockconfig']	='Por lo general, mostraría la configuración de apariencia aquí, pero no pude encontrar su bloque. Para cambiar la apariencia del bloque, regrese [aquí] ({$ a}) (o donde agregó el bloque), active el modo de edición y siga la opción "Configurar" en el menú desplegable del bloque. Si no puede encontrar el bloque, vuelva a agregarlo a su curso.';
$string['cheatguard']	='Guardia de trampa';
$string['colon']	='{$a->a}:{$a->b}';
$string['coefxp']	='Coeficiente de algoritmo';
$string['configdescription']	='Introducción';
$string['configdescription_help']	='Una breve introducción que se muestra en el bloque, por debajo del nivel del alumno. Los estudiantes tienen la capacidad de descartar el mensaje, en cuyo caso no lo volverán a ver.';
$string['configheader']	='Configuraciones';
$string['configtitle']	='Título';
$string['configtitle_help']	='El título del bloque.';
$string['configrecentactivity']	='Mostrar recompensas recientes';
$string['configrecentactivity_help']	='Cuando está habilitado, el bloque mostrará una breve lista de eventos recientes que recompensaron al estudiante con puntos.';
$string['congratulationsyouleveledup']	='Felicidades!';
$string['coolthanks']	='Genial, gracias!';
$string['courselog']	='Registro del curso';
$string['coursereport']	='Reporte del curso';
$string['courserules']	='Reglas del curso';
$string['coursesettings']	='Configuración general';
$string['coursevisuals']	='Insignias';
$string['customizelevels']	='Personaliza los niveles';
$string['dangerzone']	='Zona peligrosa';
$string['defaultlevels']	='Niveles predeterminados';
$string['defaultrules']	='Reglas predeterminadas';
$string['defaultrulesformhelp']	='Esas son las reglas predeterminadas proporcionadas por el complemento, dan automáticamente puntos predeterminados e ignoran algunos eventos redundantes. Tus propias reglas tienen prioridad sobre ellas.';
$string['defaultsettings']	='Configuración por defecto';
$string['defaultvisuals']	='Imágenes predeterminadas';
$string['deletecondition']	='Eliminar condición';
$string['deleterule']	='Eliminar regla';
$string['description']	='Descripción';
$string['difference']	='Dif.';
$string['discoverlevelupplus']	='¡Descubre Level up! Más';
$string['dismissnotice']	='Descartar aviso';
$string['displayeveryone']	='Mostrar a todos';
$string['displaynneighbours']	='Monitor{$a}vecinos';
$string['displayoneneigbour']	='Mostrar un vecino';
$string['displayparticipantsidentity']	='Mostrar la identidad de los participantes';
$string['displayrank']	='Rango de visualización';
$string['displayrelativerank']	='Mostrar una clasificación relativa';
$string['enablecheatguard']	='Habilitar guardia de trampas';
$string['enablecheatguard_help']	='El protector contra trampas ofrece un mecanismo simple y económico para evitar que los estudiantes abusen del sistema utilizando técnicas obvias, como actualizar la misma página sin cesar o repetir la misma acción una y otra vez.';
$string['enableinfos']	='Habilitar la página de información';
$string['enableinfos_help']	='Cuando se establece en \'No \', los estudiantes no podrán ver la página de información.
';
$string['enableladder']	='Habilita la escalera';
$string['enableladder_help']	='Cuando se establece en \'No \', los estudiantes no podrán ver la escalera.';
$string['enablelevelupnotif']	='Habilitar notificación de gamificación';
$string['enablelevelupnotif_help']	='Cuando se establece en \'Sí \', se mostrará a los estudiantes una ventana emergente felicitándolos por el nuevo nivel alcanzado.';
$string['enablexpgain']	='Habilitar la ganancia de puntos';
$string['enablexpgain_help']	='Cuando se establece en \'No \', nadie ganará puntos en el curso. Esto es útil para congelar los puntos ganados, o para habilitarlo en un momento determinado. Tenga en cuenta que esto también se puede controlar de manera más granular utilizando la capacidad _block / gamification: gaingamification_.';
$string['errorformvalues']	='Hay algunos problemas en los valores del formulario, corríjalos.';
$string['errorlevelsincorrect']	='El número mínimo de niveles es 2';
$string['errornotalllevelsbadgesprovided']	='No se han proporcionado todas las insignias de nivel. Desaparecido:{$a}';
$string['errorunknownevent']	='Error: evento desconocido';
$string['errorunknownmodule']	='Error: módulo desconocido';
$string['errorxprequiredlowerthanpreviouslevel']	='Los puntos requeridos son inferiores o iguales al nivel anterior.';
$string['eventis']	='El evento es{$a}';
$string['event_user_leveledup']	='Usuario subido de nivel';
$string['eventname']	='Nombre del evento';
$string['eventproperty']	='Propiedad del evento';
$string['eventtime']	='Hora del evento';
$string['for1day']	='Por 1 día';
$string['for1month']	='Por un mes';
$string['for1week']	='Durante una semana';
$string['for3days']	='Por 3 dias';
$string['forever']	='Siempre';
$string['forthewholesite']	='Para todo el sitio';
$string['give']	='dar';
$string['hideparticipantsidentity']	='Ocultar la identidad de los participantes';
$string['hiderank']	='Ocultar rango';
$string['incourses']	='En cursos';
$string['infos']	='Información';
$string['invalidxp']	='Valor de puntos no válido';
$string['keeplogs']	='Mantener registros';
$string['ladder']	='Reporte';
$string['ladderadditionalcols']	='Columnas adicionales';
$string['ladderadditionalcols_help']	='Esta configuración determina qué columnas adicionales se muestran en la escalera. Presione la tecla CTRL o CMD mientras hace clic para seleccionar más de una columna, o para deseleccionar una columna seleccionada.';
$string['level']	='Nivel';
$string['levelbadges']	='Insignias de nivel';
$string['levelbadgesformhelp']	='Nombra los archivos [nivel]. [Extensión de archivo], por ejemplo: 1.png, 2.jpg, etc. El tamaño de imagen recomendado es 100x100.';
$string['levelcount']	='Recuento de niveles';
$string['leveldesc']	='Descripción de nivel';
$string['levels']	='Configuración de niveles';
$string['levelup']	='Gamificación';
$string['levelupplus']	='Gamificación Plus';
$string['levelx']	='Nivel #{$a}';
$string['likenotice'] = '<strong>Te gusta el plugin?</strong> Tómese un momento para <a href="{$a->moodleorg}" target="_blank">Agrégalo a tus favoritas</a> on Moodle.org and <a href="{$a->github}" target="_blank">protagonizarlo GitHub</a>.';
$string['limitparticipants'] = 'Limit participants';
$string['limitparticipants_help'] = 'This setting controls who is displayed in the leaderboard. Neighbours are the participants ranked above and below the current user. For instance, when choosing \'Display 2 neighbours\', only the two participants ranked directly higher and lower than the current user will be displayed.';
$string['logging']	='Inicio sesión';
$string['maxactionspertime']	='Max. acciones en el marco de tiempo';
$string['maxactionspertime_help']	='El número máximo de acciones que contarán como puntos durante el período de tiempo dado. Se ignorará cualquier acción posterior. Cuando este valor está vacío o es igual a cero, no se aplica.';
$string['movecondition']	='Mover condición';
$string['moverule']	='Mover regla';
$string['navinfos']	='Información';
$string['navladder']	='Escalera';
$string['navlevels']	='Configuración de niveles';
$string['navlog']	='Iniciar sesión';
$string['navpromo']	='Más';
$string['navreport']	='Reporte';
$string['navrules']	='Reglas';
$string['navsettings']	='Configuración general';
$string['navvisuals']	='Configuración de la insignia';
$string['participant']	='Partícipe';
$string['pickaconditiontype']	='Elija un tipo de condición';
$string['pluginname']	='Gamificación';
$string['pointsintimelinker']	='por';
$string['pointsrequired']	='Puntos requeridos';
$string['progress']	='Progreso';
$string['property:action']	='Acción de evento';
$string['property:component']	='Componente de evento';
$string['property:crud']	='Evento CRUD';
$string['property:eventname']	='Nombre del evento';
$string['property:target']	='Destino del evento';
$string['promocontactintro']	='Contáctenos para más información. ¡No mordemos y respondemos rápidamente!';
$string['promocontactus']	='Ponerse en contacto';
$string['promoemailusat']	='Envíenos un correo electrónico a _levelup@branchup.tech_.';
$string['promoerrorsendingemail']	='¡Ay! No pudimos enviar el mensaje ... envíenos un correo electrónico directamente a:{$a}. ¡Gracias!';
$string['promointro']	='¡El complemento de _¡Level up! _ Que desata todo su potencial.';
$string['promoifpreferemailusat']	='Psst! Si lo prefiere, envíenos un correo electrónico directamente a _{$a}.';
$string['promoyourmessagewassent']	='Gracias, su mensaje fue enviado. Nos comunicaremos contigo en breve.';
$string['participatetolevelup']	='Participa en el curso para ganar puntos de experiencia y Gamificación';
$string['rank']	='Rango';
$string['ranking']	='Clasificación';
$string['ranking_help']	='El rango es la posición absoluta del usuario actual en la escalera. El rango relativo es la diferencia en los puntos de experiencia entre un usuario y sus vecinos.';
$string['recentrewards']	='Recompensas recientes';
$string['reallyresetdata']	='¿Realmente restablecer los niveles y puntos de todos en este curso?';
$string['reallyresetgroupdata']	='¿Realmente restablecer los niveles y puntos de todos en este grupo?';
$string['reallyreverttopluginsdefaults']	='¿Realmente restablece las reglas predeterminadas a los valores predeterminados sugeridos por el complemento? Esta acción no es reversible.';
$string['resetcoursedata']	='Restablecer datos del curso';
$string['resetgroupdata']	='Restablecer datos de grupo';
$string['reward']	='Recompensa';
$string['requires']	='Requiere';
$string['reverttopluginsdefaults']	='Volver a los valores predeterminados del complemento';
$string['reverttopluginsdefaultsintro']	='Utilice el botón a continuación si desea volver a los valores predeterminados del complemento.';
$string['rule']	='Regla';
$string['rule:contains']	='contiene';
$string['rule:eq']	='es igual a';
$string['rule:eqs']	='es estrictamente igual a';
$string['rule:gt']	='es mayor que';
$string['rule:gte']	='es mayor o igual a';
$string['rule:lt']	='es menos que';
$string['rule:lte']	='es menor o igual a';
$string['rule:regex']	='coincide con la expresión regular';
$string['rulecm']	='Actividad o recurso';
$string['rulecmdesc']	='La actividad o recurso es \'{$a->contextname}\'.';
$string['rulecompletiondesc']	='\'{$a->contextname}\' está marcado como completo.';
$string['ruleevent']	='Evento especifico';
$string['ruleeventdesc']	='El evento es \'{$a->eventname}\'';
$string['ruleproperty']	='Propiedad del evento';
$string['rulepropertydesc']	='La propiedad \'{$a->property}\' {$a->compare} \'{$a->value}\'.';
$string['ruleset']	='Conjunto de condiciones';
$string['ruleset:all']	='TODAS las condiciones son verdaderas';
$string['ruleset:any']	='CUALQUIERA de las condiciones es verdadera';
$string['ruleset:none']	='NINGUNA de las condiciones es verdadera';
$string['rulesformhelp'] = '<p>Este complemento hace uso de los eventos para atribuir puntos a las acciones realizadas por los estudiantes. Puede usar el formulario a continuación para agregar sus propias reglas y ver las predeterminadas.</p>
<p>Se recomienda verificar el complemento <a href="{$a->log}">log</a> para identificar qué eventos se activan a medida que realiza acciones en el curso, y también para leer más sobre los eventos en sí: <a href="{$a->list}">list of all events</a>, <a href="{$a->doc}">documentación para desarrolladores</a>.</p>
<p>Finalmente, tenga en cuenta que el complemento siempre ignora:
<ul>
    <li>Las acciones realizadas por administradores, invitados o usuarios no registrados.</li>
    <li>Las acciones realizadas por usuarios que no tienen la capacidad <em>block/gamification:earngamification</em>.</li>
    <li>Acciones repetidas en un breve intervalo de tiempo para evitar trampas.</li>
    <li>Eventos marcados como <em>anónima</em>, e.g. en un comentario anónimo.</li>
    <li>Y los eventos de nivel educativo no igualan a <em>Participativa</em>.</li>
</ul>
</p>';
$string['send']	='Enviar';
$string['someoneelse']	='Alguien más';
$string['somethinghappened']	='Algo pasó';
$string['taskcollectionloggerpurge']	='Purgar registros de recolección';
$string['total']	='Total';
$string['timebetweensameactions']	='Tiempo requerido entre acciones idénticas';
$string['timebetweensameactions_help']	='Se acepta nuevamente el tiempo mínimo requerido antes de que una acción que ya sucedió anteriormente. Una acción se considera idéntica si se colocó en el mismo contexto y objeto, la lectura de una publicación en el foro se considerará identifical si se vuelve a leer la misma publicación. Cuando este valor está vacío o es igual a cero, no se aplica.';
$string['timeformaxactions']	='Marco de tiempo para máx. comportamiento';
$string['timeformaxactions_help']	='El período de tiempo (en segundos) durante el cual el usuario no debe exceder un número máximo de acciones.';
$string['tinytimenow']	='ahora';
$string['tinytimeseconds']	='{$a}s';
$string['tinytimeminutes']	='{$a}metro';
$string['tinytimehours']	='{$a}h';
$string['tinytimedays']	='{$a}re';
$string['tinytimeweeks']	='{$a}w';
$string['tinytimewithinayearformat']	='% b% e';
$string['tinytimeolderyearformat']	='% b% Y';
$string['value']	='Valor';
$string['valuessaved']	='Los valores se han guardado correctamente.';
$string['visualsintro']	='Sube imágenes para personalizar la apariencia de los niveles.';
$string['wherearexpused']	='¿Dónde se utilizan los puntos?';
$string['wherearexpused_desc']	='Cuando se establece en \'En cursos \', los puntos obtenidos solo tendrán en cuenta el curso en el que se agregó el bloque. Cuando se establece en \'Para todo el sitio \', un usuario "subirá de nivel" en el sitio en lugar de selectivamente por curso, se utilizarán todos los puntos obtenidos en todo el sitio.';
$string['updateandpreview']	='Actualización y vista previa';
$string['urlaccessdeprecated']	='El acceso a través de esta URL está obsoleto, actualice sus enlaces.';
$string['usealgo']	='Usa el algoritmo';
$string['usecustomlevelbadges']	='Usa insignias de nivel personalizadas';
$string['usecustomlevelbadges_help']	='Cuando se establece en sí, debe proporcionar una imagen para cada nivel.';
$string['when']	='Cuando';
$string['whoops']	='¡Ups!';
$string['wewillreplyat']	='Responderemos a: _{$a}_.';
$string['gamification:addinstance']	='Agregar un nuevo bloque';
$string['gamification:earngamification']	='Ganar puntos';
$string['gamification:myaddinstance']	='Agregar el bloque a mi tablero';
$string['gamification:view']	='Ver el bloque y sus páginas relacionadas';
$string['xptogo']	='[[{$a}]] ir';
$string['gamificationgaindisabled']	='Ganancia de puntos inhabilitada';
$string['youreachedlevela']	='Has alcanzado el nivel{$a}!';
$string['yourmessage']	='Tu mensaje';
$string['yourownrules']	='Tus propias reglas';
$string['addrulesformhelp']	='La última columna define la cantidad de puntos de experiencia obtenidos cuando se cumplen los criterios.';
$string['changelevelformhelp']	='Si cambia el número de niveles, las insignias de nivel personalizado se desactivarán temporalmente para evitar niveles sin insignias. Si cambia el recuento de niveles, vaya a la página \'Visuales \' para volver a habilitar las insignias personalizadas una vez que haya guardado este formulario.';
$string['enablelogging']	='Habilitar el registro';
$string['levelswillbereset']	='¡Advertencia! ¡Al guardar este formulario se volverán a calcular los niveles de todos!';
$string['viewtheladder']	='Ver la escalera';
$string['xp']	='Puntos de experiencia';
$string['xprequired']	='se requiere gamificación';
$string['userbadgepage']	='Insignias de empleado';
$string['type']	='Tipo';
$string['event']	='Evento';
$string['selectreporttype']	='Seleccione el tipo de reporte';
$string['selectduration']	='Seleccionar duración';
$string['selecteventtype']	='Seleccionar tipo de evento';
$string['updatefields']	='Actualizar campos';
$string['submit']	='Enviar';
$string['duration']	='Duración';
$string['weekstart']	='Weekstart';
$string['sunday_weekday']	='domingo';
$string['monday_weekday']	='lunes';
$string['tuesday_weekday']	='martes';
$string['wednesday_weekday']	='miércoles';
$string['thursday_weekday']	='jueves';
$string['friday_weekday']	='viernes';
$string['saturday_weekday']	='sábado';
$string['leaderboardsetup']	='Configuración de la tabla de clasificación';
$string['taskcollectioncustomtableentry']	='Entrada de datos de tablas personalizadas';
$string['leaderboard'] = 'Leaderboard';
$string['costcenter'] = 'Costcenter';
$string['leaderboard']	='Tabla de clasificación';
$string['costcenter']	='Centro de costos';
$string['costcenternamemissing']	='Costcenter no puede estar vacío';
$string['organization']	='Organización';
$string['organization']	='Cuentas';
$string['points_str']	='{$a->coinstr}';
$string['selectcostcenter']	='Seleccionar cuenta';
$string['account']	='Cuenta';
$string['coinsbadge']	='{$a->coinstr}';
$string['levelsbadge']	='Niveles';
$string['course_completion']	='Finalizaciones del curso';
$string['peer_recognizations']	='Reconocimiento de pares';
$string['coursevalue']	='Cursos';
$string['levelsvalue']	='Niveles';
$string['levelsvalue_label'] = 'Selecciona el nivel<abbr class="initialism text-danger" title="Necesaria"><img src='.$OUTPUT->image_url("new_req").'></abbr>';
$string['coursevalue_label'] = 'Seleccionar curso<abbr class="initialism text-danger" title="Necesaria"><img src='.$OUTPUT->image_url("new_req").'></abbr>';
$string['pointsvalue_label'] = '{$a->coinstr}<abbr class="initialism text-danger" title="Necesaria"><img src='.$OUTPUT->image_url("new_req").'></abbr>';
$string['pointsvalue']	='{$a->coinstr}';
$string['requiredcoursename']	='Los cursos no pueden estar vacíos';
$string['requiredbadgeimg']	='La imagen de la insignia no puede estar vacía';
$string['selectlevel']	='Selecciona el nivel';
$string['badgereasoncoins']	='{$a} Monedas';
$string['badgereasonlevels']	='{$a} nivel';
$string['badgereasoncourse_completions']	='por {$a} Curso';
$string['achievedfrompeer']	='Insignia de compañeros';
$string['badgereasoncoursecompletions']	='Finalización del curso';
$string['selectcourses']	='Seleccionar curso';
$string['shortnamemissing']	='El nombre corto no puede estar vacío';
$string['requiredcourses']	='El curso no puede estar vacío';
$string['createbadge']	='Crear insignia';
$string['existbadgeshortname']	='El nombre de la insignia ya existe';
$string['taskcustom_kpi_badges']	='Insignia Kpi personalizada';
$string['editbadge']	='Editar insignia';
$string['accountname']	='Nombre de la cuenta';
$string['enabled_gamification']	='Habilitar {$a->coinstr}';
$string['levels_gamification']	='Definir niveles';
$string['submitlevels_gamification']	='Enviar niveles';
$string['addnewlevels']	='Agregar niveles';
$string['editlevels']	='Niveles de actualización';
$string['submit_levels']	='Enviar niveles';
$string['display_levels']	='Niveles de visualización';
$string['displaylevels_gamification']	='Contenido de los niveles';
$string['levelvaluedisplay']	='Nivel {$a}';
$string['levelpointsdesc']	='Descripción de nivel';
$string['levelpoints']	='Nivel {$a->coinstr}';
$string['levelname']	='Información de nivel';
$string['usersinfodisplay']	='Usuarios de nivel';
$string['leveldconfigdata']	='Configuraciones de nivel';
$string['requiredlevels']	='Los niveles no pueden estar vacíos';
$string['employeefullname']	='Nombre de empleado';
$string['totalpoints']	='Total {$a->coinstr}';
$string['userpointsgraphprogress']	='Progreso del empleado';
$string['levelusersdata']	='Información de usuario de nivel {$a->level}';
$string['togostring_gamification']	='{$a}% Para subir de nivel';
$string['taskcollectionmonthlytableentry']	='Tarea de recopilación de datos mensual';
$string['taskcollectionweeklytableentry']	='Tarea de recopilación de datos semanal';
$string['selectaccount']	='Seleccionar cuenta';
$string['no_level_data_availiable']	='No hay niveles definidos';
$string['gamification:viewalluserlogs']	='Ver todos los registros de usuario';
$string['gamification:viewuserpointsinfo']	='Ver información de puntos de usuario';
$string['accountsunavailiable']	='Crear cuentas para agregar usuarios y ver paneles';
$string['requiredcostcenter']	='Se requiere cuenta';
$string['badgetyperequired']	='Se requiere el tipo de insignia';
$string['bl_gm_maximum_availiable_levels']	='Máximo disponible $a->levelsstr} son {$a->maxlevels}';
$string['level_string']	='{$a->level_str}';
$string['coin_string']	='{$a->coin_str}';
$string['coins_string']	='{$a->coinstr}';
$string['rank_string']	='{$a->rank_str}';
$string['noaccountsdefined']	='¡Aún no se han creado cuentas!';
$string['nobadgesdefined']	='¡Aún no se ha definido ninguna insignia!';
$string['create']	='Crear';
$string['update']	='Actualizar';
$string['badgeinfo']	='Información de la insignia';
$string['badgetouser']	='Premio a';
$string['badgemessage']	='Mensaje';
$string['badgemessagehelp_help']	='Breve descripción / motivo para otorgar la insignia';
$string['selectawardees']	='Seleccionar premiados';
$string['awardbadges']	='Insignias de premios';
$string['award']	='Premio';
$string['badge_label']	='Insignia';
$string['account_label']	='Cuenta';
$string['badgealreadyavailable']	='Esta insignia ya está disponible';
$string['myawarders']	='Mis premiados';
$string['myawardees']	='Mis premiados';
$string['deletedcourse']	='Curso eliminado';
$string['badgeimg']	='Imagen de la insignia';
$string['badge_costcenterid']	='Ayuda para la cuenta';
$string['badge_costcenterid_help']	='Cuenta para la insignia';
$string['badge_badgename']	='Ayuda para Badgename';
$string['badge_badgename_help']	='Nombre de la insignia';
$string['badge_type']	='Ayuda para el tipo de insignia';
$string['badge_type_help']	='Tipo de insignia. ej: monedas, niveles, etc.';
$string['badge_points']	='Ayuda para monedas insignia';
$string['badge_points_help']	='Monedas para lograr la insignia';
$string['badge_level']	='Ayuda para el nivel de insignia';
$string['badge_level_help']	='Nivel para lograr la insignia';
$string['badge_courses']	='Ayuda para el curso Badge';
$string['badge_courses_help']	='Curso para lograr la insignia';
$string['badge_badgeimg']	='Ayuda para la imagen de la insignia';
$string['badge_badgeimg_help']	='Imagen para insignia';
$string['existbadgebadgename']	='La insignia ya existe con el nombre de la insignia \'{$a}\'';
$string['entervalidpointsval']	='Ingrese un número entero válido para monedas';
$string['deleteconfirm']	='¿Estás seguro? Quieres eliminar la insignia \'{$a->badgename}\'.';
$string['nousers_allocated']	='¡Aún no hay usuarios premiados!';
$string['missingreciepientname']	='Seleccione uno o más destinatarios para otorgar la insignia';
$string['selectaccount_label']	='Seleccionar cuenta:';
$string['activitygrade']	='Calificación de actividad';
$string['gradepointsreason'] = '{$a} points ignored due to pre existing points';
$string['custom_course_rule'] ='<div class=\'custom_course_completion_rule\'><span class=\'mr-1\'>La finalización de este curso otorgará
</span>{$a} Puntos.</div>';
$string['detailed_leaderboard']	='{$a}Tabla de clasificación';
$string['viewmore']	='Ver más';
$string['nodata']	='No hay datos disponibles';
$string['name']	='Nombre';
$string['weekly']	='Semanal';
$string['monthly']	='Mensual';
$string['overall']	='En general';
$string['select_courses']	='Seleccionar cursos';
$string['select_classrooms']	='Seleccionar aulas';
$string['select_competencies']	='Seleccionar competencias';
$string['select_learningplan']	='Seleccionar plan de aprendizaje';
$string['select_certification']	='Seleccionar certificación';
$string['select_program']	='Seleccionar programa';
$string['select_badge_group']	='Seleccionar grupo de insignias';
$string['select_costcenter']	='Seleccione centro de costos';
$string['certification']	='Certificación';
$string['program']	='Programa';
$string['maximum_number_of_levels']	='El número máximo de niveles es';
$string['minimum_number_of_levels']	='El número mínimo de niveles es 2';
$string['levelsmandatory']	='Los niveles son obligatorios';
$string['employee']	='Empleado';















