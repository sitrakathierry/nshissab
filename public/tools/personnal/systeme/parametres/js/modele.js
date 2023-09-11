$(document).ready(function(){
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;
    var selection = null 
    $(document).on('mouseup',".Editor-editor", function() {
        // Récupérer la sélection
        
        selection = window.getSelection();
        // var collapsed = selection.isCollapsed
        // // console.log(collapsed) ;
        // if(!collapsed)
        //     return ;
        

    });

    $(document).on('keydown',".Editor-editor", function(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
          // Empêcher le comportement par défaut de la touche "Entrée"
          event.preventDefault();
          
          // Insérer une nouvelle ligne ou effectuer d'autres actions
          var range = window.getSelection().getRangeAt(0);
          var newline = document.createElement('br');
          range.insertNode(newline);
          range.setStartAfter(newline);
          range.setEndAfter(newline);
        }
      });

    function resizeBase64Image(base64, newWidth, callback) {
        var img = new Image();
        img.src = base64;
        img.onload = function() {
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            canvas.width = newWidth;
            canvas.height = (img.height / img.width) * newWidth;
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            callback(canvas.toDataURL('image/jpeg'));
        };
    }

    $(".btnInsertImage").click(function(){
        $("#imageInsert").click()
    })
    var modele_editor = new LineEditor("#modele_editor") ;

    $('#imageInsert').on('change', function() {
        if(selection == null)
        {
            $.alert({
                title: 'Message',
                content: "Séléctionner d'abord l'éditeur",
                type: "orange"
            });
            return false ;
        }
        var reader = new FileReader();
        reader.onloadend = function() {
            // Afficher les données du fichier
            resizeBase64Image(reader.result, 170, function(resizedBase64) {
                var imageContent = '<img src="'+resizedBase64+'" alt="Image modèle" class="img" >' ;
                range = selection.getRangeAt(0);
                var imageContent = range.createContextualFragment(imageContent);
                range.insertNode(imageContent);
                // Réinitialiser la sélection
                selection.removeAllRanges();
            });
        }
        // Lire le contenu du fichier
        reader.readAsDataURL(this.files[0]);
    });

    $(".modele_info_societe").click(function(){
        var realinstance = instance.loading()
        var formData = new FormData() ;
        formData.append("information",$(this).data("value"))
        $.ajax({
            url: routes.params_information_societe_get ,
            type:'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                if(selection == null)
                {
                    $.alert({
                        title: 'Message',
                        content: "Séléctionner d'abord l'éditeur",
                        type: "orange"
                    });
                    return false ;
                }
                
                range = selection.getRangeAt(0);
                var valresponse = range.createContextualFragment(response);
                range.insertNode(valresponse);
                selection.removeAllRanges();
                // var contentEditor = modele_editor.getEditorText('#modele_editor') ;
                // modele_editor.setEditorText(contentEditor+response)
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    modele_editor.setEditorText($('#modele_editor').val())

    $("#insert_modele_forme").click(function(){
        var content = ''
        var modele_forme = $("#modele_forme").val()

        var result = appBase.verificationElement([
            modele_forme
        ],[
            "Nombre de colonne",
        ])

        if(!result["allow"])
        {
            $.alert({
                title: 'Message',
                content: result["message"],
                type: result["type"],
            });

            return result["allow"] ;
        }

        modele_forme = parseInt(modele_forme) ;
        element = '' ;
        for (let i = 0; i < modele_forme; i++) {
            var width = (100 / modele_forme);
            element += '<div class="config-editor" style="width:'+width+'%;height:auto;font-size:14px !important;line-height: 24px;"></div>' ;
        }
        content = `
            <h1 style="width:100%;display: flex; flex-direction:row;font-size:14px !important;" >
            `+element+`
            </h1>
        `
        var contentEditor = modele_editor.getEditorText('#modele_editor') ;
        modele_editor.setEditorText(contentEditor+content)
        $("#modele_forme").val("")
    })

    $(".edit_modele").click(function(){
        var btnClass = $(this).data("class")
        var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
        var inputValue = $(this).data("value")

        var self = $(this)
        var labelModele = inputValue == "ENTETE" ? "Entête de page" : "Bas de Page" ;

        $(".caption_modele").text(labelModele)
        $("#modele_value").val(inputValue)

        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)
        $(".edit_modele").each(function(){
            if (!self.is($(this))) {
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
            }
        })
    }) ;

    $(".modele_bordure").click(function(){
        if($(this).hasClass("btn-danger"))
        {
            $(this).removeClass("btn-danger");
            $(this).addClass("btn-primary") ; 
            $(this).html('<i class="fa fa-diamond"></i>&nbsp;Avec bordure')
            $(".Editor-editor .config-editor").each(function(){
                $(this).removeClass("config-editor")
                $(this).addClass("none-editor")
            })
        }
        else
        {
            $(this).removeClass("btn-primary");
            $(this).addClass("btn-danger") ; 
            $(this).html('<i class="fa fa-square"></i>&nbsp;Aucune bordure')
            $(".Editor-editor .none-editor").each(function(){
                $(this).removeClass("none-editor")
                $(this).addClass("config-editor")
            })
        }
    })
    // to canvas
    // $('.apercu_modele"').click(function(e) {
        
    // });
    // qz.websocket.connect() ;
    $(".apercu_modele").click(function(){
        // qz.printers.find().then((allPrinters) => {
        //     var options = '<option>-</option>' ;
        //     for (var i = 0; i < allPrinters.length; i++) {
        //         options += '<option>' + allPrinters[i] + '</option>';
        //     }
        //     $.confirm({
        //         title: 'Aperçu',
        //         content:`
        //             <div class="w-100 text-left">
        //                 <label for="stock_printers" class="font-weight-bold">Imprimante</label>  
        //                 <select class="custom-select custom-select-sm" id="stock_printers">
        //                     `+options+`
        //                 </select>
        //             </div>
        //             `,
        //         type:"blue",
        //         theme:"modern",
        //         buttons : {
        //             Annuler : function(){},
        //             btn2 : 
        //             {
        //                 text: 'Valider',
        //                 btnClass: 'btn-blue',
        //                 keys: ['enter', 'shift'],
        //                 action: function(){
        //                     $(".Editor-editor .config-editor").each(function(){
        //                         $(this).css("padding","10px 20px")
        //                         $(this).css("min-height","100px")
        //                     })

        //                     $(".Editor-editor .none-editor").each(function(){
        //                         $(this).css("padding","10px 20px")
        //                         $(this).css("min-height","100px")
        //                     })

        //                     var myprinter = $("#stock_printers").val()
        //                     if(myprinter == "")
        //                     {
        //                         $.alert({
        //                             title: 'Message',
        //                             content: "Veuiller séléctionner une imprimante",
        //                             type: "orange",
        //                         });
        //                         return false ;
        //                     }
        //                     var config = qz.configs.create(myprinter); 
        //                     var dataToPdf = `
        //                             <div style="width:100%">${$(".Editor-editor").html()}</div>
        //                         `

        //                     var printData = [{
        //                         type: 'pixel',
        //                         format: 'html',
        //                         flavor: 'plain',
        //                         data: dataToPdf
        //                     }];
                            
        //                     qz.print(config, printData) ;

        //                 }
        //             }
        //         }
        //     })
        // })
        if($(".modele_bordure").hasClass("btn-danger"))
        {
            $(".modele_bordure").click()
        }
        var contentModele = $(".Editor-editor").get(0);
        html2canvas(contentModele).then(function(canvas) {
            var canvasWidth = canvas.width;
            var canvasHeight = canvas.height;
            // var img = Canvas2Image.convertToImage(canvas, canvasWidth, canvasHeight);
            let type = 'png'; // image type
            // let w = $('#imgW').val(); // image width
            // let h = $('#imgH').val(); // image height
            // let f = $('#imgFileName').val(); // file name
            // console.log(img) ;
            Canvas2Image.saveAsImage(canvas, canvasWidth, canvasHeight, type, "apercuModele");
        });
    })
    
    $("#formModele").submit(function(){
        var self = $(this)
        if($(".modele_bordure").hasClass("btn-danger"))
        {
            $(".modele_bordure").click()
        }
        var contentModele = $(".Editor-editor").get(0);
        html2canvas(contentModele).then(function(canvas) {
            var canvasWidth = canvas.width;
            var canvasHeight = canvas.height;
            var img = Canvas2Image.convertToImage(canvas, canvasWidth, canvasHeight);
            // let type = 'png'; // image type
            $(".pdf_apercu_modele").html(img)
            // Canvas2Image.saveAsImage(canvas, canvasWidth, canvasHeight, type, "apercuModele");
        });
        $('#modele_editor').val(modele_editor.getEditorText('#modele_editor')) 
        $.confirm({
            title: "Confirmation",
            content:"Êtes-vous sûre ?",
            type:"blue",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-blue',
                    keys: ['enter', 'shift'],
                    action: function(){

                        $(".pdf_contenu_modele").val($(".pdf_apercu_modele").html())
                        var realinstance = instance.loading()
                        var data = self.serialize()
                        $.ajax({
                            url: routes.param_modele_pdf_save,
                            type:'post',
                            cache: false,
                            data:data,
                            dataType: 'json',
                            success: function(json){
                                realinstance.close()
                                $.alert({
                                    title: 'Message',
                                    content: json.message,
                                    type: json.type,
                                    buttons: {
                                        OK: function(){
                                            if(json.type == "green")
                                            {
                                                $("input,select").val("")
                                                $(".chosen_select").trigger("chosen:updated")
                                                location.reload()
                                            }
                                        }
                                    }
                                });
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
        return false ;
    })
})