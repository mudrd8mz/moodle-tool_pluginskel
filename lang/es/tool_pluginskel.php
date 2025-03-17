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
 * Provides the plugin strings.
 *
 * @package     tool_pluginskel
 * @category    string
 * @copyright   2016 Alexandru Elisei <alexandru.elisei@gmail.com>, David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['observers_internal'] = 'Interno';
$string['observers_internal_help'] = 'Los observadores no internos no se llaman durante las transacciones de la base de datos, sino después de un commit exitoso de la transacción.';
$string['observers_internal_link'] = 'https://docs.moodle.org/dev/Event_2';

$string['message_providers_title'] = 'Título';
$string['message_providers_title_help'] = 'Una breve descripción en una sola línea del proveedor de mensajes. Representa el valor de texto para la cadena de idioma "messageprovider:name".';
$string['message_providers_title_link'] = 'https://docs.moodle.org/dev/Messaging_2.0';

$string['upgradelib'] = 'Upgradelib';
$string['upgradelib_help'] = 'Cree el archivo db/upgradelib.php donde el código de actualización puede agruparse bajo algunas funciones auxiliares para ser utilizadas en el archivo upgrade.php.';
$string['upgradelib_link'] = 'https://docs.moodle.org/dev/Upgrade_API';

$string['addmore_applicable_formats'] = 'Agregar más formatos aplicables';
$string['addmore_archetypes'] = 'Agregar más arquetipos';
$string['addmore_backup_elements'] = 'Agregar más elementos de copia de seguridad';
$string['addmore_capabilities'] = 'Agregar más capacidades';
$string['addmore_cli_scripts'] = 'Agregar más nombres de archivo';
$string['addmore_custom_layouts'] = 'Agregar más diseños personalizados';
$string['addmore_dependencies'] = 'Agregar más dependencias';
$string['addmore_events'] = 'Agregar más eventos';
$string['addmore_lang_strings'] = 'Agregar más cadenas de idioma';
$string['addmore_message_providers'] = 'Agregar más proveedores de mensajes';
$string['addmore_mobile_addons'] = 'Agregar más complementos móviles';
$string['addmore_observers'] = 'Agregar más observadores';
$string['addmore_params_for_js'] = 'Agregar más parámetros JS';
$string['addmore_parents'] = 'Agregar más elementos principales';
$string['addmore_phpunit_tests'] = 'Agregar más clases de prueba';
$string['addmore_restore_elements'] = 'Agregar más elementos de restauración';
$string['addmore_strings_for_js'] = 'Agregar más cadenas JS';
$string['addmore_stylesheets'] = 'Agregar más hojas de estilo';

$string['atto_features_params_for_js'] = 'Parámetros JS';
$string['atto_features_params_for_js_default'] = 'Por defecto';
$string['atto_features_params_for_js_default_help'] = 'El valor predeterminado del parámetro, definido en el archivo fuente de JavaScript.';
$string['atto_features_params_for_js_default_link'] = 'https://docs.moodle.org/dev/Atto#Atto_subplugin_Php_API';
$string['atto_features_params_for_js_name'] = 'Nombre';
$string['atto_features_params_for_js_name_help'] = 'El nombre del parámetro.';
$string['atto_features_params_for_js_name_link'] = 'https://docs.moodle.org/dev/Atto#Atto_subplugin_Php_API';
$string['atto_features_params_for_js_value'] = 'Valor';
$string['atto_features_params_for_js_value_help'] = 'El valor del parámetro de JavaScript.';
$string['atto_features_params_for_js_value_link'] = 'https://docs.moodle.org/dev/Atto#Atto_subplugin_Php_API';
$string['atto_features_strings_for_js'] = 'Cadenas JS';
$string['atto_features_strings_for_js_id'] = 'ID';
$string['atto_features_strings_for_js_id_help'] = 'El identificador de la cadena.';
$string['atto_features_strings_for_js_id_link'] = 'https://docs.moodle.org/dev/Atto#Atto_subplugin_Php_API';
$string['atto_features_strings_for_js_text'] = 'Texto';
$string['atto_features_strings_for_js_text_help'] = 'El valor del identificador de la cadena.';
$string['atto_features_strings_for_js_text_link'] = 'https://docs.moodle.org/dev/Atto#Atto_subplugin_Php_API';

