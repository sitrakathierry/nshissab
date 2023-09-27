$(document).ready(function(){
    var instance = new Loading(files.loading)

    $("#formAgent").submit(function(){
        var data = $(this).serialize();
        $.confirm({
            title: 'Confirmation',
            content:"Voulez-vous vraiment enregistrer ?",
            type:"blue",
            theme:"modern",
            buttons : {
                NON : function(){},
                OUI : function(){
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.param_utilisateur_save_agent,
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
                                            $("input,select").val("") ;
                                            location.reload()
                                        }
                                    }
                                }
                            });
                            
                        }
                    })
                }
            }
        })
        return false ;
    })

    $(document).on("keyup","#confirm_mot_de_passe",function(){
        var nouveau_mot_de_passe = $("#nouveau_mot_de_passe").val() ;

        if(nouveau_mot_de_passe == "" || $(this).val() == "")
        {
            $(this).parent().find("span").html('<i class="fa fa-info-circle text-secondary"></i>') ;
        }
        else if(nouveau_mot_de_passe !== $(this).val())
        {
            $(this).parent().find("span").html('<i class="fa fa-warning text-danger"></i>') ;
        }
        else
        {
            $(this).parent().find("span").html('<i class="fa fa-check-circle text-success"></i>') ;
        }
    })
    
    $(".btn_update_password").click(function(){
        var self = $(this)
        if($(this).hasClass("isUpdate"))
        {
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
                            var nouveau_mot_de_passe = $("#nouveau_mot_de_passe").val()
                            var confirm_mot_de_passe = $("#confirm_mot_de_passe").val()
                            if(nouveau_mot_de_passe != confirm_mot_de_passe)
                            {
                                $.alert({
                                    title: 'Message',
                                    content: "Veuiller vérifier les champs",
                                    type: "red"
                                });
                                return false ;
                            }
                            var realinstance = instance.loading()
                            $.ajax({
                                url: routes.param_user_update_password,
                                type:'post',
                                cache: false,
                                data:{
                                    idUser:self.data("value"),
                                    newPass:$("#nouveau_mot_de_passe").val()
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
        else
        {
            var realinstance = instance.loading()
            $.ajax({
                url: routes.param_user_content_password_get,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#contentMotDePasse").html(response)
                    self.addClass("isUpdate")
                    self.html('<i class="fa fa-database"></i>&nbsp;Mettre à jour')
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
        }
    })

    $(document).on("click",".displayPass i",function(){
        if($(this).hasClass("fa-eye"))
        {
            $(this).addClass("fa-eye-slash")
            $(this).removeClass("fa-eye")
            $("#nouveau_mot_de_passe").attr("type","text")
        }
        else
        {
            $(this).addClass("fa-eye")
            $(this).removeClass("fa-eye-slash")
            $("#nouveau_mot_de_passe").attr("type","password")
        }
        
    })

})