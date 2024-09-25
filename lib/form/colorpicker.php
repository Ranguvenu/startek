<?php
require_once("HTML/QuickForm/text.php");

/**
 * HTML class for a url type element
 *
 * @author       Jamie Pratt
 * @access       public
 */
class MoodleQuickForm_colorpicker extends HTML_QuickForm_text{
    /**
     * html for help button, if empty then no help
     *
     * @var string
     */
    var $_helpbutton='';
    var $_hiddenLabel=false;

    function MoodleQuickForm_colorpicker($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        global $CFG;
        parent::HTML_QuickForm_text($elementName, $elementLabel, $attributes);
    }

	
    function setHiddenLabel($hiddenLabel){
        $this->_hiddenLabel = $hiddenLabel;
    }
    function toHtml(){
        global $CFG, $COURSE, $USER, $PAGE, $OUTPUT;
		$id     = $this->getAttribute('id');
        $PAGE->requires->js_init_call('M.util.init_colour_picker', array($id));
        $content  = html_writer::start_tag('div', array('class'=>'form-colourpicker defaultsnext'));
        $content .= html_writer::tag('div', $OUTPUT->pix_icon('i/loading', get_string('loading', 'admin'), 'moodle', array('class'=>'loadingicon')), array('class'=>'admin_colourpicker clearfix'));
       
        $content .= html_writer::end_tag('div');
        $content .= '<input size="7" name="'.$this->getName().'" value="'.$this->getValue().'" id="'.$id.'" type="text" >';
        return $content;
    }
   /**
    * Automatically generates and assigns an 'id' attribute for the element.
    *
    * Currently used to ensure that labels work on radio buttons and
    * checkboxes. Per idea of Alexander Radivanovich.
    * Overriden in moodleforms to remove qf_ prefix.
    *
    * @access private
    * @return void
    */
    function _generateId()
    {
        static $idx = 1;

        if (!$this->getAttribute('id')) {
            $this->updateAttributes(array('id' => 'id_'. substr(md5(microtime() . $idx++), 0, 6)));
        }
    } // end func _generateId
    /**
     * set html for help button
     *
     * @access   public
     * @param array $help array of arguments to make a help button
     * @param string $function function name to call to get html
     */
    function setHelpButton($helpbuttonargs, $function='helpbutton'){
        debugging('component setHelpButton() is not used any more, please use $mform->setHelpButton() instead');
    }
    /**
     * get html for help button
     *
     * @access   public
     * @return  string html for help button
     */
    function getHelpButton(){
        return $this->_helpbutton;
    }
    /**
     * Slightly different container template when frozen. Don't want to use a label tag
     * with a for attribute in that case for the element label but instead use a div.
     * Templates are defined in renderer constructor.
     *
     * @return string
     */
    function getElementTemplateType(){
        if ($this->_flagFrozen){
            return 'static';
        } else {
            return 'default';
        }
    }
	

}
