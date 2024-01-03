$(document).ready(function(){
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;

    $("#devise_symbole_change").keyup(function(){
        $("#devise_montant_change").val("1 "+$(this).val())
    })

    $("#devise_lettre_change").keyup(function(){
        if(($(this).val()).length < 16)
            $("#devise_label_change").text($(this).val())
        else
            $("#devise_label_change").text(($(this).val()).substring(0, 16)+" ...")   
    })

    $("#devise_create_base").click(function(){
        var devise_symbole_base = $("#devise_symbole_base").val() ;
        var devise_lettre_base = $("#devise_lettre_base").val() ;
        if(devise_symbole_base == "")
        {
            $.alert({
                title: 'Symbole vide',
                content: "Veuillez remplir le champ symbole",
                type:'orange',
            })
            return false;
        }
        else if(devise_lettre_base == "")
        {

            $.alert({
                title: 'Lettre vide',
                content: "Veuillez remplir le champ lettre",
                type:'orange',
            })
            return false;
        }
        $.confirm({
            title: '<i class="fa text-warning fa-warning"></i> <span class="text-warning">Confirmation</span> <i class="fa text-warning fa-warning"></i>',
            content:'Etes-vous sûre ? </br> La devise de base ne peut être crée qu\'<b>une seule fois</b>, vérifier bien avant de valider',
            type:"orange",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non, pas encore',
                    btnClass: 'btn-red',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui, je suis sûre',
                    btnClass: 'btn-blue',
                    keys: ['enter', 'shift'],
                    action: function(){ 
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.param_agence_update,
                            type:'post',
                            cache: false,
                            data: {devise_symbole_base:devise_symbole_base,devise_lettre_base:devise_lettre_base},
                            dataType: 'json',
                            success: function(resp){
                                realinstance.close()
                                $.alert({
                                    title: "Message",
                                    content: resp.message,
                                    type: resp.type,
                                    buttons:{
                                        OK : function(){
                                            location.reload() ;
                                        }
                                    }
                                })
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

    $(document).on("click", "#devise_modif_base", function(){
        if(!$(this).hasClass("btn-info"))
        {
            $("#devise_symbole_base").removeAttr("disabled")
            $("#devise_lettre_base").removeAttr("disabled") 
            $(this).removeClass("btn-perso-one")
            $(this).addClass("btn-info")
            $(this).html('<i class="fa fa-check"></i>&nbsp;Mettre à jour')
        }
        else
        {
            if(devise_symbole_base == "")
            {
                $.alert({
                    title: 'Symbole vide',
                    content: "Veuillez remplir le champ symbole",
                    type:'orange',
                })
                return false;
            }
            else if(devise_lettre_base == "")
            {
    
                $.alert({
                    title: 'Lettre vide',
                    content: "Veuillez remplir le champ lettre",
                    type:'orange',
                })
                return false;
            }
            
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
                        keys: ['enter', 'shift'],
                        action: function(){
                            var realinstance = instance.loading()
                            $.ajax({
                                url: routes.param_agence_update,
                                type:'post',
                                cache: false,
                                data:{
                                    devise_symbole_base:$("#devise_symbole_base").val(),
                                    devise_lettre_base:$("#devise_lettre_base").val(),
                                    devise_modif:"OK"
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
        }
        
    })

    $("#formDevise").submit(function(event){
        event.preventDefault() ;
        var self = $(this)
        $.confirm({
            title: "Confirmation",
            content:"Etes-vous sûre de vouloir enregistrer ?",
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
                    var data = self.serialize();
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.param_devise_save,
                        type:"post",
                        data:data,
                        dataType:"json",
                        success : function(json){
                            realinstance.close()
                            $.alert({
                                title: 'Message',
                                content: json.message,
                                type: json.type,
                                buttons: {
                                    OK: function(){
                                        if(json.type == "green")
                                        {
                                            var elements = data.split("&") ;
                                            elements.forEach(elem => {
                                                $("#"+elem.split("=")[0]).val("")
                                            })
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
    })
    
    

    function updateUserAgent(titre,type,idUser)
    {
        var self = $(this)
        $.confirm({
            title: titre,
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
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.param_utils_attribution_agent_update,
                            type:'post',
                            cache: false,
                            data:{
                                idUser:idUser,
                                type:type,
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
    }

    $(document).on("click",".param_btn_disable_agent",function(){
        updateUserAgent("Désactivation","DESACTIVE",$(this).data("value"))
    })

    $(document).on("click",".param_btn_delete_agent",function(){
        updateUserAgent("Suppression","EFFACER",$(this).data("value"))
    })

    $(document).on("click",".param_btn_enable_agent",function(){
        updateUserAgent("Activation","ACTIVER",$(this).data("value"))
    })

    $("#formService").submit(function(){
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
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        var data = self.serialize()
                        $.ajax({
                            url: routes.param_service_save,
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
    })

    $(".btn_srv_edit").click(function(){
        var self = $(this)
        var nom_service = $(this).closest("tr").find(".nom_service").text()
        $.confirm({
            title: "Modification",
            content:`
            <div class="w-100 text-left">
                <label for="modif_srv_nom" class="mt-2 font-weight-bold">Nom Service</label>
                <input type="text" name="modif_srv_nom" oninput="this.value = this.value.toUpperCase();" id="modif_srv_nom" class="form-control" value="`+nom_service+`">
            </div>
            `,
            type:"orange",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Annuler',
                    action: function(){}
                },
                btn2:{
                    text: 'Valider',
                    btnClass: 'btn-orange',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var param_srv_nom = $("#modif_srv_nom").val()
                        $.confirm({
                            title: "Validation",
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
                                            url: routes.param_service_save,
                                            type:'post',
                                            cache: false,
                                            data:{
                                                idService:self.data("value"),
                                                param_srv_nom:param_srv_nom
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
                    }
                }
            }
        })
        return false ;
    })

    $(".btn_srv_delete").click(function(){
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
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.param_service_delete,
                            type:'post',
                            cache: false,
                            data:{
                                idService:self.data("value")
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
    })
})