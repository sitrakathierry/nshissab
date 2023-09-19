$(document).ready(function(){
    var instance = new Loading(files.loading)
    var appBase = new AppBase()

    $(".chosen_select").chosen({
        no_results_text: "Aucun resultat trouvé : "
    });

    $(document).on("change",".fact_mod_prod_designation",function(){
        var self = $(this)
        var maRoute = $(".fact_btn_modele.btn-warning").data("indice") == "PROD" ? routes.stock_get_produit_prix : routes.prest_get_service_prix ;
        var typeData = $(".fact_btn_modele.btn-warning").data("indice") == "PROD" ? 'json' : 'html' ;
        if ($(this).is("select")) {
            $("#fact_text_designation").val($(this).find("option:selected").text())
            var realinstance = instance.loading()
            var data = new FormData() ;
            data.append('idP',self.val()) ;
            $.ajax({
                url: maRoute,
                type:'post',
                cache: false,
                data:data,
                dataType: typeData ,
                processData: false,
                contentType: false,
                success: function(resp){
                    realinstance.close()
                    if($(".fact_btn_modele.btn-warning").data("indice") == "PROD")
                    {
                        if(resp.produitPrix.length > 1)
                        {
                            var optionsPrix = '<option value=""></option>' ;
                            resp.produitPrix.forEach(elem => {
                                optionsPrix += '<option value="'+elem.id+'">'+elem.prixVente+' | '+elem.indice+'</option>'
                            });
                        }
                        else
                        {
                            var optionsPrix = '<option value="'+resp.produitPrix[0].id+'">'+resp.produitPrix[0].prixVente+' | '+resp.produitPrix[0].indice+'</option>' ;
                            $("#fact_text_prix").val(resp.produitPrix[0].prixVente+' | '+resp.produitPrix[0].indice) ;
                        }
                        
                        if(resp.tva != "")
                        {
                            $("#fact_mod_prod_tva_val").attr("readonly",true)
                            $("#fact_mod_prod_tva_val").val(resp.tva)
                        }
                        else
                        {
                            $("#fact_mod_prod_tva_val").removeAttr("readonly")
                            $("#fact_mod_prod_tva_val").val("")
                        }
                        
                        $(".fact_mod_prod_prix").html(optionsPrix)
                    }
                    else
                    {
                        $(".fact_mod_prod_prix").html(resp) ;
                    }
                    $(".fact_mod_prod_prix").trigger('chosen:updated') ; 
                },
                error: function(){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
        } 
    })


    $(document).on("change",".fact_mod_prod_prix",function(){
        var selectedText = $(this).find("option:selected").text();
        $("#fact_text_prix").val(selectedText) ;
    })

    var optionsP = $(".fact_mod_prod_designation").html()
    $(document).on("change","#fact_mod_prod_type",function(){
        var valType = $(this).val() ;
        var self = $(this)
        if(valType == "autre")
        {
            var script = document.createElement('script');
            script.innerHTML = 'var autre_editor = new LineEditor("#fact_mod_prod_autre");';
            var content_autre = '<input type="text" name="fact_mod_prod_autre" class="form-control fact_mod_prod_autre" id="fact_mod_prod_autre" placeholder=". . .">'+script.outerHTML;
            
            $.alert({
                title:"Designation Autre",
                boxWidth: "500px",
                useBootstrap: false,
                content: content_autre,
                type: "dark",
                theme:"modern",
                buttons: {
                    Annuler: function(){
                        $("#fact_mod_prod_type").val("PrdVariationPrix")
                        $("#fact_mod_prod_type").trigger("chosen:updated"); 
                    },
                    OK: function(){
                        if(autre_editor.getEditorText() == ""){
                            $.alert({
                                title: "Designation vide",
                                content: "Veuiller remplir le champ ",
                                type: "orange",
                            });
                            return false; 
                        }

                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'fact_mod_prod_designation';
                        input.className = 'form-control fact_mod_prod_designation';
                        input.id = 'fact_mod_prod_designation';
                        input.placeholder = '. . .';
                        input.value = autre_editor.getEditorText()

                        $(".content_fact_designation").html(autre_editor.getEditorText()+input.outerHTML)
                        $(".content_fact_prix").html('<input type="number" name="fact_mod_prod_prix" class="form-control fact_mod_prod_prix" id="fact_mod_prod_prix" placeholder=". . .">')
                        $("#fact_text_designation").val(autre_editor.getEditorText())
                    }

                }
            })
        }
        else
        {
            $(".content_fact_designation").html('<select class="custom-select custom-select-sm chosen_select fact_mod_prod_designation" name="fact_mod_prod_designation" id="fact_mod_prod_designation" >'+optionsP+'</select>')
            $(".content_fact_prix").html('<select class="custom-select custom-select-sm chosen_select fact_mod_prod_prix" name="fact_mod_prod_prix" id="fact_mod_prod_prix" ><option value=""></option></select>')
            $(".chosen_select").chosen({
                no_results_text: "Aucun resultat trouvé : "
            }); 
        }
    })

    
    $(document).on("click",".ajout_fact_element",function(){
        var fact_mod_prod_designation = $("#fact_mod_prod_designation").val()
        var fact_mod_prod_prix = $("#fact_mod_prod_prix").val()
        var fact_mod_prod_qte = $("#fact_mod_prod_qte").val()
        var fact_mod_prod_type = $("#fact_mod_prod_type").val()
        
        var result = appBase.verificationElement([
            fact_mod_prod_designation,
            fact_mod_prod_prix,
            fact_mod_prod_qte,
        ],[
            "Désignation",
            "Prix",
            "Quantité"
        ])

        if(!result["allow"])
        {
            $.alert({
                title: 'Message',
                content: result["message"],
                type: result["type"],
            });

            return result["allow"] ;
        }


        // if(fact_mod_prod_type == "autre")
        // {
        //     var fact_mod_prod_prix = $("#fact_mod_prod_prix").val() ;
        //     var result = appBase.verificationElement([fact_mod_prod_prix],["Prix"])
        //     if(!result["allow"])
        //     {
        //         $.alert({
        //             title: 'Message',
        //             content: result["message"],
        //             type: result["type"],
        //         });
    
        //         return result["allow"] ;
        //     }
        // }

        var fact_text_type = fact_mod_prod_type == "autre" ? "Autre" : fact_mod_prod_type
        var fact_text_designation = $("#fact_text_designation").val()
        var fact_text_prix = fact_mod_prod_type == "autre" ? $("#fact_mod_prod_prix").val() : parseFloat($("#fact_text_prix").val().split(" | ")[0])
        
        var fact_mod_prod_designation = fact_mod_prod_type == "autre" ? fact_text_designation : $("#fact_mod_prod_designation").val()
        var fact_mod_prod_prix =  $("#fact_mod_prod_prix").val()
        var fact_mod_prod_qte = $("#fact_mod_prod_qte").val()
        var fact_mod_prod_tva_val = $("#fact_mod_prod_tva_val").val()
        fact_mod_prod_tva_val = fact_mod_prod_tva_val == "" ? 0 : fact_mod_prod_tva_val
        
        if(fact_mod_prod_type != "autre")
        {
            if($(".fact_btn_modele.btn-warning").data("indice") == "PROD")
            {
                var stock = parseInt(fact_text_designation.split(" | ")[2].split(" : ")[1])
                if(stock < parseInt(fact_mod_prod_qte))
                {
                    $.alert({
                        title: "Stock insuffisant",
                        content: "Veuiller entrer une quantité inférieure au stock",
                        type:'red',
                    })
                    return false;
                }
    
                fact_text_designation = fact_text_designation.split(" | ")[0]+" | "+fact_text_designation.split(" | ")[1] ;
            }
            else
            {
                fact_text_designation + $("#fact_text_designation").val()
            }

        }

        fact_text_prix = parseFloat(fact_text_prix) ;
        fact_mod_prod_qte = parseFloat(fact_mod_prod_qte) ;
        fact_mod_prod_tva_val = parseFloat(fact_mod_prod_tva_val) ;

        var fact_mod_prod_type_remise = $("#fact_mod_prod_type_remise").val()
        var selectedTypeRemise = $("#fact_mod_prod_type_remise").find("option:selected")
        var fact_text_type_remise = selectedTypeRemise.text() ; 
        var fact_mod_prod_remise = $("#fact_mod_prod_remise").val()
        var fact_valeur_tva = ((fact_text_prix * fact_mod_prod_tva_val) / 100) * fact_mod_prod_qte
        var fact_total_partiel = fact_text_prix * fact_mod_prod_qte ;

        if(fact_mod_prod_type_remise != "")
        {
            var result = appBase.verificationElement([
                fact_mod_prod_remise,
            ],[
                "Remise",
            ])
    
            if(!result["allow"])
            {
                $.alert({
                    title: 'Message',
                    content: result["message"],
                    type: result["type"],
                });
    
                return result["allow"] ;
            }

            fact_mod_prod_remise = parseFloat(fact_mod_prod_remise) ;
            var remiseCalcul = selectedTypeRemise.data("calcul")
            var remise = 0 ;
            if(remiseCalcul == 100)
            {
                remise = ((fact_text_prix * fact_mod_prod_remise) / 100 ) * fact_mod_prod_qte
                fact_total_partiel = fact_total_partiel - remise 
            }
            else
            {
                remise = fact_mod_prod_remise
                fact_total_partiel = fact_total_partiel - remise
            }
           
        }
        else
        {
            if(fact_mod_prod_remise != "")
            {
                $.alert({
                    title: 'Type remise vide',
                    content: "Sélectionner un type remise",
                    type:'orange',
                })
                return false;
            }
        }
        var addElement = "" ;
        var colspanElement = "" ;
        if($("#fact_signal_modif").val() == "MODIF_PRODUIT")
        {
            addElement = '<td>-</td>' ;
            colspanElement = 'colspan="2"' ;
        }

        var item = `
            <tr>
                <td>
                    `+fact_text_type.toUpperCase()+`
                    <input type="hidden" value="`+fact_mod_prod_type+`" name="fact_enr_prod_type[]" class="fact_enr_prod_type">  
                </td>
                <td>
                    `+fact_text_designation+`
                    <input type="hidden" value="`+fact_text_designation+`" name="fact_enr_prod_designation[]" class="fact_enr_prod_designation"> 
                </td>
                <td>
                    `+fact_mod_prod_qte+`
                    <input type="hidden" value="`+fact_mod_prod_qte+`" name="fact_enr_prod_quantite[]" class="fact_enr_prod_quantite"> 
                </td>
                `+addElement+`
                <td>
                    `+fact_text_prix+`
                    <input type="hidden" value="`+fact_mod_prod_prix+`" name="fact_enr_prod_prix[]" class="fact_enr_prod_prix"> 
                    <input type="hidden" value="`+fact_text_prix+`" name="fact_enr_text_prix[]" class="fact_enr_text_prix"> 
                </td>
                <td>
                    `+(fact_valeur_tva == 0 ? "" : fact_valeur_tva)+` `+ (fact_mod_prod_tva_val == "" ? "" : "("+fact_mod_prod_tva_val+"%)") +` 
                    <input type="hidden" value="`+fact_mod_prod_tva_val+`" name="fact_enr_prod_tva_val[]" class="fact_enr_prod_tva_val"> 
                </td>
                <td>
                    `+fact_text_type_remise+`
                    <input type="hidden" value="`+fact_mod_prod_type_remise+`" name="fact_enr_prod_remise_type[]" class="fact_enr_prod_remise_type"> 
                </td>
                <td>
                    `+fact_mod_prod_remise+`
                    <input type="hidden" value="`+fact_mod_prod_remise+`" name="fact_enr_prod_remise[]" class="fact_enr_prod_remise"> 
                </td>
                <td>
                    `+fact_total_partiel+`
                    <input type="hidden" value="`+fact_total_partiel+`" class="fact_enr_total_ligne"> 
                </td>
                `+addElement+`
                <td class="text-center"><button type="button" class="btn fact_supprimer_ligne btn-outline-danger btn-sm font-smaller"><i class="fa fa-times"></i></button></td>
            </tr>
        `

        $(".elem_facture_produit").append(item)

        calculFacture()

        var emptyArray = [
            "#fact_mod_prod_designation",
            "#fact_mod_prod_prix",
            "#fact_mod_prod_qte",
            "#fact_mod_prod_type_remise",
            "#fact_mod_prod_remise",
            "#fact_mod_prod_tva_val"
        ]

        emptyArray.forEach(elem => {
            $(elem).val("")
            $(elem).trigger("chosen:updated");
        })

    })

    function calculFacture()
    {
        var totalHT = 0
        var totalTva = 0
        var totalTTC = 0
        var totalApresDeduction = 0
        
        var remiseType = $("#fact_type_remise_prod_general").val()
        var selectedTypeRemise = $("#fact_type_remise_prod_general").find("option:selected")
        var remiseVal = $("#fact_remise_prod_general").val() == "" ? 0 : parseFloat($("#fact_remise_prod_general").val())
        
        $(".elem_facture_produit tr").each(function(){
            var quantiteLigne = $(this).find(".fact_enr_prod_quantite").val() ;
            var prixLigne = $(this).find(".fact_enr_text_prix").val() ;
            var tvaLigne = $(this).find(".fact_enr_prod_tva_val").val() ; 
            var totalLigne = $(this).find(".fact_enr_total_ligne").val() ;

            totalHT += parseFloat(totalLigne) ;
            totalTva += (((parseFloat(tvaLigne) * parseFloat(prixLigne)) / 100) * parseFloat(quantiteLigne)) ;
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

    $(document).on('click',".fact_supprimer_ligne",function(){
        $(this).closest('tr').remove()
        calculFacture()
    })

    $(document).on("keyup","#fact_remise_prod_general",function(){
        calculFacture()
    })

    $(document).on("change","#fact_devise",function(){
            var option = $(this).find("option:selected") ;
            var montantbase = option.attr("base") ;
            var totalBase = $(".fact_enr_total_general").val()
            var selectedText = option.text() ;  

            $("#fact_lettre_devise").text(selectedText.split(" | ")[1])
            var montantDevise = parseFloat(totalBase) / parseFloat(montantbase)
            montantDevise = montantDevise.toFixed(2)
            montantDevise = montantDevise.endsWith('.00') ? montantDevise.slice(0, -3) : montantDevise ;
            $("#fact_montant_devise").text(montantDevise+" "+selectedText.split(" | ")[0])
            if($(this).val() == "")
            {
                $(".fact_enr_val_devise").val("")
                $(".fact_disp_devise").addClass("d-none")
            }
            else
            {
                $(".fact_enr_val_devise").val($(this).val())
                $(".fact_disp_devise").removeClass("d-none")
            }
    })

    $(document).on("change","#fact_type_remise_prod_general",function(){
        calculFacture()
    })
})