/**
 * Copyright (c) CreaLabs SNC (http://www.crealabs.it)
 * Code licensed under AGPL3 license:
 * http://www.gnu.org/licenses/agpl.html
 */

p4a_working = true;

p4a_event_execute_prepare = function (object_name, action_name, param1, param2, param3, param4)
{
	p4a_working = true;
	p4a_rte_update_all_instances();

	if (!param1) param1 = "";
	if (!param2) param2 = "";
	if (!param3) param3 = "";
	if (!param4) param4 = "";

	p4a_form._object.value = object_name;
	p4a_form._action.value = action_name;
	p4a_form.param1.value = param1;
	p4a_form.param2.value = param2;
	p4a_form.param3.value = param3;
	p4a_form.param4.value = param4;
}

p4a_rte_update_all_instances = function ()
{
	for (var i=0; i<p4a_form.elements.length; i++) {
		var e = p4a_form.elements[i];
		if (e.type == 'textarea') {
			try {
				FCKeditorAPI.GetInstance(e.id).UpdateLinkedField();
			} catch (e) {}
		}
	}
}

p4a_event_execute = function (object_name, action_name, param1, param2, param3, param4)
{
	if (p4a_working) return false;
	p4a_event_execute_prepare(object_name, action_name, 0, param1, param2, param3, param4);
	p4a_form.target = '';
	
	if (p4a_ajax_enabled) {
		p4a_form._ajax.value = 2;
		$('#p4a').ajaxSubmit({
			dataType: 'xml',
			success: p4a_ajax_process_response
		});
	} else {
		p4a_form._ajax.value = 0;
		p4a_form.submit();
		p4a_working = false;
	}
}

p4a_keypressed_is_return = function (event)
{
	var characterCode = (window.event) ? event.keyCode : event.which;
	return (characterCode == 13);
}

p4a_keypressed_get = function (event)
{
	return (window.event) ? event.keyCode : event.which;
}

p4a_keypressed_reset = function()
{
	$("input[type!='button']").keypress(function(event){return !p4a_keypressed_is_return(event);});
}

p4a_focus_set = function (id)
{
	try {
		document.forms['p4a'].elements[id].focus();
	} catch (e) {}
}

p4a_event_execute_ajax = function (object_name, action_name, param1, param2, param3, param4)
{
	if (p4a_working) return false;
	p4a_event_execute_prepare(object_name, action_name, param1, param2, param3, param4);
	p4a_form._ajax.value = 1;

	$('#p4a').ajaxSubmit({
		dataType: 'xml',
		success: p4a_ajax_process_response
	});
}

p4a_ajax_process_response = function (response)
{
	try {
		p4a_form._action_id.value = response.getElementsByTagName('ajax-response')[0].attributes[0].value;

		var widgets = response.getElementsByTagName('widget');
		for (i=0; i<widgets.length; i++) {
	   		var object_id = widgets[i].attributes[0].value;
	   		var object = $('#'+object_id);
			if (object.size() > 0) {
				var html = widgets[i].getElementsByTagName('html').item(0);
	   			if (html) {
	   				if (object_id == 'p4a_inner_body') {
	   					object.html(html.firstChild.data);
	   				} else {
		   				object.parent().css('display', 'block').html(html.firstChild.data);
		   			}
	   			}
	   			var javascript = widgets[i].getElementsByTagName('javascript').item(0);
	   			if (javascript) {
	   				eval(javascript.firstChild.data);
	   			}
	   		}
		}
		
		var messages = response.getElementsByTagName('message');
		if (messages.length > 0) {
			var new_messages_container = $('<div class="p4a_system_messages"></div>').appendTo(document.body);
			for (i=0; i<messages.length; i++) {
				$('<div class="p4a_system_message">'+messages[i].firstChild.data+'</div>').appendTo(new_messages_container);
			}
			p4a_messages_show();
		}
		
		p4a_focus_set(response.getElementsByTagName('ajax-response')[0].attributes[1].value);
		if (typeof p4a_png_fix == 'function') p4a_png_fix();
		if (typeof p4a_menu_activate == 'function') p4a_menu_activate();
		p4a_keypressed_reset();
		p4a_working = false;
	} catch (e) {
		p4a_ajax_error();
	}
}

p4a_ajax_error = function ()
{
	p4a_refresh();
}

p4a_refresh = function ()
{
	document.location = 'index.php';
}

p4a_loading_show = function ()
{
	$('#p4a_loading').show();
}

p4a_loading_hide = function ()
{
	$('#p4a_loading').hide();
}

