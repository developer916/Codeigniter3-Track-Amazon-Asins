var site_url = 'https://dev.trackasins.com/';
// var site_url ='http://www.trackasins.loc/';
$(document).ready(function() {
    // localStorage.removeItem(DataTables_Table_1);
    dataTableShow();
});

function dataTableShow(){
    $('.mainTable').DataTable({
        stateSave: true,
        stateSaveCallback: function(settings,data) {
            localStorage.setItem( 'DataTables_' + settings.sInstance, JSON.stringify(data) )
        },
        stateLoadCallback: function(settings) {
            return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
        },
        "paging": true,
        "pageLength": 50,
        "ordering": false,
        "lengthMenu": [
            [10, 25, 50, 100, 250, 500, 1000, 2000, -1],
            [10, 25, 50, 100, 250, 500, 1000, 2000, "All"]
        ],
        "language": {
            "lengthMenu": "Show _MENU_ products"
        }
    });
}
function resultShow(){
    $.ajax({
        type: 'POST',
        url: site_url + "/notification/reload_page",
        data: {},
        cache: false,
        contentType: false,
        processData: false,
        success: function (response) {
            var obj = jQuery.parseJSON(response);
            if(obj.result == "success"){
                // $("#loadingSpinner").hide();
                // $("#asins-search-form").submit();
                $("#notificationTbody").empty();
                $("#notificationTbody").append(obj.show_result);
                $(".mainTable").dataTable().fnDestroy()
                dataTableShow();

            }
        }
    });
}

setInterval(function(){
    resultShow();
}, 15000);