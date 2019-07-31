$(function(){
   $(document).on('click', '.toggle', function(){
        var open = $(this).data('open');

       if(open != "")
       {
           $(".innerContainer").addClass('hidden');
           $("." + open).removeClass('hidden');

           $(".toggle").removeClass('active');
           $(this).addClass('active');
       }
   });

    $.fn.dataTable.ext.order['dom-checkbox'] = function  ( settings, col )
    {
        return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i ) {
            return $('input', td).prop('checked') ? '1' : '0';
        } );
    }

    $('.mainTable').DataTable({
        stateSave: true,
        stateSaveCallback: function(settings,data) {
            localStorage.setItem( 'DataTables_' + settings.sInstance, JSON.stringify(data) )
        },
        stateLoadCallback: function(settings) {
            return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
        },
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
            { "sSortDataType": "dom-checkbox" }
        ]
    });
});

var asins = [];
$('[data-fancybox="images"]').fancybox({});
function clear() {
    asins = [];
}

function runSingle(asin) {
    clear();
    asins.push(asin);
    openModal();
}

function runMultiple() {
    clear();
    $('.product-select:checked').each(function() {
        asins.push($(this).val());
    });

    if(!asins.length) {
        return alert("Please select at leats one product for report");
    }

    openModal();
}

function openModal() {
    console.log($('#technicalReport'));
    $('#technicalReport').modal('show');
}

function report(type) {
    type = !type ? 1 : type;
    var currentDate = $("#currentDate").val();
    var startDate = '',
        endDate = currentDate;
    var timeSelect = $('input[name="date-range"]:checked').val();
    if(timeSelect) {
       if(timeSelect != 'custom') {
           startDate = timeSelect;
       } else {
           startDate = $('#custom-start-date').val();
           endDate = $('#custom-end-date').val();
       }
    }


    window.open(base_url + 'report/export/' + type + '?startDate=' + startDate + '&endDate=' + endDate + '&asin=' + asins.join(','), '_blank');
}