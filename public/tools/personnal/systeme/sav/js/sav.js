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

    $(document).on("click",".btn_save_annulation",function(){
        var sav_facture_detail = $(".sav_facture_detail").val()

        if(sav_facture_detail == undefined)
        {
            var self = $(this)
            $.confirm({
                title: "Detail vide",
                content:"Le détail vide équivaut à une annulation totale. Voulez-vous continuer ?",
                type:"dark",
                theme:"modern",
                buttons:{
                    btn1:{
                        text: 'Non',
                        btnClass: 'btn-red',
                        keys: ['enter'],
                        action: function(){
                            $("#sav_allow_element_vide").val("NON")
                        }
                    },
                    btn2:{
                        text: 'Oui',
                        btnClass: 'btn-green',
                        keys: ['enter'],
                        action: function(){
                            $("#sav_allow_element_vide").val("OUI")
                            $("#formAnnulation").submit() ;
                        }
                    }
                }
            })
            return false ;
        }
        else
        {
            $("#formAnnulation").submit() ;
        }
    })

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
                    text: 'Annuler',
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
                            // idAnnule
                            $.alert({
                                title: 'Message',
                                content: json.message,
                                type: json.type,
                                buttons: {
                                    OK: function(){
                                        if(json.type == "green")
                                        {
                                            if(!json.isProd)
                                                return false ;

                                            var idAnnulation = json.idAnnule ;
                                            $.confirm({
                                                title: "Retourner ou Déduction",
                                                content:"Voulez-vous retourner le produit sur le stock ou déduire le produit du stock ?",
                                                type:"red",
                                                buttons:{
                                                    btn0:{
                                                        text: 'Annuler',
                                                        action: function(){
                                                            location.reload() ;
                                                        }
                                                    },
                                                    btn1:{
                                                        text: 'Retourner',
                                                        btnClass: 'btn-green',
                                                        keys: ['enter'],
                                                        action: function(){
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
                                                                        keys: ['enter'],
                                                                        action: function(){
                                                                            var realinstance = instance.loading()
                                                                            $.ajax({
                                                                                url: routes.sav_update_fact_annulation,
                                                                                type:'post',
                                                                                cache: false,
                                                                                data:{
                                                                                    typeAction:"RETOUR",
                                                                                    idAnnulation:idAnnulation,
                                                                                    reduc_val_cause:"-",
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
                                                    },
                                                    btn2:{
                                                        text: 'Déduire',
                                                        btnClass: 'btn-red',
                                                        action: function(){
                                                            $.confirm({
                                                                title: 'A Remplir',
                                                                content: `
                                                                    <label for="reduc_val_cause" class="mt-2 font-weight-bold">Cause</label>
                                                                    <input type="text" name="reduc_val_cause" id="reduc_val_cause" class="form-control" placeholder=". . .">
                                                                `,
                                                                type: "black",
                                                                buttons: {
                                                                    Annuler: function(){
                                                                        if(json.type == "green")
                                                                        {
                                                                            location.reload()
                                                                        }
                                                                    },
                                                                    Valider: function()
                                                                    {
                                                                        var reduc_val_cause = $("#reduc_val_cause").val()
                                                                        if(reduc_val_cause == "")
                                                                        {
                                                                            $.alert({
                                                                                title: 'Message',
                                                                                content: "La cause obligatoire, veuiller remplir s'il vous plait",
                                                                                type: "red",
                                                                            });

                                                                            return false ;
                                                                        }
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
                                                                                    keys: ['enter'],
                                                                                    action: function(){
                                                                                        var realinstance = instance.loading()
                                                                                        $.ajax({
                                                                                            url: routes.sav_update_fact_annulation,
                                                                                            type:'post',
                                                                                            cache: false,
                                                                                            data:{
                                                                                                typeAction:"DEDUIRE",
                                                                                                idAnnulation:idAnnulation,
                                                                                                reduc_val_cause:reduc_val_cause,
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
                                                                }
                                                            });
                                                            
                                                        }
                                                    }
                                                }
                                            })
                                            return false ;
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

    function calculFacture()
    {
        var totalHT = 0
        var totalTva = 0
        var totalTTC = 0
        var totalApresDeduction = 0
        
        var remiseType = $("#fact_type_remise_prod_general").val()
        var selectedTypeRemise = $("#fact_type_remise_prod_general").find("option:selected")
        var remiseVal = $("#fact_remise_prod_general").val() == "" ? 0 : Number($("#fact_remise_prod_general").val()) ;

        $(".elem_facture_produit tr").each(function(){
            var quantiteLigne = $(this).find(".fact_enr_prod_quantite").val() ;
            var prixLigne = $(this).find(".fact_enr_text_prix").val() ;
            var tvaLigne = $(this).find(".fact_enr_prod_tva_val").val() ; 
            var totalLigne = $(this).find(".fact_enr_total_ligne").val() ;

            totalHT += Number(totalLigne) ;
            var valTva = ((Number(tvaLigne) * Number(prixLigne)) / 100) * Number(quantiteLigne)
            totalTva = totalTva + valTva ;
        })

        var remise = 0 ;

        if(selectedTypeRemise.data("calcul") != "")
            remise = selectedTypeRemise.data("calcul") == 1 ? remiseVal : (totalHT * remiseVal) / 100 

        totalTTC = (totalHT + totalTva) - remise ;
        totalApresDeduction = totalHT - remise ; 
        
        var lettreTotal = NumberToLetter(totalTTC)

        $("#fact_total_fixe").text(totalHT)

        $("#fact_total_apres_deduction").text(totalApresDeduction)

        $("#fact_total_tva").text(totalTva)
        $(".fact_enr_total_tva").val(totalTva)
        
        $("#agd_total_facture").text(totalTTC)  

        $("#agd_total_restant").text(totalTTC)
        $("#agd_val_total_restant").val(totalTTC)

        $("#fact_total_general").text(totalTTC)
        $(".fact_enr_total_general").val(totalTTC)

        $("#fact_somme_lettre").text(lettreTotal) ;
    }

    $(document).on("click",".sav_ligne_modif_facture",function(){
        var fact_detail_modele = $("#fact_detail_modele").val()
        var designation = ""
        if(fact_detail_modele == "PROD" || fact_detail_modele == "PSTD")
        {
            designation =  $(this).closest("tr").find(".fact_enr_prod_designation").val()
        }
        else if(fact_detail_modele == "PBAT")
        {
            designation =  $(this).closest("tr").find("#fact_enr_btp_designation").val()
        }
        var fact_enr_ligne_spec_quantite = $(this).closest("tr").find("#fact_enr_ligne_spec_quantite").val()
        var fact_percent_tva_ligne = $(this).closest("tr").find("#fact_percent_tva_ligne").val()
        
        var self = $(this)
        $.confirm({
            title: "Modification Facture",
            content:`
            <div class="w-100">
                <label for="sav_elem_design" class="mt-2 font-weight-bold">Désignation</label>
                <input type="text" name="sav_elem_design" value="`+designation+`" readonly id="sav_elem_design" class="form-control text-primary font-weight-bold">
            
                <label for="sav_elem_quantite" class="mt-2 font-weight-bold">Quantité</label>
                <input type="number" step="any" value="`+fact_enr_ligne_spec_quantite+`"  name="sav_elem_quantite" id="sav_elem_quantite" class="form-control">
            
                <label for="sav_elem_tva" class="mt-2 font-weight-bold">TVA</label>
                <input type="number" step="any" value="`+fact_percent_tva_ligne+`"  name="sav_elem_tva" id="sav_elem_tva" class="form-control">
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
                    text: 'Valider',
                    btnClass: 'btn-orange',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.fact_valid_modif_facture,
                            type:'post',
                            cache: false,
                            data:{
                                idFacture:self.data("value"),
                                sav_elem_quantite:$("#sav_elem_quantite").val(),
                                sav_elem_tva:$("#sav_elem_tva").val()
                            },
                            dataType: 'json',
                            success: function(json){
                                realinstance.close()
                                self.closest("tr").find(".sav_modif_quantite").text(json.valQte)
                                self.closest("tr").find(".sav_modif_tva").text(json.valTva)
                                self.closest("tr").find(".sav_modif_mtn_total").text(json.valMtnTotal)
                                self.closest("tr").find(".fact_enr_prod_quantite").val(json.valQte)
                                self.closest("tr").find(".fact_enr_prod_tva_val").val(json.percentTva)
                                self.closest("tr").find(".fact_enr_total_ligne").val(json.valMtnTotal)
                                self.closest("tr").find("#fact_enr_ligne_spec_quantite").val(json.valQte)
                                self.closest("tr").find("#fact_percent_tva_ligne").val(json.percentTva)
                                // $.alert({
                                //     title: 'Message',
                                //     content: json.message,
                                //     type: json.type,
                                //     // buttons: {
                                //     //     OK: function(){
                                //     //         if(json.type == "green")
                                //     //         {
                                //     //             location.reload()
                                //     //         }
                                //     //     }
                                //     // }
                                // });
                                calculFacture()
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
    
    var facture_editor = new LineEditor(".facture_editor") ;

    $(document).on("submit","#formSavModifFacture",function(){
        var self = $(this)
        $(".facture_editor").val(facture_editor.getEditorText('.facture_editor'))
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