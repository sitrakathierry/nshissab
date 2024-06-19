$(document).ready(function(){
    function toolTipSelector(parent,element)
    {
        for (let i = 1; i <= $(parent).length ; i++) {
            $(element+i).easyTooltip({
              content: '<div class="text-white font-weight-bold text-uppercase text-center">'+$(element+i).data("content")+'</div>',
              defaultRadius: "3px",
              tooltipFtSize: "12px",
              tooltipZindex: 1000,
              tooltipPadding: "10px 15px",
              tooltipBgColor: "rgba(0,0,0,0.85)",
            })
        }
    }

    $("#search_date").datepicker()
    $("#search_date_debut").datepicker()
    $("#search_date_fin").datepicker()
    
    for (let j = 1; j <= $(".elemMoisDepense").length; j++) {
        toolTipSelector(".elemModePaiement_"+j+" div","#ttpPaiement_"+j+"_") ;
        toolTipSelector(".elemMotif_"+j+" div","#ttpMotif_"+j+"_") ;
        toolTipSelector(".elemDepense_"+j+" tr","#ttpStatut_"+j+"_") ;
    }

    var elemSearch = [
        {
            name: "currentDate",
            action:"change",
            selector : "#search_current_date"
        },
        {
            name: "dateDeclaration",
            action:"change",
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
            name: "anneeDepense",
            action:"keyup",
            selector : ".search_annee"
        },
        {
            name: "anneeDepense",
            action:"change",
            selector : ".search_annee"
        },
        {
            name: "moisDepense",
            action:"change",
            selector : "#search_mois"
        },
        {
            name:"idService",
            action:"change",
            selector:"#search_service"
        },
        {
            name:"element",
            action:"keyup",
            selector:"#search_element"
        }
        ,
        {
            name:"beneficiaire",
            action:"keyup",
            selector:"#search_nom_concerne"
        },
        {
            name:"moisFacture",
            action:"change",
            selector:"#search_mois_facture"
        },
        {
            name:"anneeFacture",
            action:"change",
            selector:".search_annee_facture"
        },
        {
            name:"anneeFacture",
            action:"keyup",
            selector:".search_annee_facture"
        }
    ] 

    function searchDepense()
    {
        var instance = new Loading(files.search) ;
        $("#accordionExample").html(instance.otherSearch()) ;
        var formData = new FormData() ; 
        for (let j = 0; j < elemSearch.length; j++) {
            const search = elemSearch[j];
            formData.append(search.name,$(search.selector).val());
        }
        formData.append("affichage",$("#search_depense").val())
        $.ajax({
            url: routes.compta_depense_search ,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $("#accordionExample").empty().html(response) ;
            }
        })
    }

    elemSearch.forEach(elem => {
        $(document).on(elem.action,elem.selector,function(){
            searchDepense()
        })
    })

    $("#search_depense").change(function(){
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

            searchDepense()
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
        
        searchDepense()

        var currentDate = new Date();
        // var year = currentDate.getFullYear();
        // var month = currentDate.getMonth() + 1; 

        $("#search_current_date").val("")
        $("#search_date").val("")
        $("#search_date_debut").val("")
        $("#search_date_fin").val("")
        $("#search_mois").val("")
        $(".search_annee").val("")

        $(".chosen_select").trigger("chosen:updated")
    })

    $(document).on('mouseenter',".toggleIcon",function(){
        $(this).find("i").addClass("rotateIcon")
    })

    $(document).on("mouseleave",".toggleIcon",function(){
        $(this).find("i").removeClass("rotateIcon")
    })

    $(".vider").click(function(){
        searchDepense()
    })
})