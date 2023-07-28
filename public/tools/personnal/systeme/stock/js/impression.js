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

    $("#stock_print_barcode_test_2").click(function(){
        // generate ptouch code
        var ptouch = new Ptouch(1, {copies: 2}); // select template 1 for two copies
        ptouch.insertData('myObjectName', 'hello world'); // insert 'hello world' in myObjectName
        var data = ptouch.generate();
    
        // Établir la connexion WebSocket en spécifiant l'URL du serveur
        const socket = new WebSocket('ws://192.168.1.200:9100');

        // Event listener for when the connection is established
        socket.addEventListener('open', () => {
            console.log('WebSocket connection established.');
            socket.send(data);
        });
        
        // Event listener for when a message is received from the server
        socket.addEventListener('message', (event) => {
            const receivedMessage = event.data;
            console.log('Received message from server:', receivedMessage);
        });
        
        // Event listener for when an error occurs
        socket.addEventListener('error', (error) => {
            console.error('WebSocket error:', error);
        });
        
        // Event listener for when the connection is closed
        socket.addEventListener('close', (event) => {
            console.log('WebSocket connection closed with code:', event.code, 'Reason:', event.reason);
        });
    
    })


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
        {
            code:"000000000000",
            rect: false,
        },
        "ean13",
        {
            output: "bmp",
            barWidth: 2,
            barHeight: 70,
        }
    );

    initJspm();
    $("#stock_print_barcode_test").click(function(){
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
                        if(myprinter == "")
                        {
                            $.alert({
                                title: 'Message',
                                content: "Veuiller remplir les champs",
                                type: "orange",
                            });
                            return false ;
                        }
                        
                        if (!qz.websocket.isActive())
                            qz.websocket.connect() ;

                        var config = qz.configs.create(myprinter); 
                        barcodeImg = $("#mybarCode").find("object").attr('data').split(";base64,")[1]
                        // console.log(barcodeImg)
                        // var data = [{
                        //     type : 'pixel',
                        //     format : 'text',
                        //     flavor : 'plain',
                        //     data : text_to_print,
                        // }] ;
                        var printData = [
                            {
                                type: 'image',
                                format: 'base64',
                                data: barcodeImg
                            }
                        ];
                        qz.print(config, printData)
                    }
                }
            }
        })
    })

    // Fonction pour télécharger l'image
    function téléchargerImage(base64Image) {
        // Convertir la chaîne Base64 en données binaires
        const byteCharacters = atob(base64Image.split(',')[1]);
    
        // Convertir les données binaires en tableau d'octets (Uint8Array)
        const byteArray = new Uint8Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
        byteArray[i] = byteCharacters.charCodeAt(i);
        }
    
        // Créer un blob à partir du tableau d'octets
        const blob = new Blob([byteArray], { type: 'image/png' });
    
        // Créer un URL object à partir du blob
        const urlObject = URL.createObjectURL(blob);
    
        // Créer un lien de téléchargement
        const downloadLink = document.createElement('a');
        downloadLink.href = urlObject;
        downloadLink.download = 'barcode.png'; // Nom du fichier à télécharger
    
        // Ajouter le lien de téléchargement à la page (facultatif)
        document.body.appendChild(downloadLink);
    
        // Simuler un clic sur le lien pour déclencher le téléchargement
        downloadLink.click();
    
        // Nettoyer l'URL object après le téléchargement
        URL.revokeObjectURL(urlObject);
    
        // Facultatif : supprimer le lien de téléchargement de la page
        document.body.removeChild(downloadLink);
    }

    $("#stock_print_barcode").click(function(){
        barcodeImg = $("#mybarCode").find("object").attr('data')
        téléchargerImage(barcodeImg) ;
    })

})