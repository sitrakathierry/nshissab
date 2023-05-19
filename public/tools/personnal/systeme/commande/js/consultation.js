$(document).ready(function(){
    var instance = new Loading(files.loading) ;
    $(document).on('click','.cmd_btn_check',function(){
        var self = $(this)
        $.confirm({
            title: "Confirmation",
            content:"Vous êtes sûre ?",
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
                        var formD = new FormData() ;
                        formD.append('id',self.attr('value'))
                        $.ajax({
                            url: routes.cmd_check_bon_commande,
                            type:'post',
                            cache: false,
                            data:formD,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
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
    })
})