$(document).ready(function(){
    var instance = new Loading(files.loading)
    
    $(".chosen_select").chosen({
        no_results_text: "Aucun resultat trouvé : "
    });

    function affichePrixProduit()
    {
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
    }
    affichePrixProduit()

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
            affichePrixProduit()
            $(".chosen_select").chosen({
                no_results_text: "Aucun resultat trouvé : "
            }); 
        }
    })

    var totalFixe = 0
    var totalTva = 0
    var totalPartiel = 0
    $(document).on("click",".ajout_fact_element",function(){
        var elemArray = [
            "#fact_mod_prod_designation",
            "#fact_mod_prod_prix",
            "#fact_mod_prod_qte"
        ]

        var elemCaption = [
            "Désignation",
            "Prix",
            "Quantité"
        ]

        var vide = false ;
        var caption = '' ;
        var n = 0 ;

        elemArray.forEach(elem => {
            if($(elem).val() == "")
            {
                vide = true ;
                caption = elemCaption[n] ;
                return ;
            }
            n++ ;
        })

        if(vide)
        {
            $.alert({
                title: 'Champ vide',
                content: caption+" est vide",
                type:'orange',
            })
            return false;
        }

        if(parseFloat($("#fact_mod_prod_qte").val()) < 0)
        {
            $.alert({
                title: 'Valeur négatif',
                content: "Quantité doit être positif ",
                type:'red',
            })
            return false;
        }
        else if(parseFloat($("#fact_mod_prod_qte").val()) == 0)
        {
            $.alert({
                title: 'Valeur nul',
                content: "Quantité doit être valide",
                type:'orange',
            })
            return false;
        }

        

        var fact_mod_prod_type = $("#fact_mod_prod_type").val()

        if(fact_mod_prod_type == "autre")
        {
            if(parseFloat($("#fact_mod_prod_prix").val()) < 0)
            {
                $.alert({
                    title: 'Valeur négatif',
                    content: "Prix doit être positif",
                    type:'red',
                })
                return false;
            }
            else if(parseFloat($("#fact_mod_prod_prix").val()) == 0)
            {
                $.alert({
                    title: 'Valeur nul',
                    content: "Prix doit être valide",
                    type:'orange',
                })
                return false;
            }
        }

        var fact_text_type = fact_mod_prod_type == "autre" ? "Autre" : "Produit"
        var fact_text_designation = $("#fact_text_designation").val()
        var fact_text_prix = fact_mod_prod_type == "autre" ? $("#fact_mod_prod_prix").val() : parseFloat($("#fact_text_prix").val().split(" | ")[0])
        
        var fact_mod_prod_designation = fact_mod_prod_type == "autre" ? fact_text_designation : $("#fact_mod_prod_designation").val()
        var fact_mod_prod_prix =  $("#fact_mod_prod_prix").val()
        var fact_mod_prod_qte = $("#fact_mod_prod_qte").val()
        var fact_mod_prod_tva_val = $("#fact_mod_prod_tva_val").val()

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

        var fact_mod_prod_type_remise = $("#fact_mod_prod_type_remise").val()
        var fact_text_type_remise = fact_mod_prod_type_remise == 1 ? "%" : (fact_mod_prod_type_remise == 2 ? "Montant" : "") ;
        var fact_mod_prod_remise = $("#fact_mod_prod_remise").val()
        var fact_valeur_tva = ((fact_text_prix * fact_mod_prod_tva_val) / 100) * fact_mod_prod_qte
        var fact_total_partiel = fact_text_prix * fact_mod_prod_qte ;

        if(fact_text_type_remise != "")
        {
            if(fact_mod_prod_remise == "")
            {
                $.alert({
                    title: 'Champ vide',
                    content: "Remise est vide",
                    type:'orange',
                })
                return false;
            }
            else
            {
                if(parseFloat(fact_mod_prod_remise) < 0)
                {
                    $.alert({
                        title: 'Valeur négatif',
                        content: "Remise doit être positif ",
                        type:'red',
                    })
                    return false;
                }
                else
                {
                    if(fact_mod_prod_type_remise == 1)
                    {
                        var remise = fact_mod_prod_remise != "" ? (fact_total_partiel * fact_mod_prod_remise) / 100 : 0
                        fact_total_partiel = fact_total_partiel - remise 
                    }
                    else
                    {
                        var remise = fact_mod_prod_remise != "" ? fact_mod_prod_remise : 0
                        fact_total_partiel = fact_total_partiel - remise
                    }
                }
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
        var fact_total_ttc = fact_valeur_tva + fact_total_partiel ; 

        totalPartiel += fact_total_partiel
        totalFixe += fact_total_ttc
        totalTva += fact_valeur_tva
        var item = `
            <tr>
                <td>
                    `+fact_text_type+`
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
                </td>
                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm font-smaller"><i class="fa fa-times"></i></button></td>
            </tr>
        `

        $(".elem_facture_produit").append(item)
        $("#fact_total_fixe").text(totalPartiel)
        $("#fact_total_apres_deduction").text(totalPartiel)
        $("#fact_total_tva").text(totalTva)
        $("#fact_total_general").text(totalFixe)
        $("#agd_total_facture").text(totalFixe)  
        $("#agd_total_restant").text(totalFixe)
        $("#agd_val_total_restant").val(totalFixe)
        $(".fact_enr_total_general").val(totalFixe)
        $(".fact_enr_total_tva").val(totalTva)
        // $("#fact_remise_prod_general").keyup()

        var lettreTotal = NumberToLetter(totalFixe)
        $("#fact_somme_lettre").text(lettreTotal) ;

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

    $(document).on("keyup","#fact_remise_prod_general",function(){
            var currentVal = parseFloat($("#fact_total_fixe").text())
            var typeRemise = $("#fact_type_remise_prod_general").val()
            if(typeRemise == "")
            {
                $.alert({
                    title: "Type remise vide",
                    content: "Veuiller séléctionner type remise",
                    type:'orange',
                })
            }
            else
            {
                var newVal = 0
                if(typeRemise == 1)
                {
                    var remise = $(this).val() != "" ? (currentVal * $(this).val()) / 100 : 0
                    newVal = currentVal - remise 
                }
                else
                {
                    var remise = $(this).val() != "" ? $(this).val() : 0
                    newVal = currentVal - $(this).val()
                }
                var fact_total_tva = $("#fact_total_tva").text()
                $("#fact_total_apres_deduction").text(newVal)
                $("#fact_total_general").text(newVal + parseFloat(fact_total_tva))
                $("#agd_total_facture").text(newVal + parseFloat(fact_total_tva))
                $("#agd_total_restant").text(newVal + parseFloat(fact_total_tva)) 
                $("#agd_val_total_restant").val(newVal + parseFloat(fact_total_tva))
                $(".fact_enr_total_general").val(newVal + parseFloat(fact_total_tva)) ;

                var lettreTotal = NumberToLetter(newVal + parseFloat(fact_total_tva))
                $("#fact_somme_lettre").text(lettreTotal) ;
            }
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
            $("#fact_remise_prod_general").keyup()
    })
})