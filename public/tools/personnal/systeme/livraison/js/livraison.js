$(document).ready(function(){
    var cmd_creation_description = new LineEditor(".lvr_creation_description") ;
    var instance = new Loading(files.loading)
    $("#lvr_date").datepicker()
    
    $("#lvr_val_source").chosen({
        no_results_text: "Aucun resultat trouvé : "
    }); 

    $(".lvr_btn_source").click(function(){
        var btnClass = $(this).data("class")
        var target = $(this).data("target")
        var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
        var inputValue = $(this).data("source")
        var caption = $(this).data("caption")
        var self = $(this)

        $(target).val(inputValue) ;
        $("#lvr_source_caption").text(caption)

        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)

        $(".lvr_btn_source").each(function(){
            if (!self.is($(this))) {
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
            }
        })

        var data = new FormData();
        data.append('source',inputValue)
        var realinstance = instance.loading()
        $.ajax({
            url: routes.lvr_display_source_creation,
            type:'post',
            cache: false,
            data: data,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(resp){
                realinstance.close()
                $("#lvr_val_source").html(resp)
                $("#lvr_val_source").trigger('chosen:updated') ;
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $("#lvr_val_source").change(function(){
        var lvr_source = $("#lvr_source").val()
        var idSource = $(this).val()

        var self = $(this)
        var data = new FormData() ;
        data.append("lvr_source",lvr_source)
        data.append("idSource",idSource)
        var realinstance = instance.loading()
        $.ajax({
            url: routes.lvr_facture_display,
            type:'post',
            cache: false,
            data: data,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(resp){
                realinstance.close()
                $(".elem_bon_livraison").html(resp) ;
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $(document).on('click','.btn_lvr_check',function(){
        if($(this).hasClass('btn-outline-success'))
        {
            $(this).removeClass("btn-outline-success")
            $(this).addClass("btn-success")

            var value = $(this).attr("value")

            $(this).html('<span class="text-uppercase ls-1">Livré</span>')
            $(this).parent().append('<input type="hidden" value="'+value+'" class="lvr_id_facture_detail" name="lvr_id_facture_detail[]">') ;
        }
        else
        {
            $(this).removeClass("btn-success")
            $(this).addClass("btn-outline-success")

            $(this).html('<i class="fa fa-check"></i>')

            $(this).parent().find(".lvr_id_facture_detail").remove()
        }
    })

    $("#formLivraison").submit(function(event){
        event.preventDefault()
        $(".lvr_creation_description").val(cmd_creation_description.getEditorText('.lvr_creation_description'))
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
                        url: routes.lvr_save_bon_livraison,
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

    $(document).on('click','.lvr_btn_check',function(){
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
                            url: routes.lvr_check_bon_livraison,
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

    $(".lvr_btn_imprime").click(function(){
        var realinstance = instance.loading()
        $.ajax({
            url: routes.compta_cheque_details,
            type:'post',
            cache: false,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close() ;
                
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })
})