<?php
namespace local_users\forms;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
use moodleform;
use context_system;
use costcenter;
use events;
use context_user;
use local_users\functions\userlibfunctions as userlib;

class edit_user_pw extends moodleform {
	public $formstatus;
    public function definition() {
        global $USER, $CFG, $DB, $PAGE;
		$systemcontext = context_system::instance();
        $costcenter = new costcenter();
        $mform = $this->_form;
        
        $form_status = $this->_customdata['form_status'];
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];
        $filemanageroptions = $this->_customdata['filemanageroptions'];
        $admin = $this->_customdata['admin'];
        $open_positionid = $this->_customdata['open_positionid'];
        $open_domainid = $this->_customdata['open_domainid'];
        if($form_status == 0){

	        if (is_siteadmin($USER->id) || has_capability('local/users:manage',$systemcontext)) {
				$sql="select id,fullname from {local_costcenter} where visible = :visible and parentid=:parentid ";
	            $costcenters = $DB->get_records_sql($sql,array('visible' => 1,'parentid' => 0));
	        } 

			if (is_siteadmin($USER) || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
				$organizationlist=array(null=>get_string('select_org', 'local_users'));
				foreach ($costcenters as $scl) {
					$organizationlist[$scl->id]=$scl->fullname;
				}
				$mform->addElement('select', 'open_costcenterid', get_string('organization', 'local_users'), $organizationlist);
				$mform->addRule('open_costcenterid', get_string('errororganization', 'local_users'), 'required', null, 'client');	 
			} else if(has_capability('local/costcenter:manage_ownorganization', $systemcontext)|| has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
				$user_dept=$DB->get_field('user','open_costcenterid', array('id'=>$USER->id));
				$mform->addElement('hidden', 'open_costcenterid', null, array('id' =>'id_open_costcenterid'));
				$mform->setType('open_costcenterid', PARAM_ALPHANUM);
				$mform->setConstant('open_costcenterid', $user_dept);
			}	
	        $count = count($costcenters);
	        $mform->addElement('hidden', 'count', $count);
	        $mform->setType('count', PARAM_INT);

	        // $mform->addElement('text', 'username', get_string('username', 'local_users'));
	        // $mform->addRule('username', get_string('usernamerequired', 'local_users'), 'required', null, 'client');
	        // $mform->setType('username', PARAM_RAW);
			

			$auths = \core_component::get_plugin_list('auth');
	        $enabled = get_string('pluginenabled', 'core_plugin');
	        $disabled = get_string('plugindisabled', 'core_plugin');
	        // $authoptions = array($enabled => array(), $disabled => array());
	        $authoptions = array();
	        $cannotchangepass = array();
	        $cannotchangeusername = array();
	        foreach ($auths as $auth => $unused) {
	        	if($auth == 'nologin')
	        		continue;
	            $authinst = get_auth_plugin($auth);

	            if (!$authinst->is_internal()) {
	                $cannotchangeusername[] = $auth;
	            }

	            $passwordurl = $authinst->change_password_url();
	            if (!($authinst->can_change_password() && empty($passwordurl))) {
	                if ($userid < 1 and $authinst->is_internal()) {
	                    // This is unlikely but we can not create account without password
	                    // when plugin uses passwords, we need to set it initially at least.
	                } else {
	                    $cannotchangepass[] = $auth;
	                }
	            }
	            if (is_enabled_auth($auth)) {

	                $authoptions[$auth] = get_string('pluginname', "auth_{$auth}");
	            // } else {
	                // $authoptions[$disabled][$auth] = get_string('pluginname', "auth_{$auth}");
	            }
	        }

	        $mform->addElement('text', 'email', get_string('email', 'local_users'));
	        // $mform->addRule('email', get_string('erroremail','local_users'), 'required', null, 'client');
	        // $mform->addRule('email', get_string('emailerror', 'local_users'), 'email', null, 'client');
	        $mform->setType('email', PARAM_RAW);

			$mform->addElement('passwordunmask', 'password', get_string('password'), 'size="20"');
			$mform->addHelpButton('password', 'newpassword');
			$mform->setType('password', PARAM_RAW);

	   //	   $mform->addElement('text', 'firstname', get_string('firstname', 'local_users'));
	  //       $mform->addRule('firstname', get_string('errorfirstname', 'local_users'), 'required', null, 'client');
	  //       $mform->setType('firstname', PARAM_RAW);

	  //       $mform->addElement('text', 'lastname', get_string('lastname', 'local_users'));
	  //       $mform->addRule('lastname', get_string('errorlastname', 'local_users'), 'required', null, 'client');
	  //       $mform->setType('lastname', PARAM_RAW);


	        $mform->addElement('text', 'phone1', get_string('contactno', 'local_users'));
	        $mform->addRule('phone1', get_string('numeric','local_users'), 'numeric', null, 'client');
	        $mform->addRule('phone1', get_string('phoneminimum', 'local_users'), 'minlength', 10, 'client');
	        $mform->addRule('phone1', get_string('phonemaximum', 'local_users'), 'maxlength', 15, 'client');
	        $mform->setType('phone1', PARAM_RAW);


      //   	$mform->addElement('text', 'open_employeeid', get_string('serviceid', 'local_users'));
      //   	$mform->addRule('open_employeeid',  get_string('employeeidrequired','local_users'),  'required',  '',  'client');
      //   	$mform->addRule('open_employeeid',  get_string('open_employeeiderror','local_users'),  'alphanumeric',  'extraruledata',  'client');
	     //    $mform->setType('open_employeeid', PARAM_RAW);

		    // $mform->addElement('text', 'city', get_string('open_location','local_users'));
	     //    $mform->setType('city', PARAM_RAW);
	     //    $mform->addRule('city', get_string('open_locationrequired', 'local_users'), 'required', null, 'client');

		    
		}

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id',  $id);
        $mform->addElement('hidden', 'form_status');
        $mform->setType('form_status', PARAM_INT);
        $mform->setDefault('form_status',  $form_status);
        $mform->disable_form_change_checker();

    }

    public function definition_after_data() {
        global $USER, $CFG, $DB, $OUTPUT;
        $mform = & $this->_form;
        $form_status = $this->_customdata['form_status'];
        if ($userid = $mform->getElementValue('id')) {
            $user = $DB->get_record('user', array('id' => $userid));
        } else {
            $user = false;
        }
        // print picture
        if (empty($USER->newadminuser)) {
            if ($user) {
                $context = context_user::instance($user->id, MUST_EXIST);
                $fs = get_file_storage();
                $hasuploadedpicture = ($fs->file_exists($context->id, 'user', 'icon', 0, '/', 'f2.png') || $fs->file_exists($context->id, 'user', 'icon', 0, '/', 'f2.jpg'));

                if (!empty($user->picture) && $hasuploadedpicture) {
                    $imagevalue = $OUTPUT->user_picture($user, array('courseid' => SITEID, 'size' => 64,'link' => false));
                } else {
                    $imagevalue = get_string('none');
                }
            } else {
                $imagevalue = get_string('none');
            }
            if($form_status == 2){
	            $imageelement = $mform->getElement('currentpicture');
	            $imageelement->setValue($imagevalue);
			}
            if ($user && $mform->elementExists('deletepicture') && !$hasuploadedpicture) {
                $mform->removeElement('deletepicture');
            }
        }
    }

   public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
		$sub_data=data_submitted();
		$errors = parent::validation($data, $files);
        $email = $data['email'];
        // $employeeid = $data['open_employeeid'];
        $id = $data['id'];
        $uname = $data['username'];
        $form_status = $data['form_status'];
        if($form_status == 0){// as these fields are in only form part 1(form_status=0)
	        if(get_config('core', 'allowaccountssameemail') == 0){
			    if (!empty($data['email']) && ($user = $DB->get_record('user', array('email' => $data['email']), '*', IGNORE_MULTIPLE))) {
		            if (empty($data['id']) || $user->id != $data['id']) {
		                $errors['email'] = get_string('emailexists', 'local_users');
		            }
		        }
	    	}
	    	if (!empty($data['email']) && !validate_email($data['email'])) {
	    		$errors['email'] = get_string('emailerror', 'local_users');
	    	}
	        
	        if (!empty($data['password']) /*&& !in_array($data['auth'], $cannotchangepass)*/) {
                $errmsg = ''; // Prevent eclipse warning.
                if (!check_password_policy($data['password'], $errmsg)) {
                    $errors['password'] = $errmsg;
                }
            }else if(empty($data['id']) /*&& !in_array($data['auth'], $cannotchangepass) && empty($data['password'])*/){
            	$errors['password'] = get_string('passwordrequired', 'local_users');
            }
	    	$phone = $data['phone1'];
	    	if($phone){
	    		if(!is_numeric($phone)){
	    			$errors['phone1'] = get_string('numeric','local_users');
	    		}
		    	else if(($phone<999999999 || $phone>10000000000) && $phone){

		    		$errors['phone1'] = get_string('phonenumvalidate', 'local_users');
		    	}
		    }
	    }
        return $errors;
    }
}

