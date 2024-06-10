$(document).ready(function(){
    var instance = new Loading(files.loading)
    $("#clt_client_date_naiss").datepicker()
    
    $("#formClient").submit(function(){
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
                        var data = self.serialize()
                        $.ajax({
                            url: routes.clt_client_information_save,
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

    $("#clt_type").change(function(){
        var self = $(this) ;
        var realinstance = instance.loading()
        var formData = new FormData() ;
        formData.append("idType",self.val())
        $.ajax({
            url: routes.clt_client_information_get ,
            type:'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                $("#contentClient").empty().html(response)
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })
    
    $(".btn_supprimer_client").click(function(){
        var self = $(this)
        $.confirm({
            title: "Suppression",
            content:"Etes-vous sûre ?",
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
                        url: routes.clt_client_information_supprime,
                        type:"post",
                        data:{idClient:self.data("value")},
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
    }) ;


    var elemSearch = [
        {
            name: "idType",
            action:"change",
            selector : "#clt_type_societe"
        },
        {
            name: "id",
            action:"change",
            selector : "#clt_info_client"
        },
    ] 

    function searchClient()
    {
        var instance = new Loading(files.search) ;
        $(".contentSearchClient").html(instance.otherSearch()) ;
        var formData = new FormData() ;
        for (let j = 0; j < elemSearch.length; j++) {
            const search = elemSearch[j];
            formData.append(search.name,$(search.selector).val());
        }
        $.ajax({
            url: routes.clt_client_information_search ,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $(".contentSearchClient").empty().html(response) ;
            }
        })
    }

    elemSearch.forEach(elem => {
        $(document).on(elem.action,elem.selector,function(){
            searchClient()
        })
    })

})