$string['tiny_features_buttons'] = 'Tiene botones';
$string['tiny_features_buttons_help'] = 'Este complemento tendrá botones en la barra de herramientas.';
$string['tiny_features_menuitems'] = 'Tiene elementos de menú';
$string['tiny_features_menuitems_help'] = 'Este complemento tendrá elementos en el menú.';
$string['tiny_features_options'] = 'Opciones para pasar desde PHP al complemento TinyMCE';
$string['tiny_features_options_name'] = 'Nombre de la opción';
$string['tiny_features_options_name_help'] = 'Una cadena que representa el nombre de la opción.';
$string['tiny_features_options_type'] = 'Tipo de opción';
$string['tiny_features_options_type_help'] = 'El tipo de opción. Consulte https://www.tiny.cloud/docs/tinymce/6/apis/tinymce.editoroptions/ para obtener más información sobre las opciones válidas.';
$string['addmore_options'] = 'Agregar otra opción';
$string['delete_options'] = 'Eliminar opción';

$string['tool_options'] = 'Opciones que la herramienta JS usará desde PHP';

$string['auth_features_can_be_manually_set'] = 'Puede establecerse manualmente';
$string['auth_features_can_be_manually_set_help'] = 'Verdadero si el complemento de autenticación puede establecerse manualmente para los usuarios.';
$string['auth_features_can_be_manually_set_link'] = 'https://docs.moodle.org/dev/Authentication_plugins';
$string['auth_features_can_change_password'] = 'Puede cambiar la contraseña';
$string['auth_features_can_change_password_help'] = 'Verdadero si el complemento de autenticación puede cambiar la contraseña del usuario.';
$string['auth_features_can_change_password_link'] = 'https://docs.moodle.org/dev/Authentication_plugins';
$string['auth_features_can_confirm'] = 'Puede confirmar';
$string['auth_features_can_confirm_help'] = 'Verdadero si el complemento de autenticación permite la confirmación de nuevos usuarios.';
$string['auth_features_can_confirm_link'] = 'https://docs.moodle.org/dev/Authentication_plugins';
$string['auth_features_can_edit_profile'] = 'Puede editar el perfil';
$string['auth_features_can_edit_profile_help'] = 'Verdadero si el complemento de autenticación puede editar el perfil del usuario.';
$string['auth_features_can_edit_profile_link'] = 'https://docs.moodle.org/dev/Authentication_plugins';
$string['auth_features_can_reset_password'] = 'Puede restablecer la contraseña';
$string['auth_features_can_reset_password_help'] = 'Verdadero si el complemento permite restablecer la contraseña interna.';
$string['auth_features_can_reset_password_link'] = 'https://docs.moodle.org/dev/Authentication_plugins';
$string['auth_features_can_signup'] = 'Permite el registro';
$string['auth_features_can_signup_help'] = 'Verdadero si el complemento de autenticación permite el registro y la creación de usuarios.';
$string['auth_features_can_signup_link'] = 'https://docs.moodle.org/dev/Authentication_plugins';
$string['auth_features_config_ui'] = 'Interfaz de configuración';
$string['auth_features_config_ui_help'] = 'Habilita la generación de una interfaz de configuración. Un formulario web debe definirse en la función config_form() en auth.php.';
$string['auth_features_config_ui_link'] = 'https://docs.moodle.org/dev/Authentication_plugins';
$string['auth_features_description'] = 'Descripción';
$string['auth_features_description_help'] = 'Una breve descripción en una sola línea del complemento de autenticación. Representa el valor de texto de la cadena de idioma "auth_description".';
$string['auth_features_description_link'] = 'https://docs.moodle.org/dev/Authentication_plugins';
$string['auth_features_is_internal'] = 'Es interno';
$string['auth_features_is_internal_help'] = 'Verdadero si el complemento de autenticación es "interno". Los complementos internos utilizan hashes de contraseñas de la tabla de usuarios de Moodle para la autenticación.';
$string['auth_features_is_internal_link'] = 'https://docs.moodle.org/dev/Authentication_plugins';
$string['auth_features_is_synchronised_with_external'] = 'Se sincroniza con fuentes externas';
$string['auth_features_is_synchronised_with_external_help'] = 'Verdadero si Moodle debe actualizar automáticamente los registros de usuarios internos con datos de fuentes externas utilizando la información del método get_userinfo().';
$string['auth_features_is_synchronised_with_external_link'] = 'https://docs.moodle.org/dev/Authentication_plugins';
$string['auth_features_prevent_local_passwords'] = 'Evita contraseñas locales';
$string['auth_features_prevent_local_passwords_help'] = 'Verdadero si los hashes de contraseñas deben almacenarse en la base de datos local de Moodle.';
$string['auth_features_prevent_local_passwords_link'] = 'https://docs.moodle.org/dev/Authentication_plugins';

