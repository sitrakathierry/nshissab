$(document).ready(function(){
    var instance = new Loading(files.loading)
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

    
})