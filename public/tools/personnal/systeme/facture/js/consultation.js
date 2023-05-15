$(document).ready(function(){
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
            name: "mois",
            action:"change",
            selector : "#date_mois"
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
                searchStockEntrepot()
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
        searchStockEntrepot()

    })

    function searchStockEntrepot()
    {
        var instance = new Loading(files.search) ;
        $(".elem_facture").html(instance.search(8)) ;
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
            searchStockEntrepot()
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
})