p4a_tooltip_show = function (widget)
{
	var widget = $(widget);
	var id = widget.attr('id');
	var tooltip = $('#'+id+'tooltip');
	tooltip.css('margin-top', widget.outerHeight());
	if (tooltip.bgiframe) tooltip.bgiframe();
	tooltip.show();
	widget.mouseout(function() {tooltip.hide()});
}

p4a_calendar_load = function ()
{
	p4a_load_css(p4a_theme_path + '/jquery/ui.datepicker.css');
	p4a_load_js(p4a_theme_path + '/jquery/ui.datepicker.js', p4a_calendar_init_defaults);
}

p4a_calendar_init_defaults = function ()
{
	$.datepicker._defaults["dateFormat"] = "yy-mm-dd";
	$.datepicker._defaults["dayNamesMin"] = p4a_calendar_daynamesmin;
	$.datepicker._defaults["monthNames"] = p4a_calendar_monthnames;
	$.datepicker._defaults["firstDay"] = p4a_calendar_firstday;
}

p4a_calendar_open = function (id)
{
	var element = $('#'+id);
	if(!element.is($.datepicker.markerClassName)) {
		element.datepicker();
	}
	element.datepicker('show');
	return false;
}

p4a_calendar_select = function (value_id, description_id)
{
	$.get(
		p4a_form.action,
		{_p4a_date_format: $('#'+value_id).attr('value')},
		function (new_value) {
			$('#'+description_id).attr('value', new_value);
		}
	);
}

p4a_db_navigator_load = function (obj_id, current_id, field_to_update, root_movement)
{
	p4a_load_js(p4a_theme_path + '/jquery/ui.core.js',
		function () {
			p4a_load_js(p4a_theme_path + '/jquery/ui.draggable.js',
				function () {
					p4a_load_js(p4a_theme_path + '/jquery/ui.droppable.js',
						function () {
							p4a_db_navigator_init(obj_id, current_id, field_to_update, root_movement);
						}   
					);
				}
			);
		}
	);
}

p4a_db_navigator_init = function (obj_id, current_id, field_to_update, root_movement)
{
	$('#' + obj_id + '_' + current_id).draggable({revert:true});
	$('#' + obj_id + ' li a').droppable({
		accept: '.active_node',
		hoverClass: 'hoverclass',
		tolerance: 'pointer',
		drop: function() {
			$('#' + field_to_update + 'input').val($(this).parent().attr('id').split('_')[1]);
			p4a_event_execute_ajax(field_to_update, 'onchange');
		}
	});
	
	if (root_movement) {
		$('#' + obj_id).droppable({
			accept: '.active_node',
			hoverClass: 'hoverclass',
			tolerance: 'pointer',
			drop: function() {
				$('#' + field_to_update + 'input').val('');
				p4a_event_execute_ajax(field_to_update, 'onChange');
			}
		});
	}
}

p4a_messages_show = function ()
{
	if ($('.p4a_system_messages:visible').size() > 0) return false;
	var p4a_system_messages = $('.p4a_system_messages:hidden:first');
	if (p4a_system_messages.children().size() == 0) {
		p4a_system_messages.remove();
		return false;
	}
	var left = ($(window).width() - p4a_system_messages.outerWidth()) / 2;
	p4a_system_messages
		.css('top', $(window).scrollTop() + 20)
		.css('left', left)
		.fadeIn('normal');
	
	if (p4a_system_messages.bgiframe) {
		p4a_system_messages
			.bgiframe()
			.ifixpng();
	}
	
	setTimeout(function () {
		$('.p4a_system_messages:visible').fadeOut('normal', function() {
			$(this).hide().remove();
			p4a_messages_show();
		});
	}, 2000);
}

p4a_load_js = function (url, callback)
{
	var tag = document.createElement('script');
	tag.type = "text/javascript";
	tag.src = url;
	$(tag).bind('load', callback);
	tag.onreadystatechange = function() {
		if (this.readyState == 'loaded' || this.readyState == 'complete') callback();
	}
	$('head').get(0).appendChild(tag);
}

p4a_load_css = function (url, callback)
{
	if(document.createStyleSheet) {
		document.createStyleSheet(url);
	} else {
		var tag = document.createElement('link');
		tag.type = "text/css";
		tag.rel = "stylesheet";
		tag.media = "all";
		tag.href = url;
		$('head').get(0).appendChild(tag);
	}
}

$(function () {
	p4a_form = $('#p4a')[0];
	$(document)
		.ajaxStart(p4a_loading_show)
		.ajaxStop(p4a_loading_hide)
		.ajaxError(p4a_ajax_error);
	p4a_messages_show();
	setTimeout(p4a_loading_hide, 1000);
	p4a_keypressed_reset();
	p4a_working = false;
});