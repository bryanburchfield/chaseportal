/*!
 *  Lang.js for Laravel localization in JavaScript.
 *
 *  @version 1.1.10
 *  @license MIT https://github.com/rmariuzzo/Lang.js/blob/master/LICENSE
 *  @site    https://github.com/rmariuzzo/Lang.js
 *  @author  Rubens Mariuzzo <rubens@mariuzzo.com>
 */
(function(root,factory){"use strict";if(typeof define==="function"&&define.amd){define([],factory)}else if(typeof exports==="object"){module.exports=factory()}else{root.Lang=factory()}})(this,function(){"use strict";function inferLocale(){if(typeof document!=="undefined"&&document.documentElement){return document.documentElement.lang}}function convertNumber(str){if(str==="-Inf"){return-Infinity}else if(str==="+Inf"||str==="Inf"||str==="*"){return Infinity}return parseInt(str,10)}var intervalRegexp=/^({\s*(\-?\d+(\.\d+)?[\s*,\s*\-?\d+(\.\d+)?]*)\s*})|([\[\]])\s*(-Inf|\*|\-?\d+(\.\d+)?)\s*,\s*(\+?Inf|\*|\-?\d+(\.\d+)?)\s*([\[\]])$/;var anyIntervalRegexp=/({\s*(\-?\d+(\.\d+)?[\s*,\s*\-?\d+(\.\d+)?]*)\s*})|([\[\]])\s*(-Inf|\*|\-?\d+(\.\d+)?)\s*,\s*(\+?Inf|\*|\-?\d+(\.\d+)?)\s*([\[\]])/;var defaults={locale:"en"};var Lang=function(options){options=options||{};this.locale=options.locale||inferLocale()||defaults.locale;this.fallback=options.fallback;this.messages=options.messages};Lang.prototype.setMessages=function(messages){this.messages=messages};Lang.prototype.getLocale=function(){return this.locale||this.fallback};Lang.prototype.setLocale=function(locale){this.locale=locale};Lang.prototype.getFallback=function(){return this.fallback};Lang.prototype.setFallback=function(fallback){this.fallback=fallback};Lang.prototype.has=function(key,locale){if(typeof key!=="string"||!this.messages){return false}return this._getMessage(key,locale)!==null};Lang.prototype.get=function(key,replacements,locale){if(!this.has(key,locale)){return key}var message=this._getMessage(key,locale);if(message===null){return key}if(replacements){message=this._applyReplacements(message,replacements)}return message};Lang.prototype.trans=function(key,replacements){return this.get(key,replacements)};Lang.prototype.choice=function(key,number,replacements,locale){replacements=typeof replacements!=="undefined"?replacements:{};replacements.count=number;var message=this.get(key,replacements,locale);if(message===null||message===undefined){return message}var messageParts=message.split("|");var explicitRules=[];for(var i=0;i<messageParts.length;i++){messageParts[i]=messageParts[i].trim();if(anyIntervalRegexp.test(messageParts[i])){var messageSpaceSplit=messageParts[i].split(/\s/);explicitRules.push(messageSpaceSplit.shift());messageParts[i]=messageSpaceSplit.join(" ")}}if(messageParts.length===1){return message}for(var j=0;j<explicitRules.length;j++){if(this._testInterval(number,explicitRules[j])){return messageParts[j]}}var pluralForm=this._getPluralForm(number);return messageParts[pluralForm]};Lang.prototype.transChoice=function(key,count,replacements){return this.choice(key,count,replacements)};Lang.prototype._parseKey=function(key,locale){if(typeof key!=="string"||typeof locale!=="string"){return null}var segments=key.split(".");var source=segments[0].replace(/\//g,".");return{source:locale+"."+source,sourceFallback:this.getFallback()+"."+source,entries:segments.slice(1)}};Lang.prototype._getMessage=function(key,locale){locale=locale||this.getLocale();key=this._parseKey(key,locale);if(this.messages[key.source]===undefined&&this.messages[key.sourceFallback]===undefined){return null}var message=this.messages[key.source];var entries=key.entries.slice();var subKey="";while(entries.length&&message!==undefined){var subKey=!subKey?entries.shift():subKey.concat(".",entries.shift());if(message[subKey]!==undefined){message=message[subKey];subKey=""}}if(typeof message!=="string"&&this.messages[key.sourceFallback]){message=this.messages[key.sourceFallback];entries=key.entries.slice();subKey="";while(entries.length&&message!==undefined){var subKey=!subKey?entries.shift():subKey.concat(".",entries.shift());if(message[subKey]){message=message[subKey];subKey=""}}}if(typeof message!=="string"){return null}return message};Lang.prototype._findMessageInTree=function(pathSegments,tree){while(pathSegments.length&&tree!==undefined){var dottedKey=pathSegments.join(".");if(tree[dottedKey]){tree=tree[dottedKey];break}tree=tree[pathSegments.shift()]}return tree};Lang.prototype._applyReplacements=function(message,replacements){for(var replace in replacements){message=message.replace(new RegExp(":"+replace,"gi"),function(match){var value=replacements[replace];var allCaps=match===match.toUpperCase();if(allCaps){return value.toUpperCase()}var firstCap=match===match.replace(/\w/i,function(letter){return letter.toUpperCase()});if(firstCap){return value.charAt(0).toUpperCase()+value.slice(1)}return value})}return message};Lang.prototype._testInterval=function(count,interval){if(typeof interval!=="string"){throw"Invalid interval: should be a string."}interval=interval.trim();var matches=interval.match(intervalRegexp);if(!matches){throw"Invalid interval: "+interval}if(matches[2]){var items=matches[2].split(",");for(var i=0;i<items.length;i++){if(parseInt(items[i],10)===count){return true}}}else{matches=matches.filter(function(match){return!!match});var leftDelimiter=matches[1];var leftNumber=convertNumber(matches[2]);if(leftNumber===Infinity){leftNumber=-Infinity}var rightNumber=convertNumber(matches[3]);var rightDelimiter=matches[4];return(leftDelimiter==="["?count>=leftNumber:count>leftNumber)&&(rightDelimiter==="]"?count<=rightNumber:count<rightNumber)}return false};Lang.prototype._getPluralForm=function(count){switch(this.locale){case"az":case"bo":case"dz":case"id":case"ja":case"jv":case"ka":case"km":case"kn":case"ko":case"ms":case"th":case"tr":case"vi":case"zh":return 0;case"af":case"bn":case"bg":case"ca":case"da":case"de":case"el":case"en":case"eo":case"es":case"et":case"eu":case"fa":case"fi":case"fo":case"fur":case"fy":case"gl":case"gu":case"ha":case"he":case"hu":case"is":case"it":case"ku":case"lb":case"ml":case"mn":case"mr":case"nah":case"nb":case"ne":case"nl":case"nn":case"no":case"om":case"or":case"pa":case"pap":case"ps":case"pt":case"so":case"sq":case"sv":case"sw":case"ta":case"te":case"tk":case"ur":case"zu":return count==1?0:1;case"am":case"bh":case"fil":case"fr":case"gun":case"hi":case"hy":case"ln":case"mg":case"nso":case"xbr":case"ti":case"wa":return count===0||count===1?0:1;case"be":case"bs":case"hr":case"ru":case"sr":case"uk":return count%10==1&&count%100!=11?0:count%10>=2&&count%10<=4&&(count%100<10||count%100>=20)?1:2;case"cs":case"sk":return count==1?0:count>=2&&count<=4?1:2;case"ga":return count==1?0:count==2?1:2;case"lt":return count%10==1&&count%100!=11?0:count%10>=2&&(count%100<10||count%100>=20)?1:2;case"sl":return count%100==1?0:count%100==2?1:count%100==3||count%100==4?2:3;case"mk":return count%10==1?0:1;case"mt":return count==1?0:count===0||count%100>1&&count%100<11?1:count%100>10&&count%100<20?2:3;case"lv":return count===0?0:count%10==1&&count%100!=11?1:2;case"pl":return count==1?0:count%10>=2&&count%10<=4&&(count%100<12||count%100>14)?1:2;case"cy":return count==1?0:count==2?1:count==8||count==11?2:3;case"ro":return count==1?0:count===0||count%100>0&&count%100<20?1:2;case"ar":return count===0?0:count==1?1:count==2?2:count%100>=3&&count%100<=10?3:count%100>=11&&count%100<=99?4:5;default:return 0}};return Lang});

(function () {
    Lang = new Lang();
    Lang.setMessages({"en.js_msgs":{"abandoned":"Abandoned","actions_by_day":"Actions by Day","add_action":"Add Action","add_filter":"Add Another Filter","after_call_work":"After Call Work","agent_calls":"Agent Calls","agent_system_calls":"Agent vs System Calls","all_selected":"All Selected","and":"AND","ascending":": activate to sort column ascending","available_leads":"Available Leads","avg_call_count":"Avg Call Count","avg_handle_time":"Avg Handle Time","avg_rep_time":"Avg Rep Time","avg_service_level":"Average Service Level","avg_talk_time":"Avg Talk Time","back_to_daily_view":"Back to Daily View","back_to_hourly_view":"Back to Hourly View","call_answered":"Call Answered by Time","call_count":"Call Count","call_status_by_type":"Call Status by Type","call_status_count":"Call Status Count","call_time":"Call Time (minutes)","callable":"Callable","callable_leads":"Callable Leads","callable_leads_by_sub":"Callable Leads by SubCampaign","calls":"Calls","campaign_warning":"Note: If you change the Playbook Campaign you may lose filters and actions that are associated with it.","created":"Created","days_to_filter_by":"Days to Filter By","deleted":"Deleted","descending":": activate to sort column descending","destination_campaign":"Destination Campaign","destination_subcampaign":"Destination SubCampaign","dl_warning":"This is a large dataset. It may be faster to download multiple smaller reports.","dropped":"Dropped","end":"End","filter_type":"Filter Type","filter_value":"Filter Value","first":"First","handled":"Handled","handled_calls":"Handled Calls","hold_time":"Hold Time","inbound":"Inbound","info":"Showing _START_ to _END_ of _TOTAL_ entries","info_empty":"Showing 0 to 0 of 0 entries","info_filtered":"(filtered from _MAX_ total entries)","interval_updated":"Interval successfully updated","large_dl_warning":"Report is too large to download. Please run smaller reports or choose a different format","last":"Last","length_menu":"Show _MENU_ entries","loading":"Loading","logins_per_day":"Logins Per Day","longest_hold_time":"Longest Hold Time (minutes)","manual":"Manual","minutes":"Minutes","next":"Next","no_data":"No Data Available ","no_subcampaign":"No Subcampaign","non_callable":"Non Callable","non_callable_by_disp":"Non-Callable Leads by Disposition","none_selected":"None Selected","numb_filter_attempts":"Number of Attempts to Filter by","ordered_by":"order by","outbound":"Outbound","paused":"Paused","playbook_warning":"The following playbooks could not be activated. Please make sure each playbook has filters and actions.","previous":"Previous","processing":"Processing","reload_error_msg":"Something went wrong. Please reload the page.","remove_filter":"Remove Filter","rep":"Rep","reps":"Reps","sales":"Sales","save_warning":"Select a filter before saving changes","search":"Search","select_all":"Select All","select_call_status":"Select Call Status","select_campaign":"Select Campaign","select_inbound_source":"Select Inbound Source","select_one":"Select One","select_rep":"Select Rep","select_report":"Select Report","service_level":"Service Level","sorted_in":"Sorted in","source_campaign":"Source Campaign","source_subcampaign":"Source SubCampaign","start":"Start","system_calls":"System Calls","talk_time":"Talk Time","top_ten_calls_by_camp":"Top 10 Calls by Campaign","total":"Total","total_calls":"Total Calls","total_leads":"Total Leads","undo_selection":"Undo Selection","unselect_all":"Unselect All","view_quarterly":"View Quarterly","voicemails":"Voicemails","waiting":"Waiting","wrapup":"Wrapup","zero_records":"No matching records found"},"es.js_msgs":{"abandoned":"Abandonada","actions_by_day":"Acciones por D\u00eda","add_action":"Agregar Acci\u00f3n","add_filter":"Agregar Otro Filtro","after_call_work":"Tiempo Administrativo Posterior","agent_calls":"Llamadas de Agente","agent_system_calls":"Llamadas de Agentes vs del Sistema","all_selected":"Todo lo Seleccionado","and":"Y","ascending":": activar para ordenar la columna ascendente","available_leads":"Contactos Disponibles","avg_call_count":"Recuento de Llamadas Promedio","avg_handle_time":"Tiempo Promedio de Manejo","avg_rep_time":"Tiempo Promedio por Agente","avg_service_level":"Nivel de Servicio Promedio","avg_talk_time":"Tiempo Promedio de Conversaci\u00f3n","back_to_daily_view":"Volver a La Vista Diaria","back_to_hourly_view":"Volver a La Vista Por Hora","call_answered":"Llamada Contestada por Hora","call_count":"Recuento de Llamadas","call_status_by_type":"Disposiciones de Llamadas por Tipo","call_status_count":"Recuento de Disposiciones","call_time":"Duraci\u00f3n de la Llamada (minutos)","callable":"Invocable","callable_leads":"Cables Invocables","callable_leads_by_sub":"Leads Invocables por SubCampaign","calls":"Llamadas","campaign_warning":"Nota: Si cambia la campa\u00f1a de Playbook, puede perder los filtros y las acciones asociadas a ella.","created":"Creado","days_to_filter_by":"D\u00edas para Filtrar por","deleted":"Eliminado","descending":": activar para ordenar la columna descendente","destination_campaign":"Campa\u00f1a de Destino","destination_subcampaign":"Subcampa\u00f1a de Destino","dl_warning":"Este es un conjunto de datos grande. Podr\u00eda ser m\u00e1s r\u00e1pido descargar m\u00faltiples informes peque\u00f1os.","dropped":"Perdida","end":"Fin","filter_type":"Tipo de Filtro","filter_value":"Valor de filtro","first":"Primero","handled":"Manejada","handled_calls":"Llamadas Manejadas","hold_time":"Tiempo de Espera","inbound":"Entrante","info":"Mostrando _START_ a _END_ de _TOTAL_ entradas","info_empty":"Mostrando 0 a 0 de 0 entradas","info_filtered":"(filtrado de _MAX_ entradas totales)","interval_updated":"Intervalo Actualizado Exitosamente","large_dl_warning":"El informe es demasiado pesado para descargar. Por favor corra informes m\u00e1s peque\u00f1os o escoja un formato diferente","last":"\u00daltimo","length_menu":"Mostrar entradas de _MENU_","loading":"Cargando","logins_per_day":"Inicios de Sesi\u00f3n Por D\u00eda","longest_hold_time":"Tiempo de Espera M\u00e1s Largo (minutos)","manual":"Manual","minutes":"Minutos","next":"Pr\u00f3ximo","no_data":"Datos No Disponibles","no_subcampaign":"Sin Subcampa\u00f1a","non_callable":"No Invocable","non_callable_by_disp":"Conductores no Invocables por Disposici\u00f3n","none_selected":"Nada Seleccionado","numb_filter_attempts":"N\u00famero de Intentos para Filtrar por","ordered_by":"Ordenar por","outbound":"Saliente","paused":"Pausada","playbook_warning":"Los siguientes libros de jugadas no se pudieron activar. Aseg\u00farate de que cada libro de jugadas tenga filtros y acciones.","previous":"Previo","processing":"Procesando","reload_error_msg":"Algo sali\u00f3 mal. Por favor volver a cargar la p\u00e1gina.","remove_filter":"Eliminar Filtro","rep":"Agente","reps":"Agentes","sales":"Ventas","save_warning":"Seleccione un filtro antes de guardar los cambios","search":"Buscar","select_all":"Seleccionar Todo","select_call_status":"Seleccionar Disposici\u00f3n de Llamada","select_campaign":"Seleccionar Campa\u00f1a","select_inbound_source":"Seleccionar Fuente de Entrantes","select_one":"Seleccione uno","select_rep":"Seleccionar Agente","select_report":"Seleccionar Informe","service_level":"Nivel de Servicio","sorted_in":"Ordenados por","source_campaign":"Campa\u00f1a Fuente","source_subcampaign":"SubCampa\u00f1a de Origen","start":"Comienzo","system_calls":"Llamadas al Sistema","talk_time":"Tiempo de Conversaci\u00f3n","top_ten_calls_by_camp":"Los 10 Mejores Recuentos de Llamada por Campa\u00f1a","total":"Total","total_calls":"Total de Llamadas","total_leads":"Total de Contactos","undo_selection":"Deshacer Selecci\u00f3n","unselect_all":"Desmarcar Todo","view_quarterly":"Ver Trimestralmente","voicemails":"Mensajes de Voz","waiting":"Esperando","wrapup":"Finalizando","zero_records":"No se encontraron registros coincidentes"}});
})();
