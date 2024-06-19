$(document).ready(function(){
    var instance = new Loading(files.loading) ;
    $("#formSousCategorie").submit(function(){
        var self = $(this)
        $.confirm({
            title: "Confrmation",
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
                    keys: ['enter'],
                    action: function(){
                        var realinstance = instance.loading()
                        var data = self.serialize() ;
                        $.ajax({
                            url: routes.coiffure_categorie_coupes_save,
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
    }) ;

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

    $(document).on("click",".importImage",function(){
        if($(this).hasClass("btn-success"))
        {
            $(this).removeClass("btn-success")
            $(this).addClass("btn-primary")
            $(this).html('<i class="fa fa-exchange" ></i>')
        }
        $("#modele_image_file").click()
    })

    $(document).on('change','#modele_image_file', function(event) {
        var self = $(this)
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onloadend = function() {
             // Vérifie le type MIME du fichier après la lecture
             if (!file.type.startsWith('image/')) {
                $.alert({
                    title: 'Message',
                    content: "Le fichier sélectionné n'est pas une image.",
                    type: "red",
                });
                return false ;
            }

            $("#mod_image_origine").val(reader.result)

            // Afficher les données du fichier
            resizeBase64Image(reader.result, 200, function(resizedBase64) {
                $(".modele_image").attr("src",resizedBase64) ; 
                $("#mod_image_origine").val(resizedBase64) ; 
            });
        }

        reader.onerror = function() {
            $.alert({
                title: 'Message',
                content: "Erreur lors de la lecture du fichier",
                type: "red",
            });
        };

        // Lire le contenu du fichier
        reader.readAsDataURL(this.files[0]);
    });

    $("#formSoins").submit(function(){
        var self = $(this)
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
                    keys: ['enter'],
                    action: function(){
                        var realinstance = instance.loading()
                        var data = self.serialize() ;
                        $.ajax({
                            url: routes.coiffure_coupes_cheuveux_save,
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
    }) ;

    $("#coiffEmployee").submit(function(){
        var self = $(this)
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
                    keys: ['enter'],
                    action: function(){
                        var realinstance = instance.loading()
                        var data = self.serialize() ;
                        $.ajax({
                            url: routes.coiffure_employee_save,
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
    }) ;

})