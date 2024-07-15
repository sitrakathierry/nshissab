$(document).ready(function(){
    var instance = new Loading(files.loading) ;
    var appBase = new AppBase() ;

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

    var elemSearch = [
        {
            name: "id",
            action:"change",
            selector : "#coiff_search_employee"
        },
        {
            name: "currentDate",
            action:"change",
            selector : "#date_actuel"
        },
        {
            name: "dateFacture",
            action:"change",
            selector : "#date_specifique"
        },
        {
            name: "dateDebut",
            action:"change",
            selector : "#date_fourchette_debut"
        },
        {
            name: "dateFin",
            action:"change",
            selector : "#date_fourchette_fin"
        },
        {
            name: "annee",
            action:"keyup",
            selector : "#date_annee"
        },
        {
            name: "annee",
            action:"change",
            selector : "#date_annee"
        },
        {
            name: "mois",
            action:"change",
            selector : "#date_mois"
        },
    ] 

    $("#coiff_search_date").change(function(){
        var option = $(this).find("option:selected") ;
        var critere = option.data("critere") ;
        if(critere == "")
        {
            $(".elem_date").html("")
            if(option.text() == "TOUS")
            {
                searchSuiviCoiffeur()
            }
            else
            {
                var currentDate = new Date();
                var day = currentDate.getDate();
                var month = currentDate.getMonth() + 1; // Les mois sont indexés à partir de zéro, donc nous ajoutons 1
                var year = currentDate.getFullYear();
                if (month < 10) {
                    month = '0' + month;
                  }
                var formattedDate = day + '/' + month + '/' + year;

                $(".elem_date").html(`
                    <input type="hidden" id="date_actuel" name="date_actuel" value="`+formattedDate+`">
                `)
                $("#date_actuel").change();
            }
            return false;
        }

        if(critere.length == 2)
        {
            $(".elem_date").html(appBase.getItemsDate(critere))
        }
        else
        {
            var index = critere.split(",")
            var elements = ''

            index.forEach(elem => {
                elements += appBase.getItemsDate(elem)
            })

            $(".elem_date").html(elements)
            
        }
        searchSuiviCoiffeur() ;
    })

    function searchSuiviCoiffeur()
    {
        var instance = new Loading(files.search) ;
        $(".elem_suivi_coiffeur").html(instance.search(9)) ;
        var formData = new FormData() ;
        for (let j = 0; j < elemSearch.length; j++) {
            const search = elemSearch[j];
            formData.append(search.name,$(search.selector).val());
        }

        $.ajax({
            url: routes.coiffure_suivi_employee_search , 
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $(".elem_suivi_coiffeur").html(response) ;
            }
        })
    }

    elemSearch.forEach(elem => {
        $(document).on(elem.action,elem.selector,function(){
            searchSuiviCoiffeur() ;
        })
    }) ; 

    $('#staticBackdrop').on('shown.bs.modal', function () {
        // $.alert("focus") ;
    })

    $(".btn_modif_sous_cat").click(function(){
        var realinstance = instance.loading()
        var self = $(this) ;
        $.ajax({
            url: routes.coiffure_categorie_coupes_get,
            type:'post',
            cache: false,
            data: {
                idSousCat:self.data('value'),
            },
            dataType: 'json',
            success: function(response){
                realinstance.close() ;
                $("#coiff_modif_id_scat").val(response.id) ;
                $("#coiff_modif_categorie").val(response.genre) ;
                $("#coiff_modif_sous_categorie").val(response.nom.toUpperCase()) ;
                $(".chosen_select").trigger("chosen:updated") ;
                $('#staticBackdrop').modal('show')
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        }) ;
    }) ;

    $(document).on("click",".btn_valider_modif_scat",function(){
        $("#formModifSousCategorie").submit() ;
    }) ;

    $("#formModifSousCategorie").submit(function(){
        var self = $(this)
        $.confirm({
            title: "Modification",
            content:"Êtes-vous sûre ?",
            type:"orange",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-orange',
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

    $(".btn_delete_sous_cat").click(function(){
        var self = $(this)
        $.confirm({
            title: "Suppression",
            content:"Êtes-vous sûre ?",
            type:"red",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-red',
                    keys: ['enter'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.coiffure_categorie_coupes_delete,
                            type:'post',
                            cache: false,
                            data:{idCat:self.data("value")},
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