$string['back'] = 'Volver';

$string['block_features_edit_form'] = 'Editar formulario';
$string['block_features_edit_form_help'] = 'Cree el archivo edit_form.php, que se utilizará para la configuración de la instancia.';
$string['block_features_edit_form_link'] = 'https://docs.moodle.org/dev/Blocks';
$string['block_features_instance_allow_multiple'] = 'Permitir múltiples instancias';
$string['block_features_instance_allow_multiple_help'] = 'Permitir múltiples instancias del bloque en el mismo curso.';
$string['block_features_instance_allow_multiple_link'] = 'https://docs.moodle.org/dev/Blocks';
$string['block_features_applicable_formats'] = 'Formatos aplicables';
$string['block_features_applicable_formats_page'] = 'Página';
$string['block_features_applicable_formats_page_help'] = 'Declara en qué página está disponible el complemento.';
$string['block_features_applicable_formats_page_link'] = 'https://docs.moodle.org/dev/Blocks';
$string['block_features_applicable_formats_allowed'] = 'Permitido';
$string['block_features_applicable_formats_allowed_help'] = 'Verdadero si el bloque está permitido en la página.';
$string['block_features_applicable_formats_allowed_link'] = 'https://docs.moodle.org/dev/Blocks';
$string['block_features_backup_moodle2'] = 'Respaldo Moodle2';
$string['block_features_backup_moodle2_backup_elements'] = 'Elementos de respaldo';
$string['block_features_backup_moodle2_backup_elements_name'] = 'Nombre del elemento de respaldo';
$string['block_features_backup_moodle2_backup_elements_name_help'] = 'Nombre del elemento de respaldo.';
$string['block_features_backup_moodle2_backup_elements_name_link'] = 'https://docs.moodle.org/dev/Backup_API#API_for_blocks';
$string['block_features_backup_moodle2_backup_stepslib'] = 'Librería de pasos de respaldo';
$string['block_features_backup_moodle2_backup_stepslib_help'] = 'Cree un archivo stepslib para el respaldo.';
$string['block_features_backup_moodle2_backup_stepslib_link'] = 'https://docs.moodle.org/dev/Backup_API#API_for_blocks';
$string['block_features_backup_moodle2_restore_elements'] = 'Elementos de restauración';
$string['block_features_backup_moodle2_restore_elements_name'] = 'Nombre del elemento de restauración';
$string['block_features_backup_moodle2_restore_elements_name_help'] = 'Nombre del elemento de restauración.';
$string['block_features_backup_moodle2_restore_elements_name_link'] = 'https://docs.moodle.org/dev/Restore_2.0_for_developers';
$string['block_features_backup_moodle2_restore_elements_path'] = 'Ruta del elemento de restauración';
$string['block_features_backup_moodle2_restore_elements_path_help'] = 'La ruta del elemento de restauración.';
$string['block_features_backup_moodle2_restore_elements_path_link'] = 'https://docs.moodle.org/dev/Restore_2.0_for_developers';
$string['block_features_backup_moodle2_restore_stepslib'] = 'Librería de pasos de restauración';
$string['block_features_backup_moodle2_restore_stepslib_help'] = 'Cree un archivo stepslib para la restauración.';
$string['block_features_backup_moodle2_restore_stepslib_link'] = 'https://docs.moodle.org/dev/Restore_2.0_for_developers';
$string['block_features_backup_moodle2_restore_task'] = 'Tarea de restauración';
$string['block_features_backup_moodle2_restore_task_help'] = 'Cree un archivo de tarea de restauración.';
$string['block_features_backup_moodle2_restore_task_link'] = 'https://docs.moodle.org/dev/Restore_2.0_for_developers';
$string['block_features_backup_moodle2_settingslib'] = 'Librería de configuración de respaldo';
$string['block_features_backup_moodle2_settingslib_help'] = 'Cree un archivo settingslib para el respaldo.';
$string['block_features_backup_moodle2_settingslib_link'] = 'https://docs.moodle.org/dev/Backup_API#API_for_blocks';

