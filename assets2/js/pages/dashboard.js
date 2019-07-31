var site_url = 'https://dev.trackasins.com/';
// var site_url ='http://www.trackasins.loc/';
$(function() {
    var checkedArray ;
    $(document).ready(function () {
        $('[data-fancybox="images"]').fancybox({});
        $("#profilePicture").change(function() {
            var fileSelect = document.getElementById("profilePicture");
            if (fileSelect.value != "") {
                var formData = new FormData();
                var file = fileSelect.files[0];
                formData.append('profile_picture_file', file);
                $.ajax({
                    type: 'POST',
                    url: site_url + "/settings/change_profile_picture",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        var obj = jQuery.parseJSON(data);
                        if (obj.code == 1) {
                            $("#profilePicHold").attr('src', obj.link);
                        } else {

                        }
                    }
                });
            }
        });
        $("input[name='checkbulk1[]']").on("click", function(){
            var i =0;
            checkedArray = new Array();
            $("input[name='checkbulk1[]']").each(function(){
                if((this).checked) {
                    checkedArray[i] = $(this).val();
                    i++;
                }
            });
            console.log(checkedArray.length);
            if(checkedArray.length >0){
                $("#bulkActionCar").addClass("car_select")
            } else {
                $("#bulkActionCar").removeClass("car_select")
            }
        });
        $("#asinsBulkActionButton").on("click", function () {

        });

        $("#asinsSearchButton").on("click", function(){
            $("#asinsSubmitButton").click();
            // $.ajax({
            //     type: 'POST',
            //     url: site_url + "/dashboard/check_expiration_date",
            //     data: {},
            //     success: function (response) {
            //         var obj = jQuery.parseJSON(response);
            //        if(obj.result == "success"){
            //            // $("#loadingSpinner").hide();
            //            // getAsinsResult(asin);
            //            $("#asinsSubmitButton").click();
            //        } else {
            //            // $("#loadingSpinner").hide();
            //            swal({
            //                    title: "",
            //                    text: obj.message,
            //                    type: "warning",
            //                    showCancelButton: true,
            //                    confirmButtonClass: "confirm-button-color",
            //                    confirmButtonText: "Upgrade",
            //                    cancelButtonText: "Not now",
            //                    closeOnConfirm: false,
            //                },
            //                function(isConfirm) {
            //                    if (isConfirm) {
            //                        window.location.href = site_url + "settings/membership_account";
            //                    }
            //                });
            //            // swal({
            //            //     title: 'Warning',
            //            //     text: obj.message,
            //            //     type: 'warning'
            //            // });
            //        }
            //     }
            // });
        });

        $("#deleteAsinsConfirmButton").click(function(){
            deleteAsinsConfirmButton();
        });
        dataTableShow();

    });


    // var table = $('.mainTable').DataTable();
    // $('#bookSearch').keyup(function () {
    //     table.search($(this).val()).draw();
    // })

    $(document).on('click', '.car', function () {
        if ($(this).hasClass('c')) {
            $(this).removeClass('c');
        } else {
            $(this).addClass('c');
        }
    });

    $(document).on('click', '.cb-label', function () {
        var t = $(this);
        var check = $("#" + t.data('for'));

        if (check.is(':checked')) {
            //$(".car").removeClass('c');
        } else {
            $(".car").addClass('c');
        }
    });

});

function getAsinsResult(asin){
    alert(asin);
    $.ajax({
        type: 'POST',
        url: site_url + "/dashboard/getAsinsResult",
        data: {asin: asin},
        success: function (response) {

        }
    });

}

