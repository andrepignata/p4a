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
	 * Intercepts the events on a mask.
	 * @author Andrea Giardina <andrea.giardina@crealabs.it>
	 * @author Fabrizio Balliano <fabrizio.balliano@crealabs.it>
	 * @package p4a
	 */
	class P4A_LISTENER extends P4A_OBJECT
	{
		/**
		 * The object that caused the event.
		 * @var object object
		 * @access private
		 */
		var $active_object = NULL;
		
		/**
		 * Class constructor.
		 * @access private
		 */
		function &p4a_listener()
		{
			parent::p4aObject(NULL, 'lst');
			$this->active_object = NULL;
		}

		/**
		 * Sets the passed object as active.
		 * @param object object		The object that will be set as active.
		 * @access private
		 * @see $active_object
		 */		
		function setActiveObject(&$object)
		{
			unset($this->active_object);
			$this->active_object = &$object;
		}
		
		
		/**
		* Mask's initialization method.
		* This is called on the initialization of the class
		* and never more.
		* This must be overridden.
		* @access private
		*/
		function init()
		{
											
		}
		
		/**
		* Mask's main method.
		* This is called every time the mask is raised.
		* This must be overridden.
		* @access private
		*/		
		function main()
		{
		}
	}
?>