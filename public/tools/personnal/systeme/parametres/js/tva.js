$(document).ready(function(){
    var instance = new Loading(files.loading)
    $(".chosen_select").chosen({
        no_results_text: "Aucun resultat trouvé : "
    });
    $("#formTypeTva").submit(function(event){
        event.preventDefault() ;
        var self = $(this)
        $.confirm({
            title: "Confirmation",
            content:"Etes-vous sûre ?",
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
                        url: routes.param_tva_save_type,
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
                                            $("input").val("")
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

    $(".tva_check_all").click(function(){
        $('.type_tva_check').each(function(){
            if (!$(this).is(':checked'))
            {
                $('.type_tva_check').prop('checked', true);
            }
        })
    })
    
    $(".tva_off_all").click(function(){
        $('.type_tva_check').each(function(){
            if ($(this).is(':checked'))
            {
                $('.type_tva_check').prop('checked', false);
            }
        })
    })
})