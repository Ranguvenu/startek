YUI.add('moodle-block_gamification-exportfilter', function (Y) {
    var exportfilter = function () {
        exportfilter.superclass.constructor.apply(this, arguments);
    };
    Y.extend(exportfilter, Y.Base, {
        initializer: function (config) { // 'config' contains the parameter values

            if (config && config.formid) {

                var updatebut = Y.one('#' + config.formid + ' #id_updatefileds');
                var reportselect = Y.one('#' + config.formid + ' #id_reporttype');

                updatebut.setStyle('display', 'none');
                if (reportselect) {
                    reportselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }

            }
        }
    });
    M.block_gamification = M.block_gamification || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.block_gamification.init_exportfilter = function (config) { // 'config' contains the parameter values

        return new exportfilter(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});