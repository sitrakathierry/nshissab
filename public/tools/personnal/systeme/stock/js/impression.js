$(document).ready(function(){
    var instance = new Loading(files.loading)
    //Check JSPM WebSocket status
    function jspmWSStatus() {
        if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Open)
            return true;
        else if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Closed) {
            console.warn('JSPrintManager (JSPM) is not installed or not running! Download JSPM Client App from https://neodynamic.com/downloads/jspm');
            return false;
        }
        else if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Blocked) {
            alert('JSPM has blocked this website!');
            return false;
        }
    }

    var clientPrinters = null;
    var _this = this;

	function initJspm() {
	    //WebSocket settings
	    JSPM.JSPrintManager.auto_reconnect = true;
	    JSPM.JSPrintManager.start();
	    JSPM.JSPrintManager.WS.onStatusChanged = function () {
	        if (jspmWSStatus()) {
	            //get client installed printers
	            JSPM.JSPrintManager.getPrinters().then(function (printersList) {
	                clientPrinters = printersList;
	                var options = '';
	                for (var i = 0; i < clientPrinters.length; i++) {
	                    options += '<option>' + clientPrinters[i] + '</option>';
	                }
	                $('#printer_name').html(options);
	            });
	        }
            
	    };
	}

    $("#stock_print_barcode").click(function(){
        initJspm();
        if(clientPrinters == null)
        {

            $.alert({
                title: 'Message',
                content: "Le navigateur n'arrive pas à se connecter à l'imprimante ",
                type: "orange",
            });
            return false ;
        }

        var options = '<option>-</option>' ;
        for (var i = 0; i < clientPrinters.length; i++) {
            options += '<option>' + clientPrinters[i] + '</option>';
        }
        $.confirm({
            title: 'Liste des Imprimantes',
            content:`
                <select class="custom-select custom-select-sm" id="stock_printers">
                `+options+`
                </select>
                `,
            type:"blue",
            theme:"modern",
            buttons : {
                Annuler : function(){},
                btn2 : 
                {
                    text: 'Imprimer',
                    btnClass: 'btn-blue',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.stock_generate_barcode,
                            type:"post",
                            data: {printerName:$("#stock_printers").val()},
                            dataType:"json",
                            success : function(json){
                                realinstance.close()
                                console.log(json.test)
                            },
                            error: function(resp){
                                realinstance.close()
                                $.alert(JSON.stringify(resp)) ;
                            }
                        })
                    }
                }
            }
        })
    })
})