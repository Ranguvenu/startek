/**
 * Add a create new group modal to the page.
 *
 * @module     gamificationboards_leaderboard/pointsinfo
 * @class      pointsinfo
 * @package    local_users
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {
	var pointsInfo = function(args){
		// alert(1);
		var self = this;
		this.args = args;
		self.info(args);
	};
	pointsInfo.prototype.info = function(args){
		// alert(2);
		var self = this;
		return  Str.get_string('userdata', 'local_gamification').then(function(title) {
			return ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                title: title,
                body: this.getBody(args),
                footer: this.getFooter(),
            });
		}.bind(this)).then(function(modal) {
			this.modal = modal;
			this.modal.show();
			this.modal.getRoot().on(ModalEvents.hidden, function() {
				modal.destroy();
			}.bind(this));
			this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                modal.hide();
                setTimeout(function(){
                    modal.destroy();
                }, 5000);
            }.bind(this));
		});
	}

	pointsInfo.prototype.getBody = function(args){
		// alert(self);
		// console.log(this);
		// console.log(args);
		// console.log(self.context);
		// alert(self.context);

		// alert(3);
		return Fragment.loadFragment('local_gamification', 'userdetails', this.args.context, this.args);
	};

	pointsInfo.prototype.getFooter = function() {
		// alert(4);
        $footer = '<button type="button" class="btn btn-secondary" data-action="cancel">'+M.util.get_string("cancel", "moodle")+'</button>';
        return $footer;
    };


	return 	/** @alias module:local_users/newuser */ {
	        // Public variables and functions.
	        /**
	         * Attach event listeners to initialise this module.
	         *
	         * @method init
	         * @param {string} selector The CSS selector used to find nodes that will trigger this module.
	         * @param {int} contextid The contextid for the course.
	         * @return {Promise}
	         */
        load: function(){
        	// alert('done');
        },
		pointsInfo: function(args){
			// console.log(args);
			return new pointsInfo(args);

		}
	}
});