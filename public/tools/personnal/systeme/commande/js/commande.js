$(document).ready(function(){
    var cmd_creation_description = new LineEditor(".cmd_creation_description") ;
    var instance = new Loading(files.loading) ;
    $("#cmd_date").datepicker()
    $("#cmd_facture").chosen({
        no_results_text: "Aucun resultat trouvé : "
    }); 
    $("#cmd_client").chosen({
        no_results_text: "Aucun resultat trouvé : "
    });

    $("#formBonCommande").submit(function(event){
        event.preventDefault()
        $(".cmd_creation_description").val(cmd_creation_description.getEditorText('.cmd_creation_description'))
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

                    // var formData = new FormData() ;
                    // formData.append("cmd_lieu",$("#cmd_lieu").val()) ;
                    // formData.append("cmd_date",$("#cmd_date").val()) ;
                    // formData.append("cmd_facture",$("#cmd_facture").val()) ;
                    // formData.append("cmd_creation_description",$("#cmd_creation_description").val()) ;
                    var data = self.serialize();
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.cmd_save_bon_commande,
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
                                            $("#cmd_facture").val("")
                                            $("#cmd_client").val("")

                                            $("#cmd_facture").trigger("chosen:updated")
                                            $("#cmd_client").trigger("chosen:updated")
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

    $("#cmd_facture").change(function(){
        var self = $(this)
        var data = new FormData() ;
        data.append("idF",self.val())
        var realinstance = instance.loading()
        $.ajax({
            url: routes.cmd_facture_display,
            type:'post',
            cache: false,
            data: data,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(resp){
                realinstance.close()
                $(".elem_bon_commande").html(resp) ;
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })
})