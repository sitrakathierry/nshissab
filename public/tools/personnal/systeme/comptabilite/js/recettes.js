$(document).ready(function(){
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
        }
    ] 

    function searchRecette()
    {
        var instance = new Loading(files.search) ;
        $(".elemRecette").html(instance.search(5)) ;
        var formData = new FormData() ;
        for (let j = 0; j < elemSearch.length; j++) {
            const search = elemSearch[j];
            formData.append(search.name,$(search.selector).val());
        }
        formData.append("affichage",$("#search_recette").val())
        $.ajax({
            url: routes.compta_recette_search ,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $(".elemRecette").empty().html(response) ;
            }
        })
    }

    elemSearch.forEach(elem => {
        $(document).on(elem.action,elem.selector,function(){
            searchRecette()
        })
    })

    $("#search_recette").change(function(){

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

            searchRecette()
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
        
        searchRecette()

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
})