$string['capabilities'] = 'Capacidades';
$string['capabilities_archetypes'] = 'Arquetipos';
$string['capabilities_archetypes_role'] = 'Rol';
$string['capabilities_archetypes_role_help'] = 'Arquetipo estándar.';
$string['capabilities_archetypes_role_link'] = 'https://docs.moodle.org/dev/Access_API';
$string['capabilities_archetypes_permission'] = 'Permiso';
$string['capabilities_archetypes_permission_help'] = 'El permiso asociado con el rol.';
$string['capabilities_archetypes_permission_link'] = 'https://docs.moodle.org/dev/Access_API';
$string['capabilities_captype'] = 'Tipo de capacidad';
$string['capabilities_captype_help'] = 'Todas las capacidades son "read" o "write". Por razones de seguridad, todas las capacidades de escritura están bloqueadas para invitados y usuarios no autenticados.';
$string['capabilities_captype_link'] = 'https://docs.moodle.org/dev/Access_API';
$string['capabilities_clonepermissionsfrom'] = 'Clonar permisos desde';
$string['capabilities_clonepermissionsfrom_help'] = 'Copie los permisos de cada rol desde la configuración actual de otra capacidad.';
$string['capabilities_clonepermissionsfrom_link'] = 'https://docs.moodle.org/dev/Access_API';
$string['capabilities_contextlevel'] = 'Nivel de contexto';
$string['capabilities_contextlevel_help'] = 'El nivel de contexto típico donde se verifica la capacidad. Es el nivel más bajo donde esta capacidad puede ser sobrescrita por la interfaz de permisos.';
$string['capabilities_contextlevel_link'] = 'https://docs.moodle.org/dev/Access_API';
$string['capabilities_name'] = 'Nombre';
$string['capabilities_name_help'] = 'El nombre de la capacidad. Se generará la capacidad "componenttype/componentname:name".';
$string['capabilities_name_link'] = 'https://docs.moodle.org/dev/Access_API';
$string['capabilities_riskbitmask'] = 'Máscara de riesgos';
$string['capabilities_riskbitmask_help'] = 'El riesgo asociado con la capacidad. Se pueden especificar múltiples tipos de riesgo usando el separador "|".';
$string['capabilities_riskbitmask_link'] = 'https://docs.moodle.org/dev/Hardening_new_Roles_system';
$string['capabilities_title'] = 'Título';
$string['capabilities_title_help'] = 'Una breve descripción en una sola línea de la capacidad. Representa el valor de texto de la cadena de idioma "componentname:name".';
$string['capabilities_title_link'] = 'https://docs.moodle.org/dev/Access_API';

$string['cli_scripts'] = 'Scripts CLI';
$string['cli_scripts_filename'] = 'Nombre del archivo';
$string['cli_scripts_filename_help'] = 'El nombre del archivo del script CLI. Todos los archivos se generarán en el directorio "cli" del plugin.';

$string['component'] = 'Componente';
$string['component_help'] = 'El nombre completo del componente en formato frankenstyle, en la forma de componenttype_componentname.

Si deseas cambiar el tipo de componente, reinicia el proceso de generación del plugin desde el principio.';
$string['component_link'] = 'https://docs.moodle.org/dev/version.php';
$string['componenthdr'] = 'Componente';
$string['componenttype'] = 'Tipo de componente';
$string['componenttype_help'] = 'El tipo de plugin. Puedes encontrar más información sobre los diferentes tipos de plugins en la documentación oficial de Moodle haciendo clic en el botón "Más ayuda".';
$string['componenttype_link'] = 'https://docs.moodle.org/dev/Plugin_types';
$string['componentnameinvalid'] = 'Nombre de componente no válido';
$string['componentname'] = 'Nombre del componente';
$string['componentname_help'] = 'El nombre del plugin. Este se convertirá en el nombre de la carpeta raíz del plugin.

