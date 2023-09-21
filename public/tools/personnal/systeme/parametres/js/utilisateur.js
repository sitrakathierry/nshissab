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
    
})