function dataTableShow(){
    $('.mainTable').DataTable({
        responsive: true,
        stateSave: true,
        stateSaveCallback: function (settings, data) {
            localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data))
        },
        stateLoadCallback: function (settings) {
            return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance))
        },
        "paging": true,
        "pageLength": 50,
        "lengthMenu": [
            [10, 25, 50, 100, 250, 500, 1000, 2000, -1],
            [10, 25, 50, 100, 250, 500, 1000, 2000, "All"]
        ],
        "language": {
            "lengthMenu": "Show _MENU_ products"
        },
        "aoColumns": [
            null,
            null,
            null,
            { "sSortDataType": "dom-checkbox" },
            { "sSortDataType": "dom-checkbox" },
            null,
            null,
            { "sSortDataType": "dom-checkbox" },
            { "sSortDataType": "dom-checkbox" },
            // { "sSortDataType": "dom-checkbox" }
            null
        ],
        order: [[ 5, 'desc' ], [ 6, 'asc' ]]

    });
    $.fn.dataTable.ext.order['dom-checkbox'] = function (settings, col) {
        return this.api().column(col, {order: 'index'}).nodes().map(function (td, i) {
            return $('input', td).prop('checked') ? '1' : '0';
        });
    }

}
function onSelectAll(){
    var i=0;
    var totalCount = 0;
    $("input[name='checkbulk1[]']").each(function(){
        totalCount++
        if((this).checked) {
            i++;
        }
    });

    $("input[name='checkbulk1[]']").each(function(){
        if(totalCount == i){
            $(this).prop('checked', false);
        } else {
            $(this).prop('checked', true);
        }
    });

}
function onChangeTurnOnOff(type){
    var i =0;
    checkedArray = new Array();
    $("input[name='checkbulk1[]']").each(function(){
        if((this).checked) {
            checkedArray[i] = $(this).val();
            i++;
        }
    });

    if(checkedArray.length >0){
        $.ajax({
            type: 'POST',
            url: site_url + "/dashboard/change_bulk_notifications",
            data: { "list" : checkedArray, "type" : type},
            success: function (response) {
                var obj = jQuery.parseJSON(response);
                if(obj.result == "success"){
                    $(".mainTable").dataTable().fnDestroy()
                    $("#dashboardTbody").empty();
                    $("#dashboardTbody").append(obj.show_result);
                    dataTableShow();
                    swal({
                        title: "",
                        text: "All updated successfully.",
                        type: "warning",
                        showCancelButton: false,
                        confirmButtonClass: "confirm-button-color",
                        confirmButtonText: "Ok",
                        closeOnConfirm: false,
                    },
                    function(isConfirm) {
                        if (isConfirm) {
                            window.location.href =site_url +'Dashboard';
                        }
                    });
                    // swal({
                    //     title: 'Success',
                    //     text: "All updated successfully.",
                    //     type: 'success'
                    // });
                    // window.location.reload();
                } else {
                    swal({
                        //                        title: 'Warning',
                        title: '',
                        text: obj.message,
                        type: 'warning',
                        confirmButtonClass: "confirm-button-color",
                        confirmButtonText: "Ok"
                    });
                }
            }
        });
    } else {
        swal({
            //                        title: 'Warning',
            title: '',
            text: "Please select any one item.",
            type: 'warning',
            confirmButtonClass: "confirm-button-color",
            confirmButtonText: "Ok"

        });
    }
}

function  deleteAsinsConfirmButton() {
    $("#deleteAsinsModal").modal('hide');
    var i =0;
    checkedArray = new Array();
    $("input[name='checkbulk1[]']").each(function(){
        if((this).checked) {
            checkedArray[i] = $(this).val();
            i++;
        }
    });

    if(checkedArray.length >0){
        $("#loadingSpinner").show();
        $.ajax({
            type: 'POST',
            url: site_url + "/dashboard/delete_bulk_asins",
            data: { "list" : checkedArray},
            success: function (response) {
                var obj = jQuery.parseJSON(response);
                if(obj.result == "success"){
                    $(".mainTable").dataTable().fnDestroy()
                    $("#dashboardTbody").empty();
                    $("#dashboardTbody").append(obj.show_result);
                    dataTableShow();
                    // swal({
                    //     title: 'Success',
                    //     text: "Deleted successfully.",
                    //     type: 'success'
                    // });
                    $("#loadingSpinner").hide();
                    swal({
                            title: "",
                            text: "Deleted successfully.",
                            type: "warning",
                            showCancelButton: false,
                            confirmButtonClass: "confirm-button-color",
                            confirmButtonText: "Ok",
                            closeOnConfirm: false,
                        },
                        function(isConfirm) {
                            if (isConfirm) {
                                window.location.href =site_url +'Dashboard';
                            }
                        });

                } else {
                    $("#loadingSpinner").hide();
                    swal({
                        title: '',
                        text: obj.message,
                        type: 'warning',
                        confirmButtonClass: "confirm-button-color",
                        confirmButtonText: "Ok"
                    });
                }
            }
        });
    } else {
        swal({
            //                        title: 'Warning',
            title: '',
            text: "Please select any one item.",
            type: 'warning',
            confirmButtonClass: "confirm-button-color",
            confirmButtonText: "Ok"
        });
    }
}

function chackUncheck(userIp) {
    var url_link;
    if (document.getElementById('switch' + userIp).checked) {
        url_link =site_url +  'Dashboard/checkAndUncheck/' + userIp + '/1';
    } else {
        url_link = site_url + 'Dashboard/checkAndUncheck/' + userIp + '/0';
    }
    $.ajax({
        url: url_link,
        success: function (res) {
            var obj = jQuery.parseJSON(res);
            if(obj.result == "success"){
                $(".mainTable").dataTable().fnDestroy()
                $("#dashboardTbody").empty();
                $("#dashboardTbody").append(obj.show_result);
                $("#stockNotificationDiv").html(obj.count);
                dataTableShow();
            } else {
                $(".mainTable").dataTable().fnDestroy()
                $("#dashboardTbody").empty();
                $("#dashboardTbody").append(obj.show_result);
                dataTableShow();
                swal({
                    title: "",
                    text: obj.message,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "confirm-button-color",
                    confirmButtonText: "Upgrade",
                    cancelButtonText: "Not now",
                    closeOnConfirm: false,
                },
                function(isConfirm) {
                    if (isConfirm) {
                        window.location.href = site_url + "settings/membership_account";
                    }
                });
            }
            /*document.getElementById('shows').style.display='none'; */
        }
    })
}

