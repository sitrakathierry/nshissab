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

    function doPrinting(printerName)
    {
        if (jspmWSStatus()) {

            // Gen sample label featuring logo/image, barcode, QRCode, text, etc by using JSESCPOSBuilder.js

            var escpos = Neodynamic.JSESCPOSBuilder;
            var doc = new escpos.Document()
                // .image(logo, escpos.BitmapDensity.D24)
                // .font(escpos.FontFamily.A)
                // .align(escpos.TextAlignment.Center)
                // .style([escpos.FontStyle.Bold])
                // .size(1, 1)
                // .text("This is a BIG text")
                // .font(escpos.FontFamily.B)
                // .size(0, 0)
                .text("Test Lettre sur QL-800")
                // .linearBarcode('1234567', escpos.Barcode1DType.EAN8, new escpos.Barcode1DOptions(2, 100, true, escpos.BarcodeTextPosition.Below, escpos.BarcodeFont.A))
                // .qrCode('https://mycompany.com', new escpos.BarcodeQROptions(escpos.QRLevel.L, 6))
                // .pdf417('PDF417 data to be encoded here', new escpos.BarcodePDF417Options(3, 3, 0, 0.1, false))
                // .feed(5)
                // .cut()
                .generateUInt8Array();

            var escposCommands = new Uint8Array([ ...doc]);
            // create ClientPrintJob
            var cpj = new JSPM.ClientPrintJob();

            // Set Printer info
            var myPrinter = new JSPM.InstalledPrinter(printerName);
            cpj.clientPrinter = myPrinter;

            // Set the ESC/POS commands
            cpj.binaryPrinterCommands = escposCommands;

            // Send print job to printer!
            cpj.sendToClient();
        }
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
                        doPrinting($("#stock_printers").val())
                        // $.ajax({
                        //     url: routes.stock_generate_barcode,
                        //     type:"post",
                        //     data: {printerName:$("#stock_printers").val()},
                        //     dataType:"json",
                        //     success : function(json){
                        //         realinstance.close()
                        //         console.log(json.test)
                        //     },
                        //     error: function(resp){
                        //         realinstance.close()
                        //         $.alert(JSON.stringify(resp)) ;
                        //     }
                        // })
                    }
                }
            }
        })
    })
})