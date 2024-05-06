$(document).ready(function(){
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;
    $(".cs_mtn_recu").val("")
    $(".cs_mtn_remise").val("")
    $(".cs_mtn_type_remise").val(1)
    $("#caisse_search_produit").chosen({no_results_text: "Aucun resultat trouvé : "});
    $("#csenr_date_caisse").datepicker()
    $(".chosen_select").chosen({
        no_results_text: "Aucun resultat trouvé : "
    });
    $(".chosen_select").trigger("chosen:updated")

    $("#caisse_search_produit").chosen().change(function() {
        var idP = $(this).val()
        var self = $(this)
        var realinstance = instance.loading()
        $.ajax({
            url: routes.stock_get_produit_prix,
            type: 'post',
            cache: false,
            data: {idP:idP},
            dataType: 'json',
            success: function(resp){
                realinstance.close()
                if(resp.produitPrix.length > 1)
                {
                    var optionsPrix = '<option value=""></option>' ;
                    resp.produitPrix.forEach(elem => {
                        optionsPrix += '<option value="'+elem.id+'" data-prix="'+elem.prixVente+'" data-indice="'+elem.indice+'">'+elem.prixVente+' | '+elem.indice+'</option>'
                    });
                    $("#caisse_search_prix").html(optionsPrix)
                }
                else
                {
                    var optionsPrix = '<option selected value="'+resp.produitPrix[0].id+'" data-prix="'+resp.produitPrix[0].prixVente+'" data-indice="'+resp.produitPrix[0].indice+'">'+resp.produitPrix[0].prixVente+' | '+resp.produitPrix[0].indice+'</option>' ;
                    $("#caisse_search_prix").html(optionsPrix)
                    $("#caisse_search_prix").change()
                }
                
                // $("#caisse_search_image").val(resp.images)
                $("#caisse_search_prix").trigger("chosen:updated"); 

                if(resp.tva != "")
                {
                    $("#caisse_search_tva").attr("readonly",true)
                    $("#caisse_search_tva").val(resp.tva)
                }
                else
                {
                    $("#caisse_search_tva").removeAttr("readonly")
                    $("#caisse_search_tva").val("")
                }
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp))
            }
        })
    });

    $("#caisse_search_prix").change(function(){
        var self = $(this)
        var caisse_prix = self.val()
        var caisse_produit = $("#caisse_search_produit").val()

        var optionEntrepot = $("#caisse_search_entrepot").find("option:selected") ;

        if(optionEntrepot.val() == "")
        {
            $.alert({
                title: 'Message',
                content: "Veuillez seléctionner un entrepot",
                type: "orange"
            });
            return false ;
        }

        isDifferent = false ;
        $(".elem_caisse tr").each(function(){
            if($(this).find(".csenr_entrepot").val() != optionEntrepot.val())
            {
                isDifferent = true ;
                return false ;
            }
        }) ;

        if(isDifferent)
        {
            $.alert({
                title: 'Entrepot Différent',
                content: "Un seul entrepot pour une facture. Ne changez pas d'entrepot.",
                type:'orange',
            }) ;

            return false ;
        }

        var existant = false ;
        $(".elem_caisse tr").each(function(){
            var idPrix = $(this).find(".csenr_prix").val() ;
            var idProd =  $(this).find(".csenr_produit").val() ;

            if(caisse_produit == idProd && caisse_prix == idPrix)
            {
                existant = true ;
                return ;
            }
        })

        if(existant)
        {
            $.alert({
                title: 'Produit existant',
                content: "Vous ne pouvez pas ajouter ce produit avec ce prix car elle existe déjà ",
                type:'orange',
            })
            return false ;
        }

        $.confirm({
            title: "Quantité Produit",
            content:`
            <div class="w-100 text-left container ">
                <label for="caisse_ajout_produit" class="mt-2 font-weight-bold">Produit</label>
                <input type="text" readonly name="caisse_ajout_produit" id="caisse_ajout_produit" class="form-control bg-white text-primary font-weight-bold" value="`+$("#caisse_search_produit").find('option:selected').text()+`">

                <div class="row">
                    <div class="col-md-7">
                        <label for="caisse_ajout_prix" class="mt-2 font-weight-bold">Prix Unitaire</label>
                        <input type="text" readonly name="caisse_ajout_prix" id="caisse_ajout_prix" class="form-control bg-white text-primary font-weight-bold" value="`+$("#caisse_search_prix").find('option:selected').data("prix")+`">
                    </div>
                    <div class="col-md-5">
                        <label for="caisse_ajout_indice" class="mt-2 font-weight-bold">Indice</label>
                        <input type="text" readonly name="caisse_ajout_indice" id="caisse_ajout_indice" class="form-control bg-white text-primary font-weight-bold" value="`+$("#caisse_search_prix").find('option:selected').data("indice")+`">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="caisse_ajout_quantite" class="mt-2 font-weight-bold">Quantité</label>
                        <input type="number" step="any" name="caisse_ajout_quantite" id="caisse_ajout_quantite" class="form-control" placeholder=". . .">
                    </div>
                    <div class="col-md-6">
                        <label for="" class="mt-2 font-weight-bold">&nbsp;</label>
                        <button class="btn btn-sm btn-block btn_caisse_demi btn-outline-purple text-uppercase"><i class="fa fa-circle-half-stroke" ></i>&nbsp;Vente Demi</button>
                    </div>
                </div>
                <div class="w-100 mt-3 text-center pt-3 barre_dashed">
                    <button type="button" class="btn caisse_perso_qte_btn btn-outline-secondary">1</button>
                    <button type="button" class="btn ml-2 caisse_perso_qte_btn btn-outline-secondary">2</button>
                    <button type="button" class="btn ml-2 caisse_perso_qte_btn btn-outline-secondary">3</button>
                    <button type="button" class="btn ml-2 caisse_perso_qte_btn btn-outline-secondary">4</button><br>
                    <button type="button" class="btn mt-2 caisse_perso_qte_btn btn-outline-secondary">5</button>
                    <button type="button" class="btn mt-2 ml-2 caisse_perso_qte_btn btn-outline-secondary">6</button>
                    <button type="button" class="btn mt-2 ml-2 caisse_perso_qte_btn btn-outline-secondary">7</button>
                    <button type="button" class="btn mt-2 ml-2 caisse_perso_qte_btn btn-outline-secondary">8</button><br>
                    <button type="button" class="btn mt-2 caisse_perso_qte_btn btn-outline-secondary">9</button>
                    <button type="button" value="1" class="btn mt-2 ml-2 caisse_perso_qte_btn btn-outline-secondary">CE</button>
                    <button type="button" class="btn mt-2 ml-2 caisse_perso_qte_btn btn-outline-secondary">0</button>
                    <button type="button" value="0" class="btn mt-2 ml-2 caisse_perso_qte_btn btn-outline-secondary">DEL</button>
                </div>
            </div>
            `,
            type:"blue",
            // theme:"modern",
            buttons:{
                btn1:{
                    text: 'Annuler',
                    action: function(){
                        $('#caisse_search_produit').val("")
                        $('#caisse_search_prix').val("")
                        $('#caisse_search_quantite').val("")
                        $('#caisse_search_tva').val("")
                        $(".chosen_select").trigger("chosen:updated"); 
                    }
                },
                btn2:{
                    text: 'OK',
                    btnClass: 'btn-blue',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var quantiteProduit = $("#caisse_ajout_quantite").val()
                        var venteDemi = $(".btn_caisse_demi").hasClass("btn-purple") ? true : false ;
                        if(quantiteProduit == "")
                        {
                            $.alert({
                                title: 'Message',
                                content: "Quantité non valide",
                                type: "orange"
                            });
                            return false ;
                        }

                        if(parseFloat(quantiteProduit) < 0)
                        {
                            $.alert({
                                title: 'Message',
                                content: "Valeur négatif non autorisé",
                                type: "red"
                            });
                            return false ;
                        }
                    
                        var produitSelected = $("#caisse_search_produit").find("option:selected");
                        var stock = parseFloat(produitSelected.data("stock"))

                        if(stock < parseFloat(quantiteProduit))
                        {
                            $.alert({
                                title: "Stock insuffisant",
                                content: "Veuiller entrer une quantité inférieure au stock",
                                type:'red',
                            })
                            return false ;
                        }

                        if(venteDemi)
                        {
                            var autorise = true ;
                            $(".elem_caisse tr").each(function(){
                                var idProd =  $(this).find(".csenr_produit").val() ;
                    
                                if(caisse_produit == idProd)
                                {
                                    autorise = false ;
                                    return ;
                                }
                            })

                            if(!autorise)
                            {
                                $.alert({
                                    title: "Vente Demi Existant",
                                    content: "Désolé, vente demi non valide. Une vente demi sur ce produit existe déjà sur la liste",
                                    type:'orange',
                                })
                                return false ;
                            }

                        }


                        $("#caisse_search_quantite").val(parseFloat(quantiteProduit)) ;
                        ajoutProduitCaisse(venteDemi)
                    }
                }
            }
        })
        return false ;
    })

    $(document).on("click",".btn_caisse_demi",function(){
        if($(this).hasClass("btn-outline-purple"))
        {
            $(this).addClass("btn-purple") ;
            $(this).removeClass("btn-outline-purple") ;
            $(this).find("i").removeClass("fa-circle-half-stroke")
            $(this).find("i").addClass("fa-check")

            $("#caisse_ajout_quantite").attr("readonly","true")
            $("#caisse_ajout_quantite").val(0.5)
        }
        else
        {
            $(this).addClass("btn-outline-purple") ;
            $(this).removeClass("btn-purple") ;
            $(this).find("i").removeClass("fa-check")
            $(this).find("i").addClass("fa-circle-half-stroke")

            $("#caisse_ajout_quantite").removeAttr("readonly")
            $("#caisse_ajout_quantite").val("")
        }
    })

    $(document).on('click',".remove_ligne_caisse",function(){
        $(this).closest('tr').remove()
        $(".cs_mtn_recu").keyup()
    })

    function limiterA2Decimales(nombre) {
        return nombre.toFixed(2).replace(/\.0*$/, '');
      }

    function ajoutProduitCaisse(venteDemi){
        var caisse_produit = $("#caisse_search_produit").val()
        var caisse_prix = $("#caisse_search_prix").val()
        var caisse_quantite = $("#caisse_search_quantite").val()
        var caisse_tva = $("#caisse_search_tva").val()

        var caisse_search_image = $("#caisse_search_image").val()

        var existant = false ;
        $(".csenr_produit").each(function(){
            var idPrix = $(this).closest('tr').find(".csenr_prix").val() ;
            var idProd =  $(this).val() ;

            if(caisse_produit == idProd && caisse_prix == idPrix)
            {
                existant = true ;
                return
            }
        })

        if(existant)
        {
            $.alert({
                title: 'Produit existant',
                content: "Vous ne pouvez pas ajouter ce produit avec ce prix car elle existe déjà ",
                type:'orange',
            })
            return 
        }
        
        var optionEntrepot = $("#caisse_search_entrepot").find("option:selected") ;
        var produitText = $("#caisse_search_produit").find("option:selected").text();
        var prixText = $("#caisse_search_prix").find("option:selected").text()
        var totalPartiel = parseFloat(prixText.split(" | ")[0]) * caisse_quantite ;
        // var valDemi = venteDemi ? ' | <span class="font-weight-bold text-info">Demi : '+(totalPartiel / 2)+' </span>' : ""
        // console.log(valDemi)
        var tvaVal = 0 

        if(caisse_tva != "")
        {
            tvaVal = ((parseFloat(prixText.split(" | ")[0]) * parseFloat(caisse_tva)) / 100) * caisse_quantite ;
        }
        
        var item = `
        <tr>
            <td class="align-middle">
                `+produitText+`
                <input type="hidden" class="text_produit" value="`+produitText+`">
                <input type="hidden" class="csenr_entrepot" name="csenr_entrepot[]" value="`+optionEntrepot.val()+`">
                <input type="hidden" class="csenr_produit" name="csenr_produit[]" value="`+caisse_produit+`">
            </td>
            <td class="align-middle overflow-auto">
                <div class="codeBarre"></div>
            </td>
            <td class="align-middle">
                `+prixText+`
                <input type="hidden" class="csenr_prix" name="csenr_prix[]" value="`+caisse_prix+`">
                <input type="hidden" name="csenr_prixText[]" id="csenr_prixText" value="`+prixText+`">

            </td>
            <td class="align-middle text-center">
                <input type="number" step="any" name="csenr_quantite[]" class="csenr_quantite form-control" value="`+caisse_quantite+`">
            </td>
            <td class="align-middle">
                <input type="number" step="any" name="csenr_tva[]" `+(caisse_tva == "" ? "" : "readonly")+` class="csenr_tva form-control" value="`+(caisse_tva == "" ? 0 : caisse_tva)+`">
            </td>
            <td class="align-middle csenr_total_partiel">`+limiterA2Decimales(totalPartiel)+`</td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm remove_ligne_caisse font-smaller btn-outline-danger"><i class="fa fa-times"></i></button>
            </td>
        </tr>
        `
        
        $(".elem_caisse").append(item)

        $(".elem_caisse tr").each(function(index,element){
            $(element).find(".codeBarre").barcode(
                {
                    code:$(element).find('.text_produit').val().split(" | ")[0],
                    rect: false,
                },
                "code128",
                {
                    output: "svg",
                    bgColor: "transparent",
                    barHeight: 20,
                }
            );
        })

        $('#caisse_search_produit').val("")
        $('#caisse_search_prix').val("")
        $('#caisse_search_quantite').val("")
        $('#caisse_search_tva').val("")
        $(".chosen_select").trigger("chosen:updated"); 

        $(".cs_mtn_recu").keyup()
    }

    function updateMontant()
    {
        var totalTva = 0 ;
        var totalGeneral = 0 ;
        var cs_mtn_type_remise = $("#cs_mtn_type_remise").val()
        var cs_mtn_remise = $("#cs_mtn_remise").val() == "" ? 0 : parseFloat($("#cs_mtn_remise").val()) ;

        $(".elem_caisse tr").each(function(index,elem){
            var totalLigne = 0 ;
            var prixLigne = $(elem).find("#csenr_prixText").val().split(" | ")[0]
            var quantiteLigne = $(elem).find(".csenr_quantite").val()
            var tvaLigne = $(elem).find(".csenr_tva").val() == "" ? 0 : $(elem).find(".csenr_tva").val()

            quantiteLigne = quantiteLigne == "" ? 0 : quantiteLigne
            totalTva += ((parseFloat(prixLigne) * parseFloat(tvaLigne)) / 100) * quantiteLigne ;
            totalGeneral += (prixLigne * quantiteLigne)
            totalLigne = prixLigne * quantiteLigne
            // $(elem).find(".csenr_total_partiel").text(totalLigne)
        })

        var montantRecu = $(".cs_mtn_recu").val()
        montantRecu = montantRecu == "" ? 0 : parseFloat(montantRecu) ;
        var remiseValeur = cs_mtn_type_remise == 1 ? cs_mtn_remise : ((cs_mtn_remise * totalGeneral)/100)
        var a_rembourser = montantRecu - parseFloat(totalGeneral) + remiseValeur ; 

        if(a_rembourser < 0)
        {
            $(".cs_mtn_rembourse").addClass("text-danger")
            $(".cs_mtn_rembourse").removeClass("text-success")
        }
        else
        {
            $(".cs_mtn_rembourse").addClass("text-success")
            $(".cs_mtn_rembourse").removeClass("text-danger")
        }

        a_rembourser = a_rembourser < 0 ? 0 : a_rembourser ;

        var totalPayee = parseFloat(montantRecu) - parseFloat(a_rembourser);
        var totalTTC = totalPayee + totalTva

        $(".cs_total_general").text(limiterA2Decimales(totalGeneral))
        $(".csenr_total_general").val(limiterA2Decimales(totalGeneral))

        $(".cs_mtn_tva").text(limiterA2Decimales(totalTva))
        $(".csenr_total_tva").val(limiterA2Decimales(totalTva))

        $(".cs_mtn_rembourse").text(limiterA2Decimales(a_rembourser))
        $(".cs_total_pyee").text(limiterA2Decimales(totalPayee))
        $(".cs_mtn_ttc").text(limiterA2Decimales(totalTTC))
    }

    $("#formCaisse").submit(function(event){
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
                        url: routes.caisse_save_activites,
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

    var elementTo = ''

    var arrayElem = [
        $("#caisse_search_quantite"),
        $(".cs_mtn_recu"),
        $("#caisse_search_quantite"),
        $(".cs_mtn_remise"),
        $(".csenr_quantite"),
        $(".csenr_tva")
    ]

    arrayElem.forEach(elem => {
        elem.click(function(){
            elementTo = $(this)
        })
    })

    $(document).on("click",".caisse_perso_btn",function(){
        if(!isNaN($(this).text()))
        {
            var quantite = elementTo.val()
            elementTo.val(quantite+$(this).text())
        }
        else if($(this).attr("value") == 1 )
        {
            elementTo.val("")
        }
        else
        {
            var oldChar = elementTo.val()
            var newChar = oldChar.slice(0, -1);
            elementTo.val(newChar)
        }

        elementTo.keyup()
    })

    $(document).on("click",".caisse_perso_qte_btn",function(){
        if(!isNaN($(this).text()))
        {
            var quantite = $("#caisse_ajout_quantite").val()
            $("#caisse_ajout_quantite").val(quantite+$(this).text())
        }
        else if($(this).attr("value") == 1 )
        {
            $("#caisse_ajout_quantite").val("")
        }
        else
        {
            var oldChar = $("#caisse_ajout_quantite").val()
            var newChar = oldChar.slice(0, -1);
            $("#caisse_ajout_quantite").val(newChar)
        }

        $("#caisse_ajout_quantite").keyup()
    })
    
    var elemAction = [
        {
            action : 'keyup',
            selector: ".csenr_quantite"
        },
        {
            action : 'change',
            selector: ".csenr_quantite"
        },
        {
            action : 'keyup',
            selector: ".csenr_tva"
        },
        {
            action : 'change',
            selector: ".csenr_tva"
        },
        {
            action : 'keyup',
            selector: ".cs_mtn_recu"
        },
        {
            action : 'change',
            selector: ".cs_mtn_recu"
        },
        {
            action : 'keyup',
            selector: ".cs_mtn_remise"
        },
        {
            action : 'change',
            selector: ".cs_mtn_remise"
        },
        {
            action : 'change',
            selector: "#cs_mtn_type_remise"
        }, 
    ]

    elemAction.forEach(function(elem){
        $(document).on(elem.action,elem.selector,function(){
            updateMontant() ; 
        })
    })

    $(".btn_submit_caisse").click(function(){
        $("#formCaisse").submit()
    })
    // $(".imageHover").hide()
    $(document).on("mouseover",'#caisse_search_produit_chosen .chosen-drop .chosen-results li', function(){
        // Obtenez les coordonnées de l'élément
        $(".imageHover").show()
        $(".imageHover").attr("src",files.search) ;

        var topDepart = $(".posDepart").offset().top
        var offset = $(this).offset() ;
        var top = 33 + (offset.top - (topDepart + 33)) - 23
        $(".imageHover").css("top",top+"px") ;
        var codeProduit = $(this).text().split(" | ")[0] ;

        $(".caisse_search_produit option").each(function(index,item){
            if($(item).data("code") == codeProduit)
            {
                $(".imageHover").attr("src",$(item).data("images")) ;
                return ;
            }
        })

        // console.log(offset.top, offset.left) ;
        // console.log($(this).text().split(" | ")[0]) ;
    })

    $(document).on("mouseout",'#caisse_search_produit_chosen .chosen-drop .chosen-results li', function(){
        $(".imageHover").hide()
        $(".imageHover").attr("src",files.imageDefault) ;
        $(".imageHover").css("top","33px") ;
    })


    $("#search_date").datepicker()
    $("#search_date_debut").datepicker()
    $("#search_date_fin").datepicker()

    var elemSearch = [
        {
            name: "currentDate",
            action:"change",
            selector : "#search_current_date"
        },
        {
            name: "dateDeclaration",
            action: "change",
            selector : "#search_date"
        },
        {
            name: "dateDebut",
            action:"change",
            selector : "#search_date_debut"
        },
        {
            name: "dateFin",
            action:"change",
            selector : "#search_date_fin"
        },
        {
            name: "annee",
            action:"keyup",
            selector : ".search_annee"
        },
        {
            name: "annee",
            action:"change",
            selector : ".search_annee"
        },
        {
            name: "mois",
            action:"change",
            selector : "#search_mois"
        },
        {
            name: "numCommande",
            action:"keyup",
            selector : "#search_num_commande"
        },

    ] 

    function searchCaisse()
    {
        var instance = new Loading(files.search) ;
        $(".elem_caisse").html(instance.search(8)) ;
        var formData = new FormData() ;
        for (let j = 0; j < elemSearch.length; j++) {
            const search = elemSearch[j];
            formData.append(search.name,$(search.selector).val());
        }
        formData.append("affichage",$("#search_caisse").val())
        $.ajax({
            url: routes.caisse_vente_search ,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $(".elem_caisse").empty().html(response) ;
            }
        })
    }

    elemSearch.forEach(elem => {
        $(document).on(elem.action,elem.selector,function(){
            searchCaisse()
        })
    })

    $("#search_caisse").change(function(){

        $("#caption_search_date").hide()
        $("#caption_search_date_debut").hide()
        $("#caption_search_date_fin").hide()
        $("#caption_search_mois").hide()
        $("#caption_search_annee").hide()

        if($(this).val() == "JOUR")
        {
            var currentDate = new Date();
            var day = currentDate.getDate();
            var month = currentDate.getMonth() + 1; // Les mois sont indexés à partir de zéro, donc nous ajoutons 1
            var year = currentDate.getFullYear();
            if (month < 10) {
                month = '0' + month;
                }
            var formattedDate = day + '/' + month + '/' + year;

            $("#search_current_date").val(formattedDate)

        }
        else if($(this).val() == "SPEC")
        {
            $("#caption_search_date").show()
        }
        else if($(this).val() == "LIMIT")
        {
            $("#caption_search_date_debut").show()
            $("#caption_search_date_fin").show()
        }
        else if($(this).val() == "MOIS")
        {
            var currentDate = new Date();
            var month = currentDate.getMonth() + 1; 
            $("#search_mois").val(month)

            $("#caption_search_annee").show()
            $("#caption_search_mois").show()

            $(".chosen_select").trigger("chosen:updated")
        }
        
        searchCaisse()

        var currentDate = new Date();
        var year = currentDate.getFullYear();
        var month = currentDate.getMonth() + 1; 

        $("#search_date").val("")
        $("#search_date_debut").val("")
        $("#search_date_fin").val("")
        $("#search_mois").val(month)
        $(".search_annee").val(year)

        $(".chosen_select").trigger("chosen:updated")
    })

    $(".vider").click(function(){
        searchCaisse()
    })

    $("#caisse_search_entrepot").change(function(){
        var realinstance = instance.loading() ;
        var self = $(this) ;
        $.ajax({
            url: routes.stock_find_produit_in_entrepot,
            type:'post',
            cache: false,
            data: {idE:self.val()} ,
            dataType: 'json',
            success: function(response){
                realinstance.close() ;
                var images = `data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEBLAEsAAD/4QBWRXhpZgAATU0AKgAAAAgABAEaAAUAAAABAAAAPgEbAAUAAAABAAAARgEoAAMAAAABAAIAAAITAAMAAAABAAEAAAAAAAAAAAEsAAAAAQAAASwAAAAB/+0ALFBob3Rvc2hvcCAzLjAAOEJJTQQEAAAAAAAPHAFaAAMbJUccAQAAAgAEAP/hDIFodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvADw/eHBhY2tldCBiZWdpbj0n77u/JyBpZD0nVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkJz8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0nYWRvYmU6bnM6bWV0YS8nIHg6eG1wdGs9J0ltYWdlOjpFeGlmVG9vbCAxMS44OCc+CjxyZGY6UkRGIHhtbG5zOnJkZj0naHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyc+CgogPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9JycKICB4bWxuczp0aWZmPSdodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyc+CiAgPHRpZmY6UmVzb2x1dGlvblVuaXQ+MjwvdGlmZjpSZXNvbHV0aW9uVW5pdD4KICA8dGlmZjpYUmVzb2x1dGlvbj4zMDAvMTwvdGlmZjpYUmVzb2x1dGlvbj4KICA8dGlmZjpZUmVzb2x1dGlvbj4zMDAvMTwvdGlmZjpZUmVzb2x1dGlvbj4KIDwvcmRmOkRlc2NyaXB0aW9uPgoKIDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PScnCiAgeG1sbnM6eG1wTU09J2h0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8nPgogIDx4bXBNTTpEb2N1bWVudElEPmFkb2JlOmRvY2lkOnN0b2NrOjRiYTRmZGU3LTVlZTItNGFhNC05NjA4LWE3YzFkZWY2MDIwMjwveG1wTU06RG9jdW1lbnRJRD4KICA8eG1wTU06SW5zdGFuY2VJRD54bXAuaWlkOjAyNWY4YWIxLTg4ZGQtNDlhYS1iMTc4LTExOTcxMzdmNTQ0MjwveG1wTU06SW5zdGFuY2VJRD4KIDwvcmRmOkRlc2NyaXB0aW9uPgo8L3JkZjpSREY+CjwveDp4bXBtZXRhPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAo8P3hwYWNrZXQgZW5kPSd3Jz8+/9sAQwAFAwQEBAMFBAQEBQUFBgcMCAcHBwcPCwsJDBEPEhIRDxERExYcFxMUGhURERghGBodHR8fHxMXIiQiHiQcHh8e/9sAQwEFBQUHBgcOCAgOHhQRFB4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4e/8AAEQgBaAHgAwERAAIRAQMRAf/EAB0AAQADAQEBAQEBAAAAAAAAAAABBwgGBQMEAgn/xABJEAABAwICBAcNBgUDAwUAAAAAAQIDBAUGEQchMUEIElFWdZPRExQXGCI2N1RhcZWz0jJC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AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAlAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACUAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJQABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAlAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACUAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJQABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAlAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACUAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJQABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAlAP/Z` ;
                var options = '<option value="">-</option>'
                for (let i = 0; i < response.produitEntrepots.length; i++) {
                    const elementP = response.produitEntrepots[i];

                    images = elementP.images == "-" ? images : elementP.images ;

                    options += '<option value="'+elementP.idP+'" data-stock="'+elementP.stock+'" data-code="'+elementP.codeProduit+'" data-images="'+images+'" >'+elementP.codeProduit+' | '+elementP.nomType+' | '+elementP.nom+' | stock : '+elementP.stock+'</option>'
                }

                $("#caisse_search_produit").html(options) ;
                $(".chosen_select").trigger("chosen:updated"); 
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    }) ;
})