El nombre debe comenzar con una letra y, idealmente, solo debe contener letras. Se permiten números y guiones bajos, pero no se recomienda su uso. Los módulos de actividad no deben contener guiones bajos en su nombre.';
$string['copyright'] = 'Derechos de autor';
$string['copyright_desc'] = 'Valor predeterminado del campo de derechos de autor al generar manualmente la estructura del plugin.';
$string['copyright_help'] = 'Este campo debe contener el año de creación del plugin, el nombre o nombres de los titulares de los derechos de autor, así como las direcciones de correo electrónico.

La etiqueta de derechos de autor estará presente en cada archivo del plugin como parte del estándar de Moodle.

Para más información sobre el estándar de Moodle, haz clic en el botón "Más ayuda".';
$string['copyright_link'] = 'https://docs.moodle.org/dev/Coding_style#Files';

$string['delete_applicable_formats'] = 'Eliminar formato aplicable';
$string['delete_capabilities'] = 'Eliminar capacidad';
$string['delete_cli_scripts'] = 'Eliminar nombre de archivo';
$string['delete_custom_layouts'] = 'Eliminar diseños personalizados';
$string['delete_dependencies'] = 'Eliminar dependencia';
$string['delete_events'] = 'Eliminar evento';
$string['delete_lang_strings'] = 'Eliminar cadena de idioma';
$string['delete_message_providers'] = 'Eliminar proveedor de mensajes';
$string['delete_mobile_addons'] = 'Eliminar complemento móvil';
$string['delete_observers'] = 'Eliminar observador';
$string['delete_params_for_js'] = 'Eliminar parámetro JS';
$string['delete_parents'] = 'Eliminar padre';
$string['delete_phpunit_tests'] = 'Eliminar clase de prueba';
$string['delete_restore_elements'] = 'Eliminar elemento de restauración';
$string['delete_strings_for_js'] = 'Eliminar cadena JS';
$string['delete_stylesheets'] = 'Eliminar hoja de estilos';

$string['dependencies'] = 'Dependencias';
$string['dependencies_plugin'] = 'Plugin';
$string['dependencies_plugin_help'] = 'El nombre completo del componente en formato frankenstyle para la dependencia del plugin.';
$string['dependencies_version'] = 'Versión';
$string['dependencies_version_help'] = 'El número de versión de la dependencia del plugin.

Un valor ANY_VERSION significa que cualquier versión del plugin cumplirá con la dependencia.';

$string['downloadskel'] = 'Descargar estructura del plugin';
$string['downloadrecipe'] = 'Descargar receta';
$string['emptypluginname'] = 'Nombre del plugin no especificado';
$string['emptyrecipecontent'] = 'Receta vacía';

$string['enrol_features_allow_enrol'] = 'Permitir inscripción';
$string['enrol_features_allow_enrol_help'] = 'Permitir la inscripción de usuarios desde otros plugins llamando a la función enrol_user(). También se debe definir una capacidad de "inscripción".';
$string['enrol_features_allow_enrol_link'] = 'https://docs.moodle.org/dev/Enrolment_plugins';
$string['enrol_features_allow_unenrol'] = 'Permitir desinscripción';
$string['enrol_features_allow_unenrol_help'] = 'Permitir que otros plugins desinscriban a todos los usuarios. También se debe definir una capacidad de "desinscripción".';
$string['enrol_features_allow_unenrol_link'] = 'https://docs.moodle.org/dev/Enrolment_plugins';
$string['enrol_features_allow_unenrol_user'] = 'Permitir desinscripción de usuario';
$string['enrol_features_allow_unenrol_user_help'] = 'Permitir que otros plugins desinscriban a un usuario específico. También se debe definir una capacidad de "desinscripción".';
$string['enrol_features_allow_unenrol_user_link'] = 'https://docs.moodle.org/dev/Enrolment_plugins';
$string['enrol_features_allow_manage'] = 'Permitir gestión';
$string['enrol_features_allow_manage_help'] = 'Permitir que otros plugins modifiquen manualmente las inscripciones de los usuarios.';
$string['enrol_features_allow_manage_link'] = 'https://docs.moodle.org/dev/Enrolment_plugins';

