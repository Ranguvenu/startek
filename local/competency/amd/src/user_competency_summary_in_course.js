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
 * Handle selection changes on the competency tree.
 *
 * @module     local_competency/competencyselect
 * @package    local_competency
 * @copyright  2018 Hemalathacarun  <hemalatha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */




define(['jquery', 'core/modal_factory', 'core/templates'], function($, ModalFactory, Templates) {

  var courseid;

  var  competencyid;

  var  userid;

  var selector='';

  var  loadModalsss = function () {
        ModalFactory.create({
      
      title: 'test title',
      // Can include JS which is run when modal is attached to DOM.
        // This will be the context for our template. So {{name}} in the template will resolve to "Tweety bird".
     // var context = { courseid: courseid, competencyid: competencyid, userid: userid },

      body: Templates.render('local_competency/user_competency_in_course', { courseid: courseid, competencyid: competencyid, userid: userid }),
      footer: 'test footer content',
    })
    .done(function(modal) {
      // Do what you want with your modal.
    });
  }


    return {      

         /**
         * Initialise the module.
         * @method init
         * @param {Number} contextid The context id of the page.
         */
        init: function(courseid, competencyid, userid,selector) {
            console.log('dfdf');
            courseid= courseid; 
            competencyid = competencyid;
            userid= userid;
            selector= selector;


             $(selector).on('click', loadModalsss);

        }

    };


});