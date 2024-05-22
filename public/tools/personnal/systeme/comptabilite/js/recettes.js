$(document).ready(function(){
    $("#search_date").datepicker()
    $("#search_date_debut").datepicker()
    $("#search_date_fin").datepicker()
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;

    var elemSearch = [
        {
            name: "currentDate",
            action:"change",
            selector : "#date_actuel"
        },
        {
            name: "dateFacture",
            action:"change",
            selector : "#date_specifique"
        },
        {
            name: "dateDebut",
            action:"change",
            selector : "#date_fourchette_debut"
        },
        {
            name: "dateFin",
            action:"change",
            selector : "#date_fourchette_fin"
        },
        {
            name: "annee",
            action:"keyup",
            selector : "#date_annee"
        },
        {
            name: "annee",
            action:"change",
            selector : "#date_annee"
        },
        {
            name: "mois",
            action:"change",
            selector : "#date_mois"
        },
        {
            name: "refEntrepot",
            action:"change",
            selector : "#search_recette_entrepot"
        },
        {
            name: "refRecette",
            action:"change",
            selector : "#search_recette_type"
        },
        {
            name: "id",
            action:"change",
            selector : "#search_recette_numero"
        }
    ] ;

    $("#search_recette_date").change(function(){
        var option = $(this).find("option:selected") ;
        var critere = option.data("critere") ;
        if(critere == "")
        {
            $(".elem_date").html("")
            if(option.text() == "TOUS")
            {
                searchRecette()
            }
            else
            {
                var currentDate = new Date();
                var day = currentDate.getDate();
                var month = currentDate.getMonth() + 1; // Les mois sont indexés à partir de zéro, donc nous ajoutons 1
                var year = currentDate.getFullYear();
                if (month < 10) {
                    month = '0' + month;
                  }
                var formattedDate = day + '/' + month + '/' + year;

                $(".elem_date").html(`
                    <input type="hidden" id="date_actuel" name="date_actuel" value="`+formattedDate+`">
                `)
                $("#date_actuel").change();
            }
            return false;
        }

        if(critere.length == 2)
        {
            $(".elem_date").html(appBase.getItemsDate(critere))
        }
        else
        {
            var index = critere.split(",")
            var elements = ''

            index.forEach(elem => {
                elements += appBase.getItemsDate(elem)
            })

            $(".elem_date").html(elements)
            
        }
        searchRecette()
    })


    function searchRecette()
    {
        var instance = new Loading(files.search) ;
        $(".contentElemRecette").html(instance.otherSearch()) ;
        var formData = new FormData() ;
        for (let j = 0; j < elemSearch.length; j++) {
            const search = elemSearch[j];
            formData.append(search.name,$(search.selector).val());
        }
        formData.append("refTypePaiement",$(".search_recette_type_paiement.btn-info").data("value")) ;
        $.ajax({
            url: routes.compta_recette_search ,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $(".contentElemRecette").empty().html(response) ;
            }
        })
    }

    elemSearch.forEach(elem => {
        $(document).on(elem.action,elem.selector,function(){
            searchRecette()
        })
    })

    $(".search_recette_type_paiement").click(function(){
        var self = $(this) ;
        $(this).addClass("btn-info") ;
        $(this).removeClass("btn-outline-info") ;
        $(this).html('<i class="fa fa-check"></i>&nbsp;'+$(this).data("libelle"))

        $(".search_recette_type_paiement").each(function(){
            if (!self.is($(this))) {
                $(this).addClass("btn-outline-info") ; 
                $(this).removeClass("btn-info");
                $(this).html($(this).data("libelle"))
            }
        }) ;

        searchRecette()

    }) ;
})