$string['events'] = 'Eventos';
$string['events_eventname'] = 'Nombre del evento';
$string['events_eventname_help'] = 'El nombre del evento creado.';
$string['events_eventname_link'] = 'https://docs.moodle.org/dev/Event_2';
$string['events_extends'] = 'Extiende';
$string['events_extends_help'] = 'El nombre del evento base del cual se extiende el evento.';
$string['events_extends_link'] = 'https://docs.moodle.org/dev/Event_2';

$string['features_install'] = 'Instalar';
$string['features_install_help'] = 'Generar el archivo db/install.php.';
$string['features_license'] = 'Licencia';
$string['features_license_help'] = 'Generar el archivo LICENSE.md con el texto de la licencia GPL3.';
$string['features_readme'] = 'Readme';
$string['features_readme_help'] = 'Generar el archivo README.md.';
$string['features_settings'] = 'Configuraciones';
$string['features_settings_help'] = 'Generar el archivo settings.php.';
$string['features_uninstall'] = 'Desinstalar';
$string['features_uninstall_help'] = 'Generar el archivo db/uninstall.php.';
$string['features_upgrade'] = 'Actualizar';
$string['features_upgrade_help'] = 'Generar el archivo db/upgrade.php.';
$string['features_upgrade_link'] = 'https://docs.moodle.org/dev/Upgrade_API';
$string['features_upgradelib'] = 'Biblioteca de actualización';
$string['features_upgradelib_help'] = 'Generar el archivo db/upgradelib.php.';
$string['features_upgradelib_link'] = 'https://docs.moodle.org/dev/Upgrade_API';

$string['generateskel'] = 'Generar estructura del plugin';
$string['generalhdr'] = 'General';

$string['lang_strings'] = 'Cadenas de idioma';
$string['lang_strings_id'] = 'ID';
$string['lang_strings_id_help'] = 'El ID de la cadena de idioma.';
$string['lang_strings_id_link'] = 'https://docs.moodle.org/dev/String_API';
$string['lang_strings_text'] = 'Texto';
$string['lang_strings_text_help'] = 'El valor de la cadena de idioma.';
$string['lang_strings_text_link'] = 'https://docs.moodle.org/dev/String_API';

$string['manualhdr'] = 'Generar el plugin manualmente';
$string['maturity'] = 'Madurez';
$string['maturity_help'] = 'Nivel de madurez del plugin.';
$string['maturity_link'] = 'https://docs.moodle.org/dev/version.php';

$string['message_providers'] = 'Proveedores de mensajes';
$string['message_providers_capability'] = 'Capacidad requerida';
$string['message_providers_capability_help'] = 'La capacidad que el usuario necesita para recibir el mensaje generado por el proveedor.';
$string['message_providers_capability_link'] = 'https://docs.moodle.org/dev/Messaging_2.0';
$string['message_providers_name'] = 'Nombre';
$string['message_providers_name_help'] = 'El nombre del proveedor de mensajes. El proveedor de mensajes se define en el archivo db/messages.php.';
$string['message_providers_name_link'] = 'https://docs.moodle.org/dev/Messaging_2.0';

$string['mobile_addons'] = 'Complementos móviles';
$string['mobile_addons_dependencies'] = 'Dependencias';
$string['mobile_addons_dependencies_name'] = 'Nombre';
$string['mobile_addons_dependencies_name_help'] = 'El nombre de la dependencia.';
$string['mobile_addons_dependencies_name_link'] = 'https://docs.moodle.org/dev/Moodle_Mobile_Remote_add-ons';
$string['mobile_addons_name'] = 'Nombre';
$string['mobile_addons_name_help'] = 'El nombre del complemento remoto móvil que se cargará cuando el usuario acceda al plugin en la aplicación móvil.';
$string['mobile_addons_name_link'] = 'https://docs.moodle.org/dev/Moodle_Mobile_Remote_add-ons';

