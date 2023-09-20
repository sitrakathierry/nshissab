$(document).ready(function(){
    var sav_annule_editor = new LineEditor(".sav_annule_editor") ;
    var instance = new Loading(files.loading) ;
    $(".chosen_select").val("")
    $(".chosen_select").trigger("chosen:updated")
    $("#sav_percent").val("")
    
    $(".content_percent").hide()

    $(document).on('change',"#sav_facture",function(){
        var self = $(this)
        var data = new FormData() ;
        data.append("idF",self.val())
        var realinstance = instance.loading()
        $.ajax({
            url: routes.sav_facture_display,
            type:'post',
            cache: false,
            data: data,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(resp){
                realinstance.close()
                $(".elem_sav_facture").html(resp) ;
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $("#sav_type").change(function(){
        var self = $(this)
        var data = new FormData() ;
        data.append("typeAffichage",self.val())
        var realinstance = instance.loading()
        $.ajax({
            url: routes.sav_contenu_annulation_get,
            type:'post',
            cache: false,
            data: data,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(resp){
                realinstance.close()
                $("#contentType").html(resp) ;
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $(document).on('change',"#sav_caisse",function(){
        var self = $(this)
        var data = new FormData() ;
        data.append("idCs",self.val())
        var realinstance = instance.loading()
        $.ajax({
            url: routes.sav_caisse_display,
            type:'post',
            cache: false,
            data: data,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(resp){
                realinstance.close()
                $(".elem_sav_facture").html(resp) ;
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $("#formMotif").submit(function(event){
        event.preventDefault()
        var data = $(this).serialize();
        $.confirm({
            title: 'Confirmation',
            content:"Voulez-vous vraiment enregistrer ?",
            type:"blue",
            theme:"modern",
            buttons : {
                NON : function(){
                    $("#sav_motif_nom").val("")
                },
                OUI : 
                {
                    text: 'OUI',
                    btnClass: 'btn-blue',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.sav_save_motif,
                            type:"post",
                            data:data,
                            dataType:"json",
                            success : function(json){
                                realinstance.close()
                                $.alert({
                                    title: 'Message',
                                    content: json.message,
                                    type: json.type,
                                });
                                if(json.type == "green")
                                {
                                    var newItem = `
                                    <tr>
                                        <td>`+$("#sav_motif_nom").val()+`</td>
                                        <td class="text-center align-middle">
                                            <button class="btn btn-sm btn-outline-warning font-smaller"><i class="fa fa-edit"></i></button>
                                            <button class="btn ml-2 btn-sm btn-outline-danger font-smaller" ><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    `
                                    $(".elem_motifs").append(newItem) ;
                                    
                                    $("#sav_motif_nom").val("")
                                }
                            }
                        })
                    }
                }
            }
        })
    })

    // stock_edit_entrepot

    $(document).on("click",".sav_btn_motif_update", function(){
        var self = $(this)
        var realIns1 = instance.loading()
        $.ajax({
            url: routes.sav_update_motif,
            type:'post',
            cache:false,
            data:{id:self.attr('value')},
            dataType: 'json',
            success: function(resp){
                realIns1.close()
                $.confirm({
                    title: "Modification Motif",
                    content:`
                    <div class="w-100 text-left">
                        <label for="nom" class=" font-weight-bold">Nom</label>
                        <input type="text" name="nom" id="edit_nom" oninput="this.value = this.value.toUpperCase();" class="form-control" value="`+resp.nom+`" placeholder=". . .">
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
                            text: 'Mettre à jour',
                            btnClass: 'btn-orange',
                            keys: ['enter'],
                            action: function(){
                                var realIns2 = instance.loading()
                                $.ajax({
                                    url: routes.sav_save_motif,
                                    type:'post',
                                    cache:false,
                                    data:{
                                        id:resp.id,
                                        sav_motif_nom:$("#edit_nom").val(),
                                    },
                                    dataType:'json',
                                    success: function(json){
                                        realIns2.close()
                                        $.alert({
                                            title: 'Message',
                                            content: json.message,
                                            type: json.type,
                                            buttons: {
                                                OK : function(){
                                                    if(json.type == "green")
                                                    {
                                                        location.reload()
                                                    }
                                                }
                                            }
                                        });
                                    }
                                })
                            }
                        }
                    }
                })
            }
        })
    })

    $(document).on("click",".sav_btn_motif_delete", function(){
        var self = $(this)
        $.confirm({
            title: "Suppression",
            content:"Vous êtes sûre de vouloir supprimer cet éléments ?",
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
                        var realIns2 = instance.loading()
                        $.ajax({
                            url: routes.sav_delete_motif,
                            type:'post',
                            cache:false,
                            data:{id:self.attr('value')},
                            dataType:'json',
                            success: function(json){
                                realIns2.close()
                                $.alert({
                                    title: 'Message',
                                    content: json.message,
                                    type: json.type,
                                    buttons: {
                                        OK : function(){
                                            if(json.type == "green")
                                            {
                                                location.reload()
                                            }
                                        }
                                    }
                                });
                            }
                        })
                    }
                }
            }
        })
    })

    $(".sav_btn_type").click(function(){
        var facture = $("#sav_facture").val()
        if(facture == "")
        {
            $.alert({
                title: "Facture vide",
                content: "Vauillez séléctionner une facture",
                type:"orange"
            })
            return false ;
        }

        var btnClass = $(this).data("class")
        var target = $(this).data("target")
        var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
        var inputValue = $(this).attr("value")
        var reference = $(this).data("reference")
        var self = $(this)
        var ligneTableau = $(".elem_sav_facture").find('tbody').find('tr')
        if(reference == "TOT")
        {
            ligneTableau.each(function(){
                var element = $(this).find('.btn_anl_check')

                if(element.hasClass('btn-outline-purple'))
                {
                    element.click()
                    element.attr('disabled','true')
                }
            })
        }
        else
        {
            ligneTableau.each(function(){
                var element = $(this).find('.btn_anl_check')

                if(element.hasClass('btn-purple'))
                {
                    element.removeAttr('disabled')
                    element.click()
                }
            })
        }

        $(target).val(inputValue) ;

        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)
        $(".sav_btn_type").each(function(){
            if (!self.is($(this))) {
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
            }
        })
    })

    $(".sav_btn_spec").click(function(){
        var btnClass = $(this).data("class")
        var target = $(this).data("target")
        var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
        var inputValue = $(this).attr("value")
        var reference = $(this).data("reference")
        var self = $(this)

        if(reference == "RMB")
        {
            $(".content_percent").show()
        }
        else
        {
            $(".content_percent").hide()
        }

        $(target).val(inputValue) ;

        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)
        $(".sav_btn_spec").each(function(){
            if (!self.is($(this))) {
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
            }
        })
    })

    $(document).on('click','.btn_anl_check',function(){
        if($(this).hasClass('btn-outline-purple'))
        {
            $(this).removeClass("btn-outline-purple")
            $(this).addClass("btn-purple")

            var value = $(this).attr("value")

            $(this).html('<span class="text-uppercase ls-1">Annulé</span>')
            $(this).parent().append('<input type="hidden" value="'+value+'" class="sav_facture_detail" name="sav_facture_detail[]">') ;
        }
        else
        {
            $(this).removeClass("btn-purple")
            $(this).addClass("btn-outline-purple")

            $(this).html('<i class="fa fa-hand-pointer"></i>')

            $(this).parent().find(".sav_facture_detail").remove()
        }
    })

    // lvr_save_bon_livraison

    $("#formAnnulation").submit(function(event){
        event.preventDefault()
        $(".sav_annule_editor").val(sav_annule_editor.getEditorText('.sav_annule_editor'))
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
                        url: routes.sav_save_fact_annulation,
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

    $("#sav_modif_facture").change(function(){
        var self = $(this) ;
        var realinstance = instance.loading()
        var formData = new FormData() 
        formData.append("idFacture",self.val())
        $.ajax({
            url: routes.fact_content_facture_modif,
            type: 'post',
            cache: false,
            data: formData,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                $("#contentSavModifFacture").html(response) ; 
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $(".sav_btn_modif_facture").click(function(){
        $("#formSavModifFacture").submit()
    })

    $(document).on("click",".sav_ligne_modif_facture",function(){
        $.alert({
            title: '-',
            content: "... ",
            type: "purple"
        });
    })

    $(document).on("submit","#formSavModifFacture",function(){
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
                        url: routes.fact_rajoute_element_activites,
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
                                            $(".chosen_select").val("")
                                            $(".chosen_select").trigger("chosen:updated");
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