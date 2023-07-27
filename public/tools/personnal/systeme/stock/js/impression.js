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
            var escposCommands = new escpos.Document()
                // .image(logo, escpos.BitmapDensity.D24)
                // .font(escpos.FontFamily.A)
                // .align(escpos.TextAlignment.Center)
                // .style([escpos.FontStyle.Bold])
                // .size(1, 1)
                // .text("This is a BIG text")
                // .font(escpos.FontFamily.B)
                // .size(0, 0)
                // .text("Test Lettre sur QL-800")
                // .linearBarcode('1234567', escpos.Barcode1DType.EAN8, new escpos.Barcode1DOptions(2, 100, true, escpos.BarcodeTextPosition.Below, escpos.BarcodeFont.A))
                // .qrCode('https://mycompany.com', new escpos.BarcodeQROptions(escpos.QRLevel.L, 6))
                .pdf417('PDF417 data to be encoded here', new escpos.BarcodePDF417Options(3, 3, 0, 0.1, false))
                // .feed(5)
                // .cut()
                // .generateUInt8Array();

            // var escposCommands = new Uint8Array([ ...doc]);
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

    $("#mybarCode").barcode(
        "000000000000", // Value barcode (dependent on the type of barcode)
        "ean13" // type (string)
    );

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

        var options = '<option></option>' ;
        for (var i = 0; i < clientPrinters.length; i++) {
            options += '<option>' + clientPrinters[i] + '</option>';
        }
        $.confirm({
            title: 'Configuration',
            content:`
                <div class="w-100 text-left">
                    <label for="stock_printers" class="font-weight-bold">Imprimante</label>  
                    <select class="custom-select custom-select-sm" id="stock_printers">
                    `+options+`
                    </select>

                    <label for="text_to_print" class="mt-3 font-weight-bold">Numéro du code barre</label>
                    <input type="text" name="text_to_print" id="text_to_print" class="form-control" placeholder=". . .">
                </div>
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
                        var myprinter = $("#stock_printers").val()
                        var text_to_print = $("#text_to_print").val()
                        if(myprinter == "" || text_to_print == "")
                        {
                            $.alert({
                                title: 'Message',
                                content: "Veuiller remplir les champs",
                                type: "red",
                            });
                            return false ;
                        }
                        console.log(myprinter)
                        // qz.websocket.disconnect() ;
                        qz.websocket.connect().then(function() {
                            return qz.printers.find(myprinter)
                        }).then(function(found) {
                            var config = qz.configs.create(found); 

                            var data = [{
                                type : 'pixel',
                                format : 'text',
                                flavor : 'plain',
                                data : text_to_print,
                            }] ;

                            // //barcode data
                            // var code = text_to_print;

                            // //convenience method
                            // var chr = function(n) { return String.fromCharCode(n); };

                            // var barcode = '\x1D' + 'h' + chr(80) +   //barcode height
                            // '\x1D' + 'f' + chr(0) +              //font for printed number
                            // '\x1D' + 'k' + chr(69) + chr(code.length) + code + chr(0); //code39

                            qz.print(config, data) ;
                            // qz.print(config, ['\n\n\n\n\n' + barcode + '\n\n\n\n\n']);
                            
                        });

                        // qz.websocket.connect().then(function() {
                        // var config = qz.configs.create("Epson TM88V");
                        // return qz.print(config, ['\n\n\n\n\n' + barcode + '\n\n\n\n\n']);
                        // }).catch(function(err) { alert(err); });
                         
                        // doPrinting($("#stock_printers").val())
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