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
 * Handle selection changes and actions on the competency tree.
 *
 * @module     local_competency
 * @package    local_competency
 * @copyright  2018 hemalathacarun <hemalatha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery',
        'core/url',
        'core/templates',
        'core/notification',
        'core/str',
        'core/ajax',
        'local_competency/menubar',
        ], function($,url, templates, notification, str, ajax, menubar) {


    var advancedviewCourselist = function(){       
      self._courseid ='';         
      self._competencyid='';
      self._treeModel;
      self._selectedCompetencyId = null;

      
    
    };

    /**
     * Handler when a node in the aria tree is selected.
     * @method selectionChanged
     * @param {Event} evt The event that triggered the selection change.
     * @param {Object} params The parameters for the event. Contains a list of selected nodes.
     * @return {Boolean} 
     */
    advancedviewCourselist.prototype._getajaxCourses = function(evt, params){
    //selectionChanged
  
      // console.log('hi');
        var node = params.selected,
            id = $(node).data('id'),
            btn = $('[data-region="competencyactions"] [data-action="add"]'),
            actionMenu = $('[data-region="competencyactionsmenu"]'),
            selectedTitle = $('[data-region="selected-competency"]'),
            level = 0,
            sublevel = 1;

        menubar.closeAll();
       // console.log(id);

       

        if (typeof id === "undefined") {            
            // Assume this is the root of the tree.
            // Here we are only getting the text from the top of the tree, to do it we clone the tree,
            // remove all children and then call text on the result.
            $('[data-region="competencyinfo"]').html(node.clone().children().remove().end().text());
            $('[data-region="competencyactions"]').data('competency', null);
            actionMenu.hide();

        } else {
            
            var competency = self._treeModel.getCompetency(id);

            level = self._treeModel.getCompetencyLevel(id);
            sublevel = level + 1;

            actionMenu.show();
            $('[data-region="competencyactions"]').data('competency', competency);
           // renderCompetencySummary(competency);
           // UserCompentencyBrief(competency);
            AdvancedUserCompentencyView(competency,3);
            // Log Competency viewed event.
            triggerCompetencyViewedEvent(competency);
        }
        strSelectedTaxonomy(level).then(function(str) {
            selectedTitle.text(str);
            return;
        }).catch(notification.exception);

        strAddTaxonomy(sublevel).then(function(str) {
            btn.show()
                .find('[data-region="term"]')
                .text(str);
            return;
        }).catch(notification.exception);

        // We handled this event so consume it.
        evt.preventDefault();
        return false;
    

    };

    /**
     * Handler when a node in the aria tree is selected.
     * @method selectionChanged
     * @param {Event} evt The event that triggered the selection change.
     * @param {Object} params The parameters for the event. Contains a list of selected nodes.
     * @return {Boolean}
     */
     advancedviewCourselist.prototype._selectionChanged = function(evt, params) {
      // console.log('hi');
        var node = params.selected,
            id = $(node).data('id'),
            btn = $('[data-region="competencyactions"] [data-action="add"]'),
            actionMenu = $('[data-region="competencyactionsmenu"]'),
            selectedTitle = $('[data-region="selected-competency"]'),
            level = 0,
            sublevel = 1;

        menubar.closeAll();
       // console.log(id);

      

        if (typeof id === "undefined") {

            console.log('here ');
            // Assume this is the root of the tree.
            // Here we are only getting the text from the top of the tree, to do it we clone the tree,
            // remove all children and then call text on the result.
            $('[data-region="competencyinfo"]').html(node.clone().children().remove().end().text());
            $('[data-region="competencyactions"]').data('competency', null);
            actionMenu.hide();

        } else {
            
            var competency = self._treeModel.getCompetency(id);
            level = self._treeModel.getCompetencyLevel(id);
            sublevel = level + 1;
            actionMenu.show();
            $('[data-region="competencyactions"]').data('competency', competency);
           // renderCompetencySummary(competency);
           // UserCompentencyBrief(competency);
           advancedviewCourselist.prototype._courselistview(competency);
            // Log Competency viewed event.
            advancedviewCourselist.prototype._triggerCompetencyViewedEvent(competency);
        }
       /* strSelectedTaxonomy(level).then(function(str) {
            selectedTitle.text(str);
            return;
        }).catch(notification.exception);

        strAddTaxonomy(sublevel).then(function(str) {
            btn.show()
                .find('[data-region="term"]')
                .text(str);
            return;
        }).catch(notification.exception); */

        // We handled this event so consume it.
        evt.preventDefault();
        return false;
    };

    /**
    * Deletes a related competency without confirmation.
    *
    * @param {Event} e The event that triggered the action.
    * @method deleteRelatedHandler
    */ 
    advancedviewCourselist.prototype._courselistview = function(competency) {  
      
                   $('[data-region="courseactivitiesview'+competency.competencyframeworkid+'"]').hide();
                  // console.log('[data-region="courseactivitiesview'+competency.competencyframeworkid+'"]');
        var competencycourselist = ajax.call([{
            methodname: 'local_competency_data_for_advancedview_of_courselist',
                args: {competencyid: competency.id}
        }
        ]);

        competencycourselist[0].done(function(context) {
            context.competencies=$.parseJSON(context.competencies);  
            //alert(context);
            //console.log(context);
            templates.render('local_competency/advancedview_of_courselist', context).then(function(html,js) {
                          templates.render('local_competency/loading', {});
            // console.log(html);

              
                $('[data-region="competencyinfo'+competency.competencyframeworkid+'"]').replaceWith(html);
                  $('[data-region="courselistview'+competency.competencyframeworkid+'"]').replaceWith(html);
                  
               //  templates.runTemplateJS();
               //updatedRelatedCompetencies();
               // updatedRelatedCompetencies();
            }).fail(notification.exception);
        }).fail(notification.exception); 

    
    }; // end  of function


    /**
    * Deletes a related competency without confirmation.
    *
    * @param {Event} e The event that triggered the action.
    * @method deleteRelatedHandler
    */ 
    advancedviewCourselist.prototype._courseactvitiesview = function() { 

               var competency = self._treeModel.getCompetency(self._competencyid);
     $('[data-region="courseactivitiesview'+competency.competencyframeworkid+'"]').show();   
     
        var removeRelated = ajax.call([{
            methodname: 'local_competency_data_for_advancedview_of_usercompetency',
                args: {competencyid: self._competencyid,
                       courseid: self._courseid,
                      }
            }
        ]);

        removeRelated[0].done(function(context) {
         
            context.competency_assignedcmodules=$.parseJSON(context.competency_assignedcmodules);  
           
            templates.render('local_competency/advancedview_of_usercompetency', context).then(function(html,js) {
                          templates.render('local_competency/loading', {});
     
                var competency = self._treeModel.getCompetency(self._competencyid);           
                $('[data-region="competencyinfo'+competency.competencyframeworkid+'"]').replaceWith(html);
    
                $('[data-region="courseactivitiesview'+competency.competencyframeworkid+'"]').replaceWith(html);
               //  templates.runTemplateJS();
               //updatedRelatedCompetencies();
               // updatedRelatedCompetencies();
            }).fail(notification.exception);
        }).fail(notification.exception); 

    
    }; // end  of function

    /**
     * Log the competency viewed event.
     *
     * @param  {Object} competency The competency.
     * @method triggerCompetencyViewedEvent
     */
   advancedviewCourselist.prototype._triggerCompetencyViewedEvent = function(competency) {
        if (competency.id !== self._selectedCompetencyId) {
            // Set the selected competency id.
            self._selectedCompetencyId = competency.id;
            ajax.call([{
                    methodname: 'core_competency_competency_viewed',
                    args: {id: competency.id}
            }]);
        }
    };


    advancedviewCourselist.prototype._setter = function(filter,filter_text){      
        // alert('3');
        // alert(filter_text);
        self._filter = filter;
        self._filter_text = filter_text;
        if(self._filter =='inprogress'){
            self._template='block_userdashboard/elearning_courses_innercontent';
            self._targetSelector ='#elearning_inprogress';
        }

        if(self._filter =='completed'){
           self._template='block_userdashboard/elearning_courses_innercontent';
           self._targetSelector ='#elearning_completed';
        }

        if(self._filter=='menu'){
          self._template ='block_userdashboard/userdashboard_courses';
          self._targetSelector ='#elearning_inprogress';
        }

        return  advancedviewCourselist.prototype._getajaxCourses();

    };
    
   

    advancedviewCourselist._callcourseview = function(currentselector,treeModel){    

        self._treeModel= treeModel; 
        $(currentselector).on('selectionchanged',  advancedviewCourselist.prototype._selectionChanged);
       // advancedviewCourselist.prototype._setter(filter,filter_text);
    };

    advancedviewCourselist._callactivities= function(competencyid, competencycourseid){
      self._courseid= competencycourseid;
      self._competencyid=  competencyid;
      advancedviewCourselist.prototype._courseactvitiesview();
    }; 

    /** @alias module:block_userdashboard/userdashboard_elearning.js userdashboardElearning  **/ 
    return advancedviewCourselist;
    
}); // end of main function