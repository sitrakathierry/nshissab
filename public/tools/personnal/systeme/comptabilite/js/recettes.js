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
        if($(".btn_rct_caisse_jour").hasClass("btn-outline-success"))
        {
            formData.append("refTypePaiement",$(".search_recette_type_paiement.btn-info").data("value")) ;
        }
        else
        {
            formData.append("refTypePaiement","") ;
            formData.append("caisseJournalier","OK") ;
            formData.append("date_caisse_specifique",$("#date_caisse_specifique").val()) ;
        }
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

    $(".btn_rct_caisse_jour").click(function(){
        if($(this).hasClass("btn-outline-success"))
        {
            $(".content_caisse_journalier").before(`
                <div class="col-md-3 content_date_caisse">
                    <label for="date_caisse_specifique" class="font-weight-bold text-uppercase">Date Spécifique</label>
                    <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                    </div>
                        <input type="text" class="form-control" placeholder=". . ." id="date_caisse_specifique" name="date_caisse_specifique">
                    </div>
                </div>
                <script>
                    $("#date_caisse_specifique").datepicker()
                </script>
            `) ;
            $(this).removeClass("btn-outline-success") ;
            $(this).addClass("btn-success") ;
            $(this).html('<i class="fa fa-check"></i>&nbsp;Caisse Journalière') ;

            searchCaisseJournaliere() ;
        }
        else
        {
            $(".content_date_caisse").remove() ;

            $(this).removeClass("btn-success") ;
            $(this).addClass("btn-outline-success") ;
            $(this).html('Caisse Journalière'.toUpperCase()) ;
        }
    }) ;

    function searchCaisseJournaliere()
    {
        var instance = new Loading(files.search) ;
        $(".contentElemRecette").html(instance.otherSearch()) ;
        var formData = new FormData() ;
        for (let j = 0; j < elemSearch.length; j++) {
            const search = elemSearch[j];
            formData.append(search.name,$(search.selector).val());
        }
        formData.append("refTypePaiement","") ;
        formData.append("caisseJournalier","OK") ;
        formData.append("date_caisse_specifique",$("#date_caisse_specifique").val()) ;
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

    $(document).on("change","#date_caisse_specifique",function(){
        searchCaisseJournaliere() ;
    }) ; 

    $(".btn_rct_caisse_pdf").click(function(){
        var self = $(this)
        var realinstance = instance.loading()
        $.ajax({
            url: routes.param_modele_pdf_get,
            type:"post",
            dataType:"html",
            processData:false,
            contentType:false,
            success : function(response){
                realinstance.close()
                $.confirm({
                    title: "Impression Recette Journelière",
                    content:response,
                    type:"blue",
                    theme:"modern",
                    buttons:{
                        btn1:{ 
                            text: 'Annuler',
                            action: function(){}
                        },
                        btn2:{
                            text: 'Imprimer',
                            btnClass: 'btn-blue',
                            keys: ['enter', 'shift'],
                            action: function(){
                                realinstance.close()
                                var idModeleEntete = $("#modele_pdf_entete").val() ;
                                var idModeleBas = $("#modele_pdf_bas").val() ;
                                var dateSpecifique = ($("#date_caisse_specifique").val() == undefined || $("#date_caisse_specifique").val() == "") ? "-" : $("#date_caisse_specifique").val() ;
                                dateSpecifique = appBase.encodeString(dateSpecifique) ;
                                var idEntrepot = $("#search_recette_entrepot").val() == "" ? "-" : $("#search_recette_entrepot").val() ;
                                var url = routes.compta_recette_journalier_imprimer + '/' + dateSpecifique + '/' + idEntrepot + '/' + idModeleEntete + '/' + idModeleBas;
                                window.open(url, '_blank');
                            }
                        }
                    }
                })
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
        return false ;
    }) ;
})