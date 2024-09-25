define([
    'local_certificates/jquery.dataTables',
    'jquery'
], function(DataTable, $){
    return {
        certificates : function() {
            $('#certificateslist').DataTable({"aaSorting": [],});
        },
    };
});