$string['mod_features_backup_moodle2'] = 'Copia de seguridad Moodle2';
$string['mod_features_backup_moodle2_settingslib'] = 'Settingslib';
$string['mod_features_backup_moodle2_settingslib_help'] = 'Crear un archivo settingslib de copia de seguridad.';
$string['mod_features_backup_moodle2_settingslib_link'] = 'https://docs.moodle.org/dev/Backup_2.0_for_developers';
$string['mod_features_backup_moodle2_backup_elements'] = 'Elementos de copia de seguridad';
$string['mod_features_backup_moodle2_backup_elements_name'] = 'Nombre del elemento de copia de seguridad';
$string['mod_features_backup_moodle2_backup_elements_name_help'] = 'Nombre del elemento de copia de seguridad.';
$string['mod_features_backup_moodle2_backup_elements_name_link'] = 'https://docs.moodle.org/dev/Backup_2.0_for_developers';
$string['mod_features_backup_moodle2_restore_elements'] = 'Elementos de restauración';
$string['mod_features_backup_moodle2_restore_elements_name'] = 'Nombre del elemento de restauración';
$string['mod_features_backup_moodle2_restore_elements_name_help'] = 'Nombre del elemento de restauración.';
$string['mod_features_backup_moodle2_restore_elements_name_link'] = 'https://docs.moodle.org/dev/Restore_2.0_for_developers';
$string['mod_features_backup_moodle2_restore_elements_path'] = 'Ruta del elemento de restauración';
$string['mod_features_backup_moodle2_restore_elements_path_help'] = 'La ruta del elemento de restauración.';
$string['mod_features_backup_moodle2_restore_elements_path_link'] = 'https://docs.moodle.org/dev/Restore_2.0_for_developers';
$string['mod_features_file_area'] = 'Área de archivos';
$string['mod_features_file_area_help'] = 'Genera las funciones relacionadas con la API de archivos.';
$string['mod_features_file_area_link'] = 'https://docs.moodle.org/dev/Activity_modules';
$string['mod_features_gradebook'] = 'Libro de calificaciones';
$string['mod_features_gradebook_help'] = 'Verdadero si el plugin implementa un libro de calificaciones.';
$string['mod_features_gradebook_link'] = 'https://docs.moodle.org/dev/Activity_modules';
$string['mod_features_navigation'] = 'Navegación';
$string['mod_features_navigation_help'] = 'Crea las funciones extend_navigation() y extend_settings_navigation() en lib.php.';
$string['mod_features_navigation_link'] = 'https://docs.moodle.org/dev/Activity_modules';

$string['name'] = 'Nombre';
$string['name_help'] = 'Nombre legible para el plugin. Este representa el valor de texto para la cadena de idioma "pluginname".';

$string['observers'] = 'Observadores';
$string['observers_callback'] = 'Callback';
$string['observers_callback_help'] = 'Nombre de la función de callback.';
$string['observers_callback_link'] = 'https://docs.moodle.org/dev/Event_2';
$string['observers_eventname'] = 'Nombre del evento';
$string['observers_eventname_help'] = 'Nombre de la clase del evento completamente calificado.';
$string['observers_eventname_link'] = 'https://docs.moodle.org/dev/Event_2';
$string['observers_includefile'] = 'Archivo incluido';
$string['observers_includefile_help'] = 'Archivo que se incluirá antes de llamar al observador. La ruta del archivo debe ser relativa al directorio raíz de Moodle.';
$string['observers_includefile_link'] = 'https://docs.moodle.org/dev/Event_2';
$string['observers_priority'] = 'Prioridad';
$string['observers_priority_help'] = 'La prioridad del observador. Los observadores con mayor prioridad se llaman primero. Si no se especifica, el valor predeterminado será 0.';
$string['observers_priority_link'] = 'https://docs.moodle.org/dev/Event_2';

$string['phpunit_tests'] = 'Pruebas PHPUnit';
$string['phpunit_tests_classname'] = 'Nombre de la clase';
$string['phpunit_tests_classname_help'] = 'El nombre de la clase de prueba. Puede ser el nombre completo en frankenstyle o simplemente el nombre de la clase a probar. Todos los archivos de prueba de PHPUnit se generarán en el directorio "tests".';
$string['phpunit_tests_classname_link'] = 'https://docs.moodle.org/dev/PHPUnit';

$string['privacy:metadata'] = 'El generador de esqueletos de plugins no almacena ningún dato personal';

