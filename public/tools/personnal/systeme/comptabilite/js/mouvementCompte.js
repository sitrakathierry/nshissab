$(document).ready(function(){
    $("#search_date").datepicker()
    $("#search_date_debut").datepicker()
    $("#search_date_fin").datepicker()
    var instance = new Loading(files.loading)
    var elemSearch = [
        {
            name: "currentDate",
            action:"change",
            selector : "#search_current_date"
        },
        {
            name: "dateDeclaration",
            action:"change",
            selector : "#search_date"
        },
        {
            name: "dateDebut",
            action:"change",
            selector : "#search_date_debut"
        },
        {
            name: "dateFin",
            action:"change",
            selector : "#search_date_fin"
        },
        {
            name: "annee",
            action:"keyup",
            selector : ".search_annee"
        },
        {
            name: "annee",
            action:"change",
            selector : ".search_annee"
        },
        {
            name: "mois",
            action:"change",
            selector : "#search_mois"
        },
        {
            name:"compte",
            action:"keyup",
            selector:"#cmp_search_compte"
        },
        {
            name:"personne",
            action:"keyup",
            selector:"#cmp_search_nom_concerne"
        },
        {
            name:"idBanque",
            action:"change",
            selector:"#cmp_search_banque"
        },
        {
            name:"idCategorie",
            action:"change",
            selector:"#cmp_search_categorie"
        }
    ] 
    
    function searchMouvementCompte()
    {
        var instance = new Loading(files.search) ;
        $(".elementMouvementCompte").html(instance.search(11)) ;
        var formData = new FormData() ;
        for (let j = 0; j < elemSearch.length; j++) {
            const search = elemSearch[j];
            formData.append(search.name,$(search.selector).val());
        }
        formData.append("affichage",$("#search_mouvement_compte").val())
        $.ajax({
            url: routes.compta_mouvement_compte_search,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $(".elementMouvementCompte").empty().html(response) ;
            }
        })
    }

    elemSearch.forEach(elem => {
        $(document).on(elem.action,elem.selector,function(){
            searchMouvementCompte()
        })
    })

    $("#search_mouvement_compte").change(function(){

        $("#caption_search_date").hide()
        $("#caption_search_date_debut").hide()
        $("#caption_search_date_fin").hide()
        $("#caption_search_mois").hide()
        $("#caption_search_annee").hide()

        if($(this).val() == "JOUR")
        {
            var currentDate = new Date();
            var day = currentDate.getDate();
            var month = currentDate.getMonth() + 1; // Les mois sont indexés à partir de zéro, donc nous ajoutons 1
            var year = currentDate.getFullYear();
            if (month < 10) {
                month = '0' + month;
                }
            var formattedDate = day + '/' + month + '/' + year;

            $("#search_current_date").val(formattedDate)
        }
        else if($(this).val() == "SPEC")
        {
            $("#caption_search_date").show()
        }
        else if($(this).val() == "LIMIT")
        {
            $("#caption_search_date_debut").show()
            $("#caption_search_date_fin").show()
        }
        else if($(this).val() == "MOIS")
        {
            var currentDate = new Date();
            var month = currentDate.getMonth() + 1; 
            $("#search_mois").val(month)

            $("#caption_search_annee").show()
            $("#caption_search_mois").show()

            $(".chosen_select").trigger("chosen:updated")
        }
        
        searchMouvementCompte()

        var currentDate = new Date();
        var year = currentDate.getFullYear();
        var month = currentDate.getMonth() + 1; 

        $("#search_date").val("")
        $("#search_date_debut").val("")
        $("#search_date_fin").val("")
        $("#search_mois").val(month)
        $(".search_annee").val(year)

        $(".chosen_select").trigger("chosen:updated")
    })

    $(".vider").click(function(){
        searchMouvementCompte()
    })

    $(document).on('click',".btn_unlock_mvt",function(){
        var self = $(this)
        var id_user_admin = $(this).data("iduser") ;
        var user_admin = $(this).data("nameuser") ;
        $.confirm({
            title: "Authentification",
            content:`
            <div class="w-100 text-left">
                <label for="user_admin" class="font-weight-bold">Administrateur</label>
                <input type="text" readonly name="user_admin" id="user_admin" class="form-control font-weight-bold" value="`+user_admin+`">
                <input type="hidden" name="id_user_admin" id="id_user_admin" value="`+id_user_admin+`">
    
                <label for="pass_admin" class="mt-2 font-weight-bold">Mot de passe Administrateur</label>
                <input type="password" name="pass_admin" id="pass_admin" class="form-control" placeholder=". . .">
                <div class="w-100 d-flex mt-2 flex-row align-items-center">
                    <input type="checkbox" class="form-input-check" id="loginTogglePass">
                    <span class="ml-2" id="labelToggle">Afficher le mot de passe</span>
                </div>
            </div>
            `,
            type:"dark",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Annuler',
                    action: function(){}
                },
                btn2:{
                    text: 'Valider',
                    btnClass: 'btn-red',
                    keys: ['enter'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.credit_activity_authentification,
                            type:'post',
                            cache: false,
                            data:{
                              id_user_admin:$("#id_user_admin").val(),
                              pass_admin:$("#pass_admin").val()
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

    $(document).on("click","#loginTogglePass",function(){
        if($(this).is(':checked'))
        {
            $("#pass_admin").attr("type","text")
            $("#labelToggle").text("Masquer le mot de passe")
        }
        else
        {
            $("#pass_admin").attr("type","password")
            $("#labelToggle").text("Afficher le mot de passe")
        }
    }) ;

    $(document).on('click',".btn_delete_mvt",function(){
        // compta_banque_operation_delete
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
                            url: routes.compta_banque_operation_delete,
                            type:'post',
                            cache: false,
                            data:{
                                idOpt:self.data("value")
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