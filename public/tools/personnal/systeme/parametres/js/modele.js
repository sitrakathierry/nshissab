$(document).ready(function(){
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;
    // var selection = null 
    // $(document).on('mouseup',".Editor-editor", function() {
    //     // Récupérer la sélection
        
    //     selection = window.getSelection();
    //     // var collapsed = selection.isCollapsed
    //     // // console.log(collapsed) ;
    //     // if(!collapsed)
    //     //     return ;
        

    // });

    // $(document).on('keydown',".Editor-editor", function(event) {
    //     if (event.key === 'Enter' && !event.shiftKey) {
    //       // Empêcher le comportement par défaut de la touche "Entrée"
    //       event.preventDefault();
          
    //       // Insérer une nouvelle ligne ou effectuer d'autres actions
    //       var range = window.getSelection().getRangeAt(0);
    //       var newline = document.createElement('br');
    //       range.insertNode(newline);
    //       range.setStartAfter(newline);
    //       range.setEndAfter(newline);
    //     }
    // });

    function resizeBase64Image(base64, newWidth, callback) {
        var img = new Image();
        img.src = base64;
        img.onload = function() {
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            var imgWidth = newWidth ;

            // console.log("width : "+img.width+", height : "+img.height+", mesure : "+((img.width / 2) + 100) )

            // if(img.width > 200 && img.height < ((img.width / 2) + 100))
            //     imgWidth = 350 ;
            canvas.width = imgWidth;
            canvas.height = (img.height / img.width) * imgWidth;
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            callback(canvas.toDataURL('image/jpeg'));
        };
    }

    $(".btnInsertImage").click(function(){
        $("#imageInsert").click()
    })

    $('#imageInsert').on('change', function() {
        var reader = new FileReader();
        reader.onloadend = function() {
            // Afficher les données du fichier
            resizeBase64Image(reader.result, 240, function(resizedBase64) {
                var imageContent = '<img src="'+resizedBase64+'" alt="Image modèle" class="img" >' ;
            });
        }
        // Lire le contenu du fichier
        reader.readAsDataURL(this.files[0]);
    });

    $(document).on("click",".importImageLeft",function(){
        if($(this).hasClass("btn-success"))
        {
            $(this).removeClass("btn-success")
            $(this).addClass("btn-primary")
            $(this).html('<i class="fa fa-square" ></i>&nbsp;Changer Image')
        }
        $("#modele_image_file_left").click()
    })

    $(document).on('change','#modele_image_file_left', function() {
        var self = $(this)
        var reader = new FileReader();
        reader.onloadend = function() {
            $("#mod_image_origine_left").val(reader.result)
            // Afficher les données du fichier
            resizeBase64Image(reader.result, 200, function(resizedBase64) {
                var imageContent = '<img src="'+resizedBase64+'" alt="Image modèle" class="img" >' ;
                $(".modele_image_left").html(imageContent)
                $("#data_modele_image_left").val(imageContent)
                var parentLeft = self.closest(".parentImgLeft")
                parentLeft.find(".mod_content_dimension").show()
                var img = new Image();
                img.src = resizedBase64;
                img.onload = function() {
                    parentLeft.find(".mod_val_largeur").val(img.width)
                    parentLeft.find(".mod_val_hauteur").val(img.height)
                };
            });
        }
        // Lire le contenu du fichier
        reader.readAsDataURL(this.files[0]);
    });

    $(document).on("click",".importImageRight",function(){
        if($(this).hasClass("btn-success"))
        {
            $(this).removeClass("btn-success")
            $(this).addClass("btn-primary")
            $(this).html('<i class="fa fa-square" ></i>&nbsp;Changer Image')
        }
        $("#modele_image_file_right").click()
    })

    $(document).on('change','#modele_image_file_right', function() {
        var self = $(this)
        var reader = new FileReader();
        reader.onloadend = function() {
            $("#mod_image_origine_right").val(reader.result)
            resizeBase64Image(reader.result, 200, function(resizedBase64) {
                var imageContent = '<img src="'+resizedBase64+'" alt="Image modèle" class="img" >' ;
                $(".modele_image_right").html(imageContent)
                $("#data_modele_image_right").val(imageContent)
                var parentRight = self.closest(".parentImgRight")
                parentRight.find(".mod_content_dimension").show()
                var img = new Image();
                img.src = resizedBase64;
                img.onload = function() {
                    parentRight.find(".mod_val_largeur").val(img.width)
                    parentRight.find(".mod_val_hauteur").val(img.height)
                };
            });
            // $(".modele_image_right").html($(".apercuImageModele").html())
            // $("#data_modele_image_right").val($(".apercuImageModele").html())
        }
        // Lire le contenu du fichier
        reader.readAsDataURL(this.files[0]);
    });

    function resizeDynamicBase64Image(base64, newWidth,newHeight, callback) {
        var img = new Image();
        img.src = base64;
        img.onload = function() {
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');

            var imgWidth = newWidth ;
            var imgHeight = newHeight ;
            // // console.log("width : "+img.width+", height : "+img.height+", mesure : "+((img.width / 2) + 100) )
            // if(newHeight == null)
            // {
            //     imgHeight = (img.height / img.width) * imgWidth ;
            // }
            // else if(newWidth == null)
            // {
            //     imgWidth = (img.width / img.height) * imgHeight ;
            // }
            // if(img.width > 200 && img.height < ((img.width / 2) + 100))
            //     imgWidth = 350 ;
            canvas.width = imgWidth ;
            canvas.height = imgHeight ;
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            callback(canvas.toDataURL('image/jpeg'));
        };
    }

    var actions = [
        "change",
        "keyup"
    ]
    for (let i = 0; i < actions.length; i++) {
        const action = actions[i];
        $(document).on(action,".mod_val_largeur",function(){
            var elemOrigine = $(this).data("origine") ;
            var elemValue = $(this).data("value") ;
            var elemTarget = $(this).data("target") ;
            var valSelf = $(this).val()
            var elemHeight = $(this).closest(".mod_content_dimension").find(".mod_val_hauteur")

            resizeDynamicBase64Image($(elemOrigine).val(), parseFloat(valSelf),elemHeight.val(), function(resizedBase64) {
                var imageContent = '<img src="'+resizedBase64+'" alt="Image modèle" class="img" >' ;
                $(elemTarget).html(imageContent)
                $(elemValue).val(imageContent)
            });
        })
    
        $(document).on(action,".mod_val_hauteur",function(){
            var elemOrigine = $(this).data("origine") ;
            var elemValue = $(this).data("value") ;
            var elemTarget = $(this).data("target") ;
            var valSelf = $(this).val()
            var elemWidth = $(this).closest(".mod_content_dimension").find(".mod_val_largeur")

            resizeDynamicBase64Image($(elemOrigine).val(),elemWidth.val(),parseFloat(valSelf), function(resizedBase64) {
                var imageContent = '<img src="'+resizedBase64+'" alt="Image modèle" class="img" >' ;
                $(elemTarget).html(imageContent)
                $(elemValue).val(imageContent)
            });
        })
    }
    

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
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

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
        var labelModele = inputValue == "ENTETE" ? "Entête de page" : "Bas de Page" ;

        $(this).html('<i class="fa fa-check"></i>&nbsp;'+labelModele)
        var self = $(this)

        $("#modele_value").val(inputValue)

        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)
        
        $(".edit_modele").each(function(){
            if (!self.is($(this))) {
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
                $(this).html($(this).data("content"));
            }
        })

    }) ;

    $(".imageModele").click(function(){
        $(this).html('<i class="fa fa-check-circle"></i>')
        var self = $(this)

        $(".imageModele").each(function(){
            if (!self.is($(this))) {
                $(this).empty() ; 
            }
        })

        var realinstance = instance.loading()
        var formData = new FormData() ;
        formData.append("indice",$(this).data("indice"))
        $.ajax({
            url: routes.params_contenu_modele_pdf_get ,
            type:'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                $("#forme_modele_pdf").val(self.data("indice"))
                $("#contentEditModele").html(response) ;
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

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

    $(".apercu_modele").click(function(){
        if($(".modele_bordure").hasClass("btn-danger"))
        {
            $(".modele_bordure").click()
        }
        var contentModele = $(".Editor-editor").get(0);
        html2canvas(contentModele).then(function(canvas) {
            var canvasWidth = canvas.width;
            var canvasHeight = canvas.height;
            let type = 'png'; // image type
            Canvas2Image.saveAsImage(canvas, canvasWidth, canvasHeight, type, "apercuModele");
        });
    })
    
    $("#formModele").submit(function(){
        var self = $(this)
        console.log($(".Editor-editor").html())
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
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.param_modele_pdf_save,
                            type:'post',
                            cache: false,
                            data:{
                                modele_nom: $("#modele_nom").val(),
                                modele_value: $("#modele_value").val(),
                                data_modele_image_left: $("#data_modele_image_left").val(),
                                data_modele_image_right: $("#data_modele_image_right").val(),
                                forme_modele_pdf: $("#forme_modele_pdf").val(),
                                modele_editor: $(".Editor-editor").html(),
                            },
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