$string['proceedmanually'] = 'Continuar con la generación manual';
$string['proceedrecipefile'] = 'Continuar con el archivo de receta';
$string['proceedrecipe'] = 'Continuar con la receta';

$string['qtype_features_base_class'] = 'Clase base';
$string['qtype_features_base_class_help'] = 'Clase base para la clase de pregunta ubicada en question.php.';
$string['qtype_features_base_class_link'] = 'https://docs.moodle.org/dev/Question_types';

$string['pluginname'] = 'Generador de esqueleto de plugins de Moodle';
$string['recipe'] = 'Receta';
$string['recipe_help'] = 'La receta debe escribirse utilizando el formato de serialización YAML. Una plantilla de receta se encuentra en el directorio de instalación del plugin en cli/example.yaml.

Más información sobre la sintaxis YAML se puede encontrar haciendo clic en el botón "Más ayuda", que te llevará a la página web oficial de YAML.';
$string['recipe_link'] = 'http://yaml.org/';
$string['recipefile'] = 'Archivo de receta';

$string['recipefile_help'] = 'La receta debe escribirse utilizando el formato de serialización YAML. Una plantilla de receta se encuentra en el directorio de instalación del plugin en cli/example.yaml.

Más información sobre la sintaxis YAML se puede encontrar haciendo clic en el botón "Más ayuda", que te llevará a la página web oficial de YAML.';
$string['recipefile_link'] = 'http://yaml.org/';
$string['recipefilehdr'] = 'Generar el plugin desde un archivo de receta';
$string['recipehdr'] = 'Generar el plugin desde una receta';
$string['release'] = 'Versión';
$string['release_help'] = 'Nombre de versión legible para identificar cada lanzamiento del plugin.';
$string['release_link'] = 'https://docs.moodle.org/dev/version.php';
$string['requires'] = 'Versión mínima de Moodle requerida';
$string['requires_help'] = 'La versión mínima de Moodle requerida para que el plugin se instale y funcione correctamente.';
$string['showrecipe'] = 'Mostrar receta';
$string['showrecipehdr'] = 'Receta';

$string['theme_features_all_layouts'] = 'Todos los diseños';
$string['theme_features_all_layouts_help'] = 'Aplicar el tema a todos los diseños.';
$string['theme_features_all_layouts_link'] = 'https://docs.moodle.org/dev/Themes';
$string['theme_features_custom_layouts'] = 'Diseños personalizados';
$string['theme_features_custom_layouts_name'] = 'Nombre del diseño personalizado';
$string['theme_features_custom_layouts_name_help'] = 'El nombre del diseño personalizado que creará el plugin. El diseño estará ubicado en el directorio "layouts".';
$string['theme_features_custom_layouts_name_link'] = 'https://docs.moodle.org/dev/Themes';
$string['theme_features_doctype'] = 'Doctype';
$string['theme_features_doctype_help'] = 'El doctype para la página web. Normalmente es "html5".';
$string['theme_features_doctype_link'] = 'https://docs.moodle.org/dev/Themes';
$string['theme_features_parents'] = 'Temas principales';
$string['theme_features_parents_base_theme'] = 'Tema base';
$string['theme_features_parents_base_theme_help'] = 'El tema base que este tema extenderá.';
$string['theme_features_parents_base_theme_link'] = 'https://docs.moodle.org/dev/Themes';
$string['theme_features_stylesheets'] = 'Hojas de estilo';
$string['theme_features_stylesheets_name'] = 'Nombre de la hoja de estilo';
$string['theme_features_stylesheets_name_help'] = 'El nombre de la hoja de estilo definida por el plugin. La hoja de estilo se creará en el directorio "styles".';
$string['theme_features_stylesheets_name_link'] = 'https://docs.moodle.org/dev/Themes';

$string['undefined'] = 'Indefinido';
$string['version'] = 'Versión';
$string['version_help'] = 'El número de versión del plugin. El formato es parcialmente basado en la fecha con la forma YYYYMMDDXX, donde XX es un contador incremental para el año (YYYY), mes (MM) y día (DD) del lanzamiento de la versión del plugin.';
$string['version_link'] = 'https://docs.moodle.org/dev/version.php';

$string['examples_recipe'] = 'Ejemplos de la receta';
$string['none'] = "Ninguno";