function stockcheck(stockIp) {
    var url_link;
    var chck = document.getElementById('switchstock' + stockIp).checked;
    url_link = site_url+'Dashboard/stockinsert/' + stockIp + '/' + chck;
    $.ajax({
        url: url_link,
        success: function (res3) {
            var obj = jQuery.parseJSON(res3);
            if(obj.result == "success"){
                $(".mainTable").dataTable().fnDestroy()
                $("#dashboardTbody").empty();
                $("#dashboardTbody").append(obj.show_result);
                $("#backStockNotificationsDiv").html(obj.count);
                dataTableShow();
            }else {
                $(".mainTable").dataTable().fnDestroy()
                $("#dashboardTbody").empty();
                $("#dashboardTbody").append(obj.show_result);
                dataTableShow();

                swal({
                        title: "",
                        text: obj.message,
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "confirm-button-color",
                        confirmButtonText: "Upgrade",
                        cancelButtonText: "Not now",
                        closeOnConfirm: false,
                    },
                    function(isConfirm) {
                        if (isConfirm) {
                            window.location.href = site_url + "settings/membership_account";
                        }
                    });
                // swal({
                //     title: 'Warning',
                //     text: obj.message,
                //     type: 'warning'
                // });
            }

        }
    })
}

function emailcheck(emailget) {
    var chck = document.getElementById('switchid' + emailget).checked;
    var url_link;
    url_link = site_url+'Dashboard/emailinsert/' + emailget + '/' + chck;
    $.ajax({
        url: url_link,
        success: function (res1) {
            console.log(res1);

        }
    })
}

function phonecheck(phoneip) {
    console.log(phoneip);
    var url_link;
    var chck = document.getElementById('switchphone' + phoneip).checked;
    url_link = site_url+'Dashboard/phoneinsert/' + phoneip + '/' + chck;
    //console.log(url_link);
    $.ajax({
        url: url_link,
        success: function (res2) {
            console.log(res2);
            /*document.getElementById('shows').style.display='none'; */
        }
    })
}

function show() {
    if (document.getElementById('shows').style.display == 'none') {
        document.getElementById('shows').style.display = 'block';
    }
}
function dele_show() {
    document.getElementById('shows_delete').style.display = 'block';
}

function saveTodatabase() {
    var user_id = document.getElementById('user_id_1').value;
    var id = document.getElementById('id_1').value;
    var img = document.getElementById('img_1').value;
    var title_name = document.getElementById('title_name_1').value;
    var asin = document.getElementById('asin_1').value;
    var amznotseller = document.getElementById('amznotseller_1').value;
    /*var stock_url     = document.getElementById('stock_url_1').value;*/
    var sellerstock = document.getElementById('sellerstock_1').value;
    var rating = document.getElementById('rating_1').value;
    var reviews = document.getElementById('reviews_1').value;
    var seller_name = document.getElementById('seller_name_1').value;
    var seller_url = document.getElementById('seller_url_1').value;
    var seller_ids = document.getElementById('seller_ids_1').value;
    var price = document.getElementById('price_1').value;
    var shipping = document.getElementById('shipping_1').value;

    /*console.log( img);*/

    var url_link = site_url+'Dashboard/SaveToDB/';

    $.ajax({
        type: "POST",
        url: url_link,
        data: "user_id=" + user_id + "&img=" + img + "&title_name=" + title_name + "&asin=" + asin + "&amznotseller=" + amznotseller + "&sellerstock=" + sellerstock + "&rating=" + rating + "&reviews=" + reviews + "&seller_name=" + seller_name + "&seller_url=" + seller_url + "&seller_ids=" + seller_ids + "&price=" + price + "&shipping=" + shipping,
        success: function (msg) {
            var f_data = jQuery.parseJSON(msg);
            if(f_data.result =='success' ){
                $(".mainTable").dataTable().fnDestroy()
                $("#dashboardTbody").empty();
                $("#dashboardTbody").append(f_data.show_result);
                swal({
                    title: "",
                    text: f_data.message,
                    type: "success",
                    showCancelButton: false,
                    confirmButtonClass: "confirm-button-color",
                    confirmButtonText: "Ok",
                    closeOnConfirm: false,
                },
                function(isConfirm) {
                    if (isConfirm) {
                        window.location.href =site_url +'Dashboard';
                    }
                });
                // swal({
                //     title: 'Success',
                //     text: f_data.message,
                //     type: 'success'
                // });

            } else {
                // swal({
                //     title: 'Warning',
                //     text: f_data.message,
                //     type: 'warning'
                // });
                // window.location.href =site_url +'Dashboard';
                swal({
                    title: "",
                    text: f_data.message,
                    type: "warning",
                    showCancelButton: false,
                    confirmButtonClass: "confirm-button-color",
                    confirmButtonText: "Ok",
                    closeOnConfirm: false,
                },
                function(isConfirm) {
                    if (isConfirm) {
                        window.location.href =site_url +'Dashboard';
                    }
                });
            }
            // var finalData = f_data.replace(/\\/g, "");
            // $('#DataTables_Table_0 > tbody:last-child').append(finalData);
        }
    });
    document.getElementById('shows').style.display = 'none';

}