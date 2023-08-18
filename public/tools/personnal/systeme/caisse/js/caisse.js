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
                        optionsPrix += '<option value="'+elem.id+'">'+elem.prixVente+' | '+elem.indice+'</option>'
                    });
                    $("#caisse_search_prix").html(optionsPrix)
                }
                else
                {
                    var optionsPrix = '<option selected value="'+resp.produitPrix[0].id+'">'+resp.produitPrix[0].prixVente+' | '+resp.produitPrix[0].indice+'</option>' ;
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
        $("#caisse_search_quantite").val(1)
        $(".caisse_ajout").click()
    })

    $(document).on('click',".remove_ligne_caisse",function(){
        $(this).closest('tr').remove()
        $(".cs_mtn_recu").keyup()
    })

    $(".caisse_ajout").click(function(){
        var caisse_prix = $("#caisse_search_prix").val()
        var caisse_produit = $("#caisse_search_produit").val()
        var caisse_quantite = $("#caisse_search_quantite").val()
        var caisse_tva = $("#caisse_search_tva").val()
        var caisse_search_image = $("#caisse_search_image").val()

        var result = appBase.verificationElement([
            caisse_produit,
            caisse_prix,
            caisse_quantite,
        ],[
            "Produit",
            "Prix",
            "Quantité",
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

        var produitText = $("#caisse_search_produit").find("option:selected").text();
        var stock = parseInt(produitText.split(" | ")[2].split(" : ")[1])

        if(stock < parseInt(caisse_quantite))
        {
            $.alert({
                title: "Stock insuffisant",
                content: "Veuiller entrer une quantité inférieure au stock",
                type:'red',
            })
            return ;
        }

        var existant = false ;
        $(".csenr_produit").each(function(){
            var idPrix = $(this).closest('tr').find(".csenr_prix").val() ;
            var idProd =  $(this).val() ;

            caisse_prix
            caisse_produit

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

        var prixText = $("#caisse_search_prix").find("option:selected").text()
        var totalPartiel = parseFloat(prixText.split(" | ")[0]) * caisse_quantite ;

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
            <td class="align-middle csenr_total_partiel">`+totalPartiel+`</td>
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
    })

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
            $(elem).find(".csenr_total_partiel").text(totalLigne)
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

        $(".cs_total_general").text(totalGeneral)
        $(".csenr_total_general").val(totalGeneral)

        $(".cs_mtn_tva").text(totalTva)
        $(".csenr_total_tva").val(totalTva)

        $(".cs_mtn_rembourse").text(a_rembourser)
        $(".cs_total_pyee").text(totalPayee)
        $(".cs_mtn_ttc").text(totalTTC)
    }

    $("#formCaisse").submit(function(event){
        event.preventDefault()
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
    ]

    arrayElem.forEach(elem => {
        elem.click(function(){
            elementTo = $(this)
        })
    })

    $(document).on('click',".csenr_quantite",function(){
        elementTo = $(this)
    })

    $(document).on('click',".csenr_tva",function(){
        elementTo = $(this)
    })
    
    $(".caisse_perso_btn").click(function(){
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
    $(".imageHover").hide()
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
})