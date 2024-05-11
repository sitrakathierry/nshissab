$(document).ready(function(){
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;
    var elemSearch = [
        {
            name: "idT",
            action:"change",
            selector : "#fact_search_type"
        },
        {
            name: "idM",
            action:"change",
            selector : "#fact_search_modele"
        },
        {
            name: "id",
            action:"change",
            selector : "#fact_search_num"
        },
        {
            name: "idC",
            action:"change",
            selector : "#fact_search_client"
        },
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
            name: "idEntrepot",
            action:"change",
            selector : "#fact_search_entrepôt"
        }
    ] 
    
    $("#fact_search_date").change(function(){
        var option = $(this).find("option:selected") ;
        var critere = option.data("critere") ;
        if(critere == "")
        {
            $(".elem_date").html("")
            if(option.text() == "TOUS")
            {
                searchFacture()
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
        searchFacture()
    })

    function searchFacture()
    {
        var instance = new Loading(files.search) ;
        $(".elem_facture").html(instance.search(9)) ;
        var formData = new FormData() ;
        for (let j = 0; j < elemSearch.length; j++) {
            const search = elemSearch[j];
            formData.append(search.name,$(search.selector).val());
        }

        $.ajax({
            url: routes.facture_search_items , 
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $(".elem_facture").html(response) ;
            }
        })
    }

    elemSearch.forEach(elem => {
        $(document).on(elem.action,elem.selector,function(){
            searchFacture()
        })
    })

    $(".fact_search_btn_type").click(function(){
        var btnClass = $(this).data("class")
            var target = $(this).data("target")
            var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
            var inputValue = $(this).attr("value")
            var self = $(this)

            $(target).val(inputValue) ;

            $(this).addClass(btnClass)
            $(this).removeClass(currentbtnClass)

            $(".fact_search_btn_type").each(function(){
                if (!self.is($(this))) {
                    $(this).addClass(currentbtnClass) ; 
                    $(this).removeClass(btnClass);
                }
            })

            $(target).change()
    })

    $(".fact_search_btn_modele").click(function(){
        if($(this).data("reference") == "PLOC")
        {
            
            var realinstance = instance.loading()
            $.ajax({
                url: routes.fact_list_prest_location_get ,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#contentFacture").html(response) ;
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
            return false ;
        }
        var btnClass = $(this).data("class")
        var target = $(this).data("target")
        var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
        var inputValue = $(this).attr("value")
        var self = $(this)

        $(target).val(inputValue) ;

        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)

        $(".fact_search_btn_modele").each(function(){
            if (!self.is($(this))) {
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
            }
        })

        $(target).change()
    })

    var elemforSearch = [
        {
            name: "dateDebut",
            action:"change",
            selector : "#location_search_dateDebut"
        },
        {
            name: "dateFin",
            action:"change",
            selector : "#location_search_dateFin"
        },
        {
            name: "id",
            action:"change",
            selector : "#location_search_numContrat"
        },
        {
            name: "bailleurId",
            action:"change",
            selector : "#location_search_bailleur"
        },
        {
            name: "bailId",
            action:"change",
            selector : "#location_search_bail"
        },
        {
            name: "locataireId",
            action:"change",
            selector : "#location_search_locataire"
        },
        {
            name: "refStatut",
            action:"change",
            selector : "#location_search_statut"
        },
        {
            name: "dateContrat",
            action:"change",
            selector : "#location_search_dateContrat"
        },
    ]

    function searchContrat()
    {
        var instance = new Loading(files.search) ;
        $(".elem_contrat").html(instance.search(10)) ;
        var formData = new FormData() ;
        for (let j = 0; j < elemforSearch.length; j++) {
            const search = elemforSearch[j];
            formData.append(search.name,$(search.selector).val());
        }
        formData.append("typeSearch","FACTURE") ;
        $.ajax({
            url: routes.prest_location_contrat_search_items ,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, 
            contentType: false, 
            success: function(response){
                $(".elem_contrat").html(response) ;
            }
        })
    }

    elemforSearch.forEach(elem => {
        $(document).on(elem.action,elem.selector,function(){
            searchContrat()
        })
    })
})