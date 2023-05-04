$(document).ready(function(){
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;
    $("#caisse_search_produit").chosen({no_results_text: "Aucun resultat trouvé : "});

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
                var optionsPrix = '<option value=""></option>' ;
                resp.forEach(elem => {
                    optionsPrix += '<option value="'+elem.id+'">'+elem.prixVente+' | '+elem.indice+'</option>'
                });
                
                $("#caisse_search_prix").html(optionsPrix)
                $("#caisse_search_prix").trigger("chosen:updated"); 

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
    $(".caisse_ajout").click(function(){
        var caisse_prix = $("#caisse_search_prix").val()
        var caisse_produit = $("#caisse_search_produit").val()
        var caisse_quantite = $("#caisse_search_quantite").val()
        var totalPartiel = parseFloat(prixText.split(" | ")[0]) * caisse_quantite ;

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

        var item = `
        <tr>
            <td class="align-middle">
                `+prodtuitText+`
                <input type="hidden" name="csenr_produit[]" value="`+caisse_produit+`">
            </td>
            <td class="align-middle">
                code
            </td>
            <td class="align-middle">
                `+prixText+`
                <input type="hidden" name="csenr_prix[]" value="`+caisse_prix+`">
            </td>
            <td class="align-middle">
                `+caisse_quantite+`
                <input type="hidden" name="csenr_quantite[]" value="`+caisse_quantite+`">
            </td>
            <td class="align-middle">`+totalPartiel+`</td>
            <td class="text-center align-middle">
                <button class="btn btn-sm remove_ligne_caisse font-smaller btn-outline-danger"><i class="fa fa-times"></i></button>
            </td>
        </tr>
        `
        $(".elem_caisse").append(item)
        elemSearch.forEach(elem => {
            elem.val("")
            elem.trigger("chosen:updated"); 
        })

        totalGeneral += totalPartiel
        $(".cs_total_general").text(totalGeneral)
    })

    $(".cs_mtn_recu").keyup(function(){
        var a_rembourser = parseFloat($(this).val()) - totalGeneral ; 

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
        $(".cs_total_pyee").addClass("text-primary")
        $(".cs_total_pyee").text(parseFloat($(this).val()) - a_rembourser)
    })

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
})