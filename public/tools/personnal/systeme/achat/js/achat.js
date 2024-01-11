$(document).ready(function(){
    var instance = new Loading(files.loading) ;
    var appBase = new AppBase() ;
    var compta_achat_editor = new LineEditor("#compta_achat_editor") ;
    var ach_commande_editor = new LineEditor("#ach_commande_editor") ;
    ach_commande_editor.setEditorText($("#ach_commande_editor").val())
    
    $("#ach_date").datepicker() ; 
    $("#ach_commande_credit_date").datepicker() ; 

    $(document).on('click',"#achat_bon_new_marchandise",function(){
        if(!$(this).attr("disabled"))
        {
            var realinstance = instance.loading()
            var self = $(this)
            $.ajax({
                url: routes.achat_new_marchandise,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#contentMarchandise").empty().html(response)
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
        }
        $(this).prop("disabled", true);
        // $(this).closest('tr').remove() ;
    })

    $(document).on('click',"#achat_bon_existing_marchandise",function(){
        if(!$(this).attr("disabled"))
        {
            var realinstance = instance.loading()
            var self = $(this)
            $.ajax({
                url: routes.achat_existing_marchandise,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#contentMarchandise").empty().html(response)
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
        }
        $(this).prop("disabled", true);
        // $(this).closest('tr').remove() ;
    })

    $(document).on('change',"#achat_bon_designation", function(){
        if(!$(this).is("select"))
            return false ;
        // var realinstance = instance.loading()
        // var self = $(this)
        // $.ajax({
        //     url: routes.achat_marchandise_prix_get,
        //     type:'post',
        //     cache: false,
        //     data:{id:self.val()},
        //     dataType: 'json',
        //     success: function(json){
        //         realinstance.close();
        //         if (json.type == "green") {
        //             $("#achat_bon_prix").val(json.prix) ;
        //         }
        //         else
        //         {
        //             $("#achat_bon_prix").val("") ;
        //         }
        //     },
        //     error: function(resp){
        //         realinstance.close()
        //         $.alert(JSON.stringify(resp)) ;
        //     }
        // })
    })

    function calculMontantMarchandise()
    {
        var totalGeneral = 0
        
        $("#contentItemMarchandise tr").each(function(){
            // var quantiteLigne = $(this).find("#achat_bon_enr_quantite").val()
            // var prixLigne = $(this).find("#achat_bon_enr_prix").val()
            // var totalLigne = parseFloat(quantiteLigne) * parseFloat(prixLigne)
            var totalLigne = parseFloat($(this).find("#achat_bon_enr_total_ligne").val())
            totalGeneral += totalLigne ;
        })

        $("#achat_bon_total_Gen").text(totalGeneral)
        $("#achat_bon_val_total_Gen").val(totalGeneral)
    }

    $(document).on('click',"#ajout_marchandise", function(){

        var designation = $("#achat_bon_designation").val()
        if($("#new_marchandise").val() == "NON")
        {
            if($("#achat_bon_designation").val() != "")
            {
                var optionSelected = $("#achat_bon_designation").find("option:selected")
                designation = optionSelected.text()
            }
            else
            {
                designation = "" ;
            }
        }
        var quantite = $("#achat_bon_quantite").val()
        var prix = $("#achat_bon_prix").val()
        var reference = $("#achat_bon_reference").val()

        var result = appBase.verificationElement([
            designation,
            quantite,
            prix,
        ],[
            "Désignation",
            "Quantité",
            "Prix",
        ]) ;

        

        if(!result["allow"])
        {
            $.alert({
                title: 'Message',
                content: result["message"],
                type: result["type"],
            });

            return result["allow"] ;
        }
        
        var item = '' ;
        var total = 0 ;
        if($("#new_marchandise").val() == "NON")
        {
            total = parseFloat(quantite) * parseFloat(prix) ;
            item = `
                    <tr>
                        <td>
                            `+designation+`
                            <input type="hidden" name="achat_bon_enr_designation[]" id="achat_bon_enr_designation" value="`+designation+`">
                            <input type="hidden" name="achat_bon_enr_design_id[]" id="achat_bon_enr_design_id" value="`+$("#achat_bon_designation").val()+`">
                        </td>
                        <td>
                            `+reference+`
                            <input type="hidden" name="achat_bon_enr_reference[]" id="achat_bon_enr_reference" value="`+reference+`">
                        </td>
                        <td>
                            `+quantite+`
                            <input type="hidden" name="achat_bon_enr_quantite[]" id="achat_bon_enr_quantite" value="`+quantite+`">
                        </td>
                        <td>
                            `+prix+`
                            <input type="hidden" name="achat_bon_enr_prix[]" id="achat_bon_enr_prix" value="`+prix+`">
                        </td>
                        <td>
                        `+total+`
                        <input type="hidden" id="achat_bon_enr_total_ligne" value="`+total+`">
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm bon_supprimer_ligne btn-outline-danger font-smaller"><i class="fa fa-times"></i></button>
                        </td>
                    </tr>
                `
            $("#contentItemMarchandise").append(item) ;
            $("#achat_bon_designation").val("")
            $(".chosen_select").trigger("chosen:updated")
            $("#achat_bon_quantite").val("")
            $("#achat_bon_prix").val("")
            $("#achat_bon_reference").val("")

            calculMontantMarchandise()

            return false ;
        }

        var realinstance = instance.loading()
        var self = $(this)
        $.ajax({
            url: routes.achat_marchandise_creation,
            type:'post',
            cache: false,
            data:{designation:designation},
            dataType: 'json',
            success: function(json){
                realinstance.close();
                total = parseFloat(quantite) * parseFloat(prix) ;
                item = `
                    <tr>
                        <td>
                            `+designation+`
                            <input type="hidden" name="achat_bon_enr_designation[]" id="achat_bon_enr_designation" value="`+designation+`">
                            <input type="hidden" name="achat_bon_enr_design_id[]" id="achat_bon_enr_design_id" value="`+json.id+`">
                        </td>
                        <td>
                            `+reference+`
                            <input type="hidden" name="achat_bon_enr_reference[]" id="achat_bon_enr_reference" value="`+reference+`">
                        </td>
                        <td>
                            `+quantite+`
                            <input type="hidden" name="achat_bon_enr_quantite[]" id="achat_bon_enr_quantite" value="`+quantite+`">
                        </td>
                        <td>
                            `+prix+`
                            <input type="hidden" name="achat_bon_enr_prix[]" id="achat_bon_enr_prix" value="`+prix+`">
                        </td>
                        <td>`+total+`</td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm bon_supprimer_ligne btn-outline-danger font-smaller"><i class="fa fa-times"></i></button>
                        </td>
                    </tr>
                `

                $("#contentItemMarchandise").append(item) ;
                $("#achat_bon_designation").val("")
                $("#achat_bon_quantite").val("")
                $("#achat_bon_prix").val("")
                $("#achat_bon_reference").val("")

                calculMontantMarchandise()
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $("#formbonCommande").submit(function(){
        var self = $(this)
        $("#compta_achat_editor").val(compta_achat_editor.getEditorText('#compta_achat_editor'))
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
                            url: routes.achat_bon_commande_save,
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

    $("#ach_commande_credit_date").val("")
    $("#ach_commande_credit_montant").val("")

    $("#formCreditAchat").submit(function(){
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
                            url: routes.achat_paiement_credit_save,
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

    $("#ach_mrch_designation").val("")
    $("#ach_mrch_prix").val("")

    $("#formMarchandise").submit(function(){
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
                            url: routes.achat_marchandise_creation,
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

    $(document).on('click',".ach_edit_marchandise",function(){
        var designation = $(this).closest("tr").find(".elem_designation").text() ;
        // var prix = $(this).closest("tr").find(".elem_prix").text() ;
        var idM = $(this).attr("value") ;

        var element = `
            <div class="w-100 text-left">
                <label for="mrch_modif_designation" class="font-weight-bold">Désignation</label>
                <input type="text" name="designation" id="mrch_modif_designation" oninput="this.value = this.value.toUpperCase();" value="`+designation+`" class="form-control" placeholder=". . .">
            </div>
            `
        $.confirm({
            title: "Modification",
            content:element,
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
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.achat_marchandise_creation,
                            type:'post',
                            cache: false,
                            data: {
                                designation: $("#mrch_modif_designation").val(),
                                // prix: $("#mrch_modif_prix").val(),
                                idM:idM,
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
    })

    $(document).on('click',".ach_delete_marchandise", function(){
        var self = $(this)
        $.confirm({
            title: "Suppression",
            content:"Vous êtes sûre ?",
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
                            url: routes.achat_marchandise_supprime,
                            type:'post',
                            cache: false,
                            data:{idM:self.attr('value')},
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

    $(document).on('click',".ach_check_paiement",function(){
        var self = $(this)
        $.confirm({
            title: "Validation Paiement",
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
                        $.ajax({
                            url: routes.achat_validation_total_save,
                            type:'post',
                            cache: false,
                            data:{
                                idBon:self.data("value"),
                                statutBon:"PAYE",
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

    $(document).on('click',".ach_check_livraison",function(){
        var self = $(this)
        $.confirm({
            title: "Validation Livraison",
            content:"Vous êtes sûre ?",
            type:"green",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-green',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.achat_validation_total_save,
                            type:'post',
                            cache: false,
                            data:{
                                idBon:self.data("value"),
                                statutBon:"LIVRE",
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

    $(document).on('click',".ach_check_credit_livraison",function(){
        var idData = $(this).data("value") ;
        var self = $(this).parent()
        $(this).parent().empty().html(instance.otherSearch())

        var formData = new FormData() ;
        formData.append("idData",idData) ;
        $.ajax({
            url: routes.achat_validation_credit_save,
            type:'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false,
            contentType:false,
            success: function(response){
                self.empty().html(response)
            },
            error: function(resp){
                self.empty().html("")
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $(document).on("click",".bon_supprimer_ligne",function(){
        $(this).closest("tr").remove()
        calculMontantMarchandise() ;
    })

    $(".btn_update_bon_commande").click(function(){
        $("#formAddCommande").submit()
    })

    $("#formAddCommande").submit(function(){
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
                            url: routes.,
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
    
    $(document).on('click',".btn_supprime_detail_bon", function(){
        var self = $(this)
        $.confirm({
            title: "Suppression",
            content:"Vous êtes sûre ?",
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
                            url: routes.achat_bon_marchandise_item_supprime,
                            type:'post',
                            cache: false,
                            data:{idDetailBon:self.data('value')},
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

    $(document).on("click",".btn_modifie_detail_bon",function(){
        var realinstance = instance.loading()
        var self = $(this)
        var formData = new FormData() ;
        formData.append('idDetailBon',self.data("value"))
        $.ajax({
            url: routes.achat_get_modif_detail_bon,
            type:'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                $("#contentDetailBonCommande").html(response)
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $(document).on("submit","#detailBonCommande",function(){
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
                        var data = self.serialize() ;
                        $.ajax({
                            url: routes.achat_update_detail_bon,
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

    $(".btn_imprimer_bon_commande").click(function(){
        var self = $(this)
        var realinstance = instance.loading()
        $.ajax({
            url: routes.param_modele_pdf_get,
            type:"post",
            dataType:"html",
            processData:false,
            contentType:false,
            success : function(response){
                realinstance.close()
                $.confirm({
                    title: "Impression Facture",
                    content:response,
                    type:"blue",
                    theme:"modern",
                    buttons:{
                        btn1:{
                            text: 'Annuler',
                            action: function(){}
                        },
                        btn2:{
                            text: 'Imprimer',
                            btnClass: 'btn-blue',
                            keys: ['enter', 'shift'],
                            action: function(){
                                var idModeleEntete = $("#modele_pdf_entete").val() ;
                                var idModeleBas = $("#modele_pdf_bas").val() ;
                                var realinstance = instance.loading()
                                $.ajax({
                                    url: routes.achat_information_update,
                                    type:'post',
                                    cache: false,
                                    data:{
                                        idAchat:self.data("value"),
                                        ach_commande_editor:ach_commande_editor.getEditorText(),
                                        ach_lieu:$("#ach_lieu").val(),
                                        ach_date:$("#ach_date").val()
                                    },
                                    dataType: 'json',
                                    success: function(response){
                                        realinstance.close()
                                        var idAchat = self.data("value") ;
                                        var url = routes.achat_detail_imprimer + '/' + idAchat + '/' + idModeleEntete + '/' + idModeleBas;
                                        window.open(url, '_blank');
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
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
        return false ;
    })
})