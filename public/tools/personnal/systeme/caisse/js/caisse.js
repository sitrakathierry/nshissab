$(document).ready(function(){
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;
    $("#caisse_search_produit").chosen({no_results_text: "Aucun resultat trouvé : "});
    $("#csenr_date_caisse").datepicker()
    $(".chosen_select").chosen({
        no_results_text: "Aucun resultat trouvé : "
    });

    var prodtuitText = '' ;

    $("#caisse_search_produit").chosen().change(function() {
        var selectedText = $(this).find("option:selected").text();
        prodtuitText = selectedText
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
                    $("#caisse_search_prix").trigger("chosen:updated"); 
                }
                else
                {
                    var optionsPrix = '<option value="'+resp.produitPrix[0].id+'">'+resp.produitPrix[0].prixVente+' | '+resp.produitPrix[0].indice+'</option>' ;
                    $("#caisse_search_prix").html(optionsPrix)
                    $("#caisse_search_prix").trigger("chosen:updated"); 
                    $("#caisse_search_prix").change()
                }

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

                // var optionsPrix = '<option value=""></option>' ;
                // resp.forEach(elem => {
                //     optionsPrix += '<option value="'+elem.id+'">'+elem.prixVente+' | '+elem.indice+'</option>'
                // });
                
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp))
            }
        })
      });

      var prixText = ''
      $("#caisse_search_prix").chosen().change(function(){
        var selectedText = $(this).find("option:selected").text();
        prixText = selectedText ;
      })
    var totalGeneral = 0
    var totalTva = 0
    function removeLigneCaisse()
    {
        $(".remove_ligne_caisse").click(function(){
            if(!$(this).attr("disabled"))
            {
                var totalPartiel = $(this).closest('tr').find(".csenr_total_partiel").text() ;
                totalGeneral = parseFloat(totalGeneral) - parseFloat(totalPartiel) ;
                
                var valeur_tva =  $(this).closest('tr').find(".csenr_tva").val() ;
                totalTva = parseFloat(totalTva) - parseFloat(valeur_tva) ;

                $(".cs_total_general").text(totalGeneral)
                $(".csenr_total_general").val(totalGeneral)
                
                $(".cs_mtn_tva").text(totalTva)
                $(".csenr_total_tva").val(totalTva)

                $(".cs_mtn_recu").keyup()
            }
            $(this).prop("disabled", true);
            $(this).closest('tr').remove()
        })
    }

    $(".caisse_ajout").click(function(){
        var caisse_prix = $("#caisse_search_prix").val()
        var caisse_produit = $("#caisse_search_produit").val()
        var caisse_quantite = $("#caisse_search_quantite").val()
        var caisse_tva = $("#caisse_search_tva").val()

        var elemSearch = [
            $("#caisse_search_produit"),
            $("#caisse_search_prix"),
            $("#caisse_search_quantite")
        ]

        var elemCaption = [
            "Produit",
            "Prix",
            "Quantité"
        ]
        var n = 0 
        var caption = ''
        var vide = false ;
        elemSearch.forEach(elem => {
            if(elem.val() == "")
            {
                caption = elemCaption[n] ; 
                vide = true ;
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
            return ;
        }

        if(parseFloat($("#caisse_search_quantite").val()) < 0)
        {
            $.alert({
                title: 'Valeur négatif',
                content: "Quantité doit être positif ",
                type:'red',
            })
            return ;
        }
        else if(parseFloat($("#caisse_search_quantite").val()) == 0)
        {
            $.alert({
                title: 'Valeur nul',
                content: "Quantité doit être valide",
                type:'orange',
            })
            return ;
        }

        var stock = parseInt(prodtuitText.split(" | ")[2].split(" : ")[1])
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
        var totalPartiel = parseFloat(prixText.split(" | ")[0]) * caisse_quantite ;
        var tvaVal = 0 
        if(caisse_tva != "")
        {
            tvaVal = ((parseFloat(prixText.split(" | ")[0]) * parseFloat(caisse_tva)) / 100) * caisse_quantite ;
        }
        
        
        var item = `
        <tr>
            <td class="align-middle">
                `+prodtuitText+`
                <input type="hidden" class="csenr_produit" name="csenr_produit[]" value="`+caisse_produit+`">
            </td>
            <td class="align-middle">
                code
            </td>
            <td class="align-middle">
                `+caisse_quantite+`
                <input type="hidden" name="csenr_quantite[]" value="`+caisse_quantite+`">
            </td>
            <td class="align-middle">
                `+prixText+`
                <input type="hidden" class="csenr_prix" name="csenr_prix[]" value="`+caisse_prix+`">
                <input type="hidden" name="csenr_prixText[]" value="`+prixText+`">
            </td>
            <td class="align-middle">
                `+(tvaVal != 0 ? tvaVal : "-")+`
                <input type="hidden"  name="csenr_tva[]" class="csenr_tva" value="`+(caisse_tva == "" ? 0 : caisse_tva)+`">
            </td>
            <td class="align-middle csenr_total_partiel">`+totalPartiel+`</td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm remove_ligne_caisse font-smaller btn-outline-danger"><i class="fa fa-times"></i></button>
            </td>
        </tr>
        `
        
        $(".elem_caisse").append(item)
        elemSearch.forEach(elem => {
            elem.val("")
            elem.trigger("chosen:updated"); 
        })
        totalGeneral += totalPartiel
        totalTva += tvaVal
        $(".cs_total_general").text(totalGeneral)
        $(".csenr_total_general").val(totalGeneral)

        $(".cs_mtn_tva").text(totalTva)
        $(".csenr_total_tva").val(totalTva)

        removeLigneCaisse()
        $(".cs_mtn_recu").keyup()
    })

    function updateMontant(selection)
    {
        var a_rembourser = parseFloat(selection.val()) - parseFloat(totalGeneral) ; 
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

        $(".cs_mtn_rembourse").text(a_rembourser)
        $(".cs_total_pyee").addClass("text-warning")
        $(".cs_mtn_ttc").addClass("text-primary")
        var rembourse = a_rembourser < 0 ? 0 : a_rembourser ;
        var totalPayee = parseFloat(selection.val()) - parseFloat(rembourse) ;
        totalPayee = totalPayee + totalTva
        $(".cs_total_pyee").text(totalPayee)
        $(".cs_mtn_ttc").text(totalPayee)
    }

    $(".cs_mtn_recu").keyup(function(){
        updateMontant($(this))
    })

    $(".cs_mtn_recu").val("")
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
                                            $(".cs_mtn_recu").val("")
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
        $("#caisse_search_quantite")
    ]

    arrayElem.forEach(elem => {
        elem.click(function(){
            elementTo = $(this)
        })
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
    

})