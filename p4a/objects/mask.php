<?php

/**
 * P4A - PHP For Applications.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2 
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * To contact the authors write to:									<br>
 * CreaLabs															<br>
 * Viale dei Mughetti 13/A											<br>
 * 10151 Torino (Italy)												<br>
 * Tel.:   (+39) 011 735645											<br>
 * Fax:    (+39) 011 735645											<br>
 * Web:    {@link http://www.crealabs.it}							<br>
 * E-mail: {@link mailto:info@crealabs.it info@crealabs.it}
 *
 * The latest version of p4a can be obtained from:
 * {@link http://p4a.sourceforge.net}
 *
 * @link http://p4a.sourceforge.net
 * @link http://www.crealabs.it
 * @link mailto:info@crealabs.it info@crealabs.it
 * @copyright CreaLabs
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @author Fabrizio Balliano <fabrizio.balliano@crealabs.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @package p4a
 */

	/**
	 * The mask is the basic interface object wich contains all widgets and generically every displayed object.
	 * @author Andrea Giardina <andrea.giardina@crealabs.it>
	 * @author Fabrizio Balliano <fabrizio.balliano@crealabs.it>
	 * @package p4a
	 */
	class P4A_MASK extends P4A_OBJECT
	{
		/**
		 * The mask that called the current mask.
		 * @var mask
		 * @access public
		 */
       	var $prev_mask = NULL;
       	      	      	
       	/**
		 * The mask's data source.
		 * @var mixed
		 * @access public
		 */
		var $data = NULL;
		
       	/**
		 * The mask's data browser.
		 * @var DATA_BROWSER
		 * @access public
		 */
		var $data_browser = NULL;

       	/**
		 * The fields collection
		 * @var array
		 * @access public
		 */		
		var $fields = array();
		
		/**
		 * Store the external fields' object_id
		 * @var array
		 * @access private
		 */
		var $external_fields = array();
		
       	/**
		 * Keeps the association between actions events and actions.
		 * @var array
		 * @access public
		 */
		var $map_actions = array();
		
       	/**
		 * Mask's title.
		 * @var string
		 * @access private
		 */
		var $title = NULL;
		
       	/**
		 * Template engine object.
		 * @var object
		 * @access private
		 */
		var $smarty = NULL;
		
       	/**
		 * Stores opening code for form.
		 * @var string
		 * @access private
		 */
		var $sOpen = NULL;
		
		/**
		 * The object with active focus
		 * @var object
		 * @access private
		 */
		var $focus_object = NULL;
		
		/**
		 * Currently used template name.
		 * @var string
		 * @access private
		 */
		var $template_name = NULL;
		
		//todo
		var $_top = array();
		var $_right = array();
		var $_bottom = array();
		var $_left = array();
		
		/**
		 * Mask constructor.
		 * Generates unique ID for the object, istance a new
		 * {@link SHEET} for widget positioning and store
		 * itself into p4a application.
		 * @param string Object name (identifier).
		 */
		function &P4A_Mask($name)
		{
			parent::p4aObject($name, 'ma');

            $this->title = ucwords(str_replace('_', ' ', $this->name)) ;
			$this->useTemplate('default');
		}
		
		//todo
		function  &singleton($name)
		{
			$p4a =& P4A::singleton();
			
			if (! isset($p4a->masks[$name])){
				$p4a->masks[$name] =& new $name($name);
			}
			return $p4a->masks[$name];
		}
		
		/**
		 * Sets the focus on object
		 * @access public
		 * @param object
		 */		
		function setFocus(&$object)
		{
			$this->focus_object =& $object;
		}
		
		/**
		 * Removes focus property
		 * @access public
		 */		
		function unsetFocus()
		{
			unset( $this->focus_object );
			$this->focus_object = NULL;
		}
		
		/**
		 * Inizializes the mask.
		 * It means that the 'init' function of the current mask's listener is called.
		 * @access private
		 */
		function init()
		{
			$this->actionHandler('init');
		}
		
		/**
		 * Shows the mask.
		 * It means that the 'show' function of the current mask's listener is called.
		 * @access private
		 */
		function show()
		{
			return $this->actionHandler('show');
		}
		
		/**
		 * Shows the caller mask.
		 * @access public
		 */
		function showPrevMask()
		{
			$p4a =& P4A::singleton();
			$p4a->showPrevMask();
		}
		
		/**
		 * Tells the mask that we're going to use a template.
		 * @param string	"template name" stands for "template name.tpl" in the "CURRENT THEME\masks\" directory.
		 * @access public
		 */
		function useTemplate($template_name)
		{
			$this->use_template = TRUE;
			$this->template_name = $template_name;
			
			// If smarty is not yes istanced, than we call it.
			if (! is_object($this->smarty)){
				$this->smarty = new SMARTY();
							
				$this->smarty->compile_dir = P4A_SMARTY_MASK_COMPILE_DIR;
				$this->smarty->left_delimiter = P4A_SMARTY_LEFT_DELIMITER;
				$this->smarty->right_delimiter = P4A_SMARTY_RIGHT_DELIMITER; 			
				$this->displayText('theme_path', P4A_THEME_PATH);
				$this->displayText('mask_open', $this->maskOpen());
				$this->displayText('mask_close', $this->maskClose());
			}									
	
			if (file_exists(P4A_SMARTY_MASK_TEMPLATES_DIR . '/' . $this->template_name)) {  
				$this->smarty->template_dir = P4A_SMARTY_MASK_TEMPLATES_DIR;
				$this->displayText('tpl_path', P4A_SMARTY_MASK_TEMPLATES_PATH . '/' . $this->template_name);
				$this->displayText('base_url', P4A_SERVER_URL . P4A_SMARTY_MASK_TEMPLATES_PATH . '/' . $this->template_name . '/');
			}else{
				$this->smarty->template_dir = P4A_SMARTY_DEFAULT_MASK_TEMPLATES_DIR;
				$this->displayText('tpl_path', P4A_SMARTY_DEFAULT_MASK_TEMPLATES_PATH . '/' . $this->template_name);
				$this->displayText('base_url', P4A_SERVER_URL . P4A_SMARTY_DEFAULT_MASK_TEMPLATES_PATH . '/' . $this->template_name . '/');
			} 
		}
		
		/**
		 * Returns the currently used template name.
		 * @access public
		 * @return string
		 */
		function getTemplateName()
		{
			return $this->template_name;
		}
		
		/**
		 * Tells the object that we'll not use a template.
		 * @access public
		 */
		function noUseTemplate()
		{
			$this->use_template = FALSE;
			$this->template_name = NULL;
		}	
			
		/**
		 * Tells the template engine to show an object as a variable.
		 * $object will be shown in the $variable template zone.
		 * @param string	Variable name, stands for a template zone.
		 * @param mixed		Widget or string, the value of the assignment.
		 * @access public
		 */
		function display($variable, &$object)
		{
			unset($this->smarty_var[$variable]);
			$this->smarty_var[$variable] =& $object;
		}
		
		 /**
		 * Tells the template engine to show a strng as a variable.
		 * @param string	Variable name, stands for a template variable.
		 * @param mixed		String, the value of the assignment.
		 * @access public
		 */
		function displayText($variable, $text)
		{
			$this->smarty_var[$variable] = $text;
		}
		
		/**
		 * Sets the title for the mask.
		 * @param string	Mask title.
		 * @access public
		 */
		function setTitle( $title )
		{
			$this->title = $title ;
		}
		
		/**
		 * Returns the title for the mask.
		 * @return string
		 * @access public
		 */
		function getTitle()
		{
			return $this->title ;
		}
		
		/**
		 * Prints out the mask.
		 * @access public
		 */
		function raise()
		{
			$p4a =& P4A::singleton();
			$charset = $p4a->i18n->getCharset();
			header("Content-Type: text/html; charset={$charset}");
			
			$this->smarty->assign('charset', $charset);
			$this->smarty->assign('title', $this->title);
			$this->smarty->assign('css', $p4a->css);
			$this->smarty->assign('focus_id', $this->focus_object->id);
			
			$this->smarty->assign('application_title', $p4a->getTitle());
// 			$this->smarty->assign('sheet', $this->sheet->getAsString());
			                        		
			foreach($this->smarty_var as $key=>$value)
			{
				if (is_object($value)){
					$value = $value->getAsString();
				}
				$this->smarty->assign($key, $value);
			}
			
			$path_template = $this->template_name . '/' . $this->template_name . '.' . P4A_SMARTY_TEMPLATE_EXSTENSION;
			$this->smarty->display($path_template);
		}
		
		/**
		 * Removes every template variable assigned.
		 * @access public
		 */
		function clearDisplay()
		{
			$this->smarty_var = array();
			$this->smarty->clear_all_assign();
			unset($this->smarty);
			$this->useTemplate($this->template_name);
		}
        
        /**
		 * Add a multivalue external field to mask
		 * @access public
		 */
		 //todo
        function addMultivalueField($fieldname)
        {
        	if (! $this->data ){
        		ERROR('NO DATASOURCE SPECIFIED, CALL SET_SOURCE BEFORE');
        	}
			
			$this->fields->build("p4a_multivalue_field", $fieldname);
        	$this->external_fields[] = $this->fields[$fieldname]->id ;
        	
        	$pk_value = $this->fields->{$this->data->pk}->getNewValue();
			$this->fields->$fieldname->setPkValue($pk_value);
        }
        
		/**
		 * Associates a data source with the mask.
		 * Also set the data structure to allow correct widget rendering.
		 * Also moves to the first row of the data source.
		 * @param data_source		 
		 * @access public
		 */ 
		function setSource(&$data_source)
		{
			$this-data =& $data_source;

            foreach($this->data->getFields() as $field){
            	$this->fields->build("P4A_Field",$field, false);
            	$this->fields->$field->setDataField($this->data->fields->$field);
            }

		}
        		
		/**
		 * Loads the current record data.
		 * @param integer		The wanted row number.
		 * @access public
		 */
		function loadRow($num_row = NULL)
		{
			$p4a =& P4A::singleton();
			if( $this->actionHandler( 'beforeLoadRow' ) == ABORT ) return ABORT;
			
			if( $this->isActionTriggered( 'onLoadRow' ) )
			{
				if( $this->actionHandler( 'onLoadRow' ) == ABORT ) return ABORT;
			}
			else
			{				
				$this->data->loadRow($num_row);
				
				foreach($this->external_fields as $object_id){
					$pk_value = $this->fields->{$this->data->pk}->getNewValue();
					$p4a->objects[$object_id]->setPkValue($pk_value);
					$p4a->objects[$object_id]->load();
				}
				
			}
            
            $this->actionHandler( 'afterLoadRow' ) ;
		}
		
		/**
		 * Reloads data for the current record.
		 * @access public
		 */
		function reloadRow()
		{
			$this->loadRow();
		}
		
		/**
		 * Overwrites internal data with the data arriving from the submitted mask.
		 * @access public
		 */ 
		function updateRow()
		{
			$p4a =& P4A::singleton();
			if( $this->actionHandler( 'beforeUpdateRow' ) == ABORT ) return ABORT;

			if( $this->isActionTriggered( 'onUpdateRow' ) )
			{
				if( $this->actionHandler( 'onUpdateRow' ) == ABORT ) return ABORT;
			}
			else
			{
				// FILE UPLOADS
	            foreach(array_keys($this->fields) as $fieldname)
         		{
					$field_type = $this->fields[$fieldname]->getType();
					if ($field_type=='file' or $field_type=='image')
					{
						$new_value  = $this->fields[$fieldname]->getNewValue();
						$old_value  = $this->fields[$fieldname]->getValue();
						$target_dir = P4A_UPLOADS_DIR . '/' . $this->fields[$fieldname]->getUploadSubpath();

						if( !is_dir( $target_dir ) ) {
							mkdir( $target_dir, P4A_UMASK );
						}
					
						$a_new_value = explode( ',', substr( $new_value, 1, -1 ) );
						$a_old_value = explode( ',', substr( $old_value, 1, -1 ) );
					
						if ($old_value === NULL)
						{
							if ($new_value !== NULL)
							{
								$a_new_value[0] = get_unique_file_name( $a_new_value[0], $target_dir );
                    			$new_path = $target_dir . '/' . $a_new_value[0];
								rename(P4A_UPLOADS_DIR . '/' . $a_new_value[1], $new_path );
								$a_new_value[1] = str_replace( P4A_UPLOADS_DIR , '', $new_path );
								$this->fields[$fieldname]->setNewValue( '{' . join($a_new_value, ',') . '}' );
							}
							else
							{							
								$this->fields[$fieldname]->setNewValue( NULL );
							}
						}
						else
						{
							if ($new_value === NULL)
							{
								unlink($target_dir . '/' . $a_old_value[1] );
								$this->fields[$fieldname]->setNewValue( NULL );
							}
							elseif ($new_value!=$old_value)
							{
								unlink($target_dir . '/' . $a_old_value[1] );
								$a_new_value[0] = get_unique_file_name( $a_new_value[0], $target_dir );
								$new_path = $target_dir . '/' . $a_new_value[0];
								rename(P4A_UPLOADS_DIR . '/' . $a_new_value[1], $new_path );
								$a_new_value[1] = str_replace( P4A_UPLOADS_DIR , '', $new_path );
								$this->fields[$fieldname]->setNewValue( '{' . join($a_new_value, ',') . '}' );
							}
						}
					}
	            }
				
				// EXECUTE THE DATA BROWSER COMMIT
				$this->data_browser->commitRow();

				// EXTERNAL FIELDS
				foreach($this->external_fields as $object_id){
					$p4a->objects[$object_id]->update();
				}
			}
			
			$this->actionHandler( 'afterUpdateRow' ) ;
		}
		
		/**
		 * Goes in "new row" modality.
		 * This means that we prepare p4a for adding a new record
		 * to the data source wich is associated to the mask.
		 * @access public
		 */
		function newRow()
		{
			$p4a =& P4A::singleton();
			if( $this->actionHandler( 'beforeNewRow' ) == ABORT ) return ABORT;
			
			if( $this->isActionTriggered( 'onNewRow' ) )
			{
				if( $this->actionHandler( 'onNewRow' ) == ABORT ) return ABORT;
			}
			else
			{
    			$this->data_browser->moveNew();
				foreach($this->external_fields as $object_id){
					$pk_value = $this->fields[$this->data->pk]->getNewValue();
					$p4a->objects[$object_id]->setPkValue($pk_value);
					$p4a->objects[$object_id]->load();
				}
			}
		
			$this->actionHandler( 'afterNewRow' ) ;
		}
		
		/**
		 * Deletes the currently pointed record.
		 * @access public
		 */
		function deleteRow()
		{
			$p4a =& P4A::singleton();
			if( $this->actionHandler( 'beforeDeleteRow' ) == ABORT ) return ABORT;
			
			if( $this->isActionTriggered( 'onDeleteRow' ) )
			{
				if( $this->actionHandler( 'onDeleteRow' ) == ABORT ) return ABORT;
			}
			else
			{    			
				// EXTERNAL FIELDS
				foreach($this->external_fields as $object_id){
					$p4a->objects[$object_id]->setNewValue();
					$p4a->objects[$object_id]->update();
				}
    			
    			$this->data_browser->deleteRow();
			}
			
			$this->actionHandler( 'afterDeleteRow' ) ;
		}
		
		/**
		 * Moves to the next row.
		 * @access public
		 */
		function nextRow()
		{
			if( $this->actionHandler( 'beforeMoveRow' ) == ABORT ) return ABORT;
			
			if( $this->isActionTriggered( 'onMoveRow' ) )
			{
				if( $this->actionHandler( 'onMoveRow' ) == ABORT ) return ABORT;
			}
			else
			{
				/*
    			$this->data_browser->moveNext();
    			$this->loadRow();
    			*/
    			
    			$this->data_browser->moveNext();
			}
			
			$this->actionHandler( 'afterMoveRow' ) ; 
		}
		
		/**
		 * Moves to the previous row.
		 * @access public
		 */
		function prevRow()
		{
			if( $this->actionHandler( 'beforeMoveRow' ) == ABORT ) return ABORT;
			
			if( $this->isActionTriggered( 'onMoveRow' ) )
			{
				if( $this->actionHandler( 'onMoveRow' ) == ABORT ) return ABORT;
			}
			else
			{
				/*
    			$this->data_browser->movePrev();
    			$this->loadRow();
    			*/
    			
    			$this->data_browser->movePrev();
			}
			
			$this->actionHandler( 'afterMoveRow' ) ; 
		}
		
		/**
		 * Moves to the last row.
		 * @access public
		 */
		function lastRow()
		{
			if( $this->actionHandler( 'beforeMoveRow' ) == ABORT ) return ABORT;
			
			if( $this->isActionTriggered( 'onMoveRow' ) )
			{
				if( $this->actionHandler( 'onMoveRow' ) == ABORT ) return ABORT;
			}
			else
			{
				/*
    			$this->data_browser->moveLast();
    			$this->loadRow();
    			*/
    			
    			$this->data_browser->moveLast();
			}
			
			$this->actionHandler( 'afterMoveRow' ) ;
		}

		/**
		 * Moves to the first row.
		 * @access public
		 */
		function firstRow()
		{
			if( $this->actionHandler( 'beforeMoveRow' ) == ABORT ) return ABORT;
			
			if( $this->isActionTriggered( 'onMoveRow' ) )
			{
				if( $this->actionHandler( 'onMoveRow' ) == ABORT ) return ABORT;
			}
			else
			{
				/*
    			$this->data_browser->moveFirst();
    			$this->loadRow();
    			*/
    			
    			$this->data_browser->moveFirst();
			}
			
			$this->actionHandler( 'afterMoveRow' ) ;
		}

		
		/**
		 * Returns the opening code for the mask.
		 * @return string
		 * @access public
		 */
		function maskOpen()
		{
			$this->sOpen  = '';
			$this->sOpen .= '<SCRIPT LANGUAGE="JavaScript1.2">'													. "\n";
			$this->sOpen .= 'function executeEvent(object_name, action_name, param1, param2, param3, param4)'	. "\n";
			$this->sOpen .= '{'																					. "\n";
            $this->sOpen .= '	if (!param1) param1 = "" ;'														. "\n";
            $this->sOpen .= '	if (!param2) param2 = "" ;'														. "\n";
            $this->sOpen .= '	if (!param3) param3 = "" ;'														. "\n";
            $this->sOpen .= '	if (!param4) param4 = "" ;'														. "\n";
			$this->sOpen .= ''																					. "\n";            
			$this->sOpen .= '	document.forms["'. $this->name .'"].object.value = object_name;'				. "\n";
			$this->sOpen .= '	document.forms["'. $this->name .'"].action.value = action_name;'				. "\n";
            $this->sOpen .= '	document.forms["'. $this->name .'"].param1.value = param1;'						. "\n";            
            $this->sOpen .= '	document.forms["'. $this->name .'"].param2.value = param2;'						. "\n";
            $this->sOpen .= '	document.forms["'. $this->name .'"].param3.value = param3;'						. "\n";
            $this->sOpen .= '	document.forms["'. $this->name .'"].param4.value = param4;'						. "\n";
			$this->sOpen .= '	if (typeof document.forms["'. $this->name .'"].onsubmit == "function") {'		. "\n";
			$this->sOpen .= '		document.forms["'. $this->name .'"].onsubmit();'							. "\n";
			$this->sOpen .= '	}'																				. "\n";
			$this->sOpen .= '	document.forms["'. $this->name .'"].submit();'									. "\n";				
			$this->sOpen .= '}'																					. "\n";
			$this->sOpen .= ''																					. "\n";
			$this->sOpen .= 'function isReturnPressed(e)'														. "\n";
			$this->sOpen .= '{'																					. "\n";
			$this->sOpen .= '	var characterCode;'																. "\n";
            $this->sOpen .= ''																					. "\n";
            $this->sOpen .= '	if(e && e.which) {'																. "\n";
			$this->sOpen .= '		e = e; characterCode = e.which;'											. "\n";            
			$this->sOpen .= '	} else {'																		. "\n";
			$this->sOpen .= '		e = event; characterCode = e.keyCode;'										. "\n";
            $this->sOpen .= '	}'																				. "\n";            
            $this->sOpen .= ''																					. "\n";
            $this->sOpen .= '	if(characterCode == 13) {'														. "\n";
            $this->sOpen .= '		return true;'																. "\n";
			$this->sOpen .= '	} else {'																		. "\n";
			$this->sOpen .= '		return false;'																. "\n";
			$this->sOpen .= '	}'																				. "\n";				
			$this->sOpen .= '}'																					. "\n";
			$this->sOpen .= ''																					. "\n";
			$this->sOpen .= 'function setFocus(id)'															. "\n";
			$this->sOpen .= '{'																					. "\n";
			$this->sOpen .= '	if( (id != null) && (document.forms["'. $this->name .'"].elements[id] != null) && (document.forms["'. $this->name .'"].elements[id].disabled == false) ) {' . "\n";
			$this->sOpen .= '		document.forms["'. $this->name .'"].elements[id].focus();'					. "\n";
			$this->sOpen .= '	}'																				. "\n";				
			$this->sOpen .= '}'																					. "\n";
			$this->sOpen .= ''																					. "\n";
			$this->sOpen .= '</SCRIPT>'																			. "\n";			
			$this->sOpen .= '<FORM method="post" enctype="multipart/form-data" name="' . $this->name . '" action="index.php">';
			$this->sOpen .= "<INPUT TYPE='hidden' name='object' value='" . $this->id . "'>" . "\n";
			$this->sOpen .= "<INPUT TYPE='hidden' name='action' value='none'>" . "\n";
            $this->sOpen .= "<INPUT TYPE='hidden' name='param1'>" . "\n";
            $this->sOpen .= "<INPUT TYPE='hidden' name='param2'>" . "\n";
            $this->sOpen .= "<INPUT TYPE='hidden' name='param3'>" . "\n";
            $this->sOpen .= "<INPUT TYPE='hidden' name='param4'>" . "\n";
			return $this->sOpen; 
		}
		
		/**
		 * Returns the closing code for the mask.
		 * @return string
		 * @access public
		 */
		function maskClose()
		{
			$this->sClose = "</FORM>";
			return $this->sClose;
		}
		
		/**
		 * Does nothing.
		 * @access public
		 */
		function none()
		{
		}
	}
?>