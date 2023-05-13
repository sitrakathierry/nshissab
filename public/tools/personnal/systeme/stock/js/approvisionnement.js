$(document).ready(function(){
    // APPRO 
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;
    var produitEntrepots = $("#appro_search_produit").html() ;
    $( "#appro_expireeLe").datepicker();
    $("#enr_appro_date").datepicker();

    var recordArray = [
        "#appro_search_entrepot",
        "#appro_search_produit",
        "#appro_prix_produit",
        "#appro_indice",
        "#appro_quantite",
        "#appro_prix_achat",
        "#appro_charge",
        "#appro_calcul",
        "#appro_marge",
        "#appro_prix_revient",
        "#appro_fournisseur",
        "#appro_expireeLe",
        "#appro_prix_vente",
        ".appro_montant_total"
    ]

    recordArray.forEach(elem => {
        $(elem).val("")
        $(elem).trigger("chosen:updated"); 
    })
    
    $("#appro_search_entrepot").chosen({no_results_text: "Aucun resultat trouvé : "});
    $("#appro_search_produit").chosen({no_results_text: "Aucun resultat trouvé : "});
    $("#appro_fournisseur").chosen({no_results_text: "Aucun resultat trouvé : "});

    function deleteLigneAppro()
    {
        $(".delete_appro").click(function(){
            if(!$(this).attr("disabled"))
            {
                var totalPartiel = $(this).closest('tr').find(".enr_appro_montant_total").text()
                var appro_total_general = $("#appro_total_general").text()
                var resultat = parseFloat(appro_total_general) - parseFloat(totalPartiel) 
                $("#appro_total_general").text(resultat)
            }
            $(this).attr("disabled","true")
            $(this).closest('tr').remove()
        })
    }

    deleteLigneAppro()

    $(".appr_ajout").click(function(){
        var self = $(this)
        $(".appro_caption").text($(this).attr("caption"))
        var recordArray = [
            "#appro_search_entrepot",
            "#appro_search_produit",
            "#appro_prix_produit",
            "#appro_indice",
            "#appro_quantite",
            "#appro_prix_achat",
            "#appro_charge",
            "#appro_calcul",
            "#appro_marge",
            "#appro_prix_revient",
            "#appro_fournisseur",
            "#appro_expireeLe",
            "#appro_prix_vente",
            ".appro_montant_total"
        ]

        recordArray.forEach(elem => {
            $(elem).val("")
            $(elem).trigger("chosen:updated"); 
        })
        if($(this).attr("caption") == "Existant" )
        {
            approSearchEntrepot()
            $("#appro_prix_produit").removeAttr("disabled")
            // $("#appro_indice").attr("disabled")
        }
        else
        {
            $("#appro_prix_produit").attr("disabled","true")
            $("#appro_indice").removeAttr("readonly")

            var setNumberArray = [
                "#appro_prix_achat",
                "#appro_charge",
                "#appro_marge",
                "#appro_prix_revient",
                "#appro_prix_vente"
            ]

            setNumberArray.forEach(elem => {
                $(elem).removeAttr("readonly")
            })

            $("#appro_search_produit").html(produitEntrepots) ; 
            $("#appro_search_produit").trigger("chosen:updated") ;
        }
        var btnClass = $(this).data("class")
        var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)
        $(".appr_ajout").each(function(){
            if (!self.is($(this))) {
                var btnClass = $(this).data("class")
                var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
            }
        })
    })

    $(".appro_ajoute_elem").click(function(){
        // var appro_fournisseur = $("#appro_fournisseur").val()
        // if(appro_fournisseur.length == 0)
        // {
        //     $.alert({
        //         title: 'Fournisseur vide',
        //         content: "Veuillez séléctionner au moins un fournisseur",
        //         type:'orange',
        //     })
        //     return false;
        // }

        var numberArray = [
            "#appro_prix_achat",
            "#appro_charge",
            "#appro_marge",
            "#appro_quantite"
        ]

        var numberCaption = 
        [
            "Prix Achat",
            "Charge",
            "Marge",
            "Quantite",
        ]

        var vide = false ;
        var negatif = false ;
        var n = 0 ;
        var caption = "" ;
        numberArray.forEach(elem => {
            const item = $(elem).val()
            if(item == "")
            {
                caption = numberCaption[n] ;
                vide = true ;
                return 
            }
            else if(parseFloat(item) < 0)
            {
                caption = numberCaption[n] ;
                negatif = true ;
                return 
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
            return false;
        }
        else if(negatif)
        {
            $.alert({
                title: 'Valeur négatif',
                content: caption+" doit être positif ",
                type:'red',
            })
            return false;
        }

        var appro_entrepot_text = $("#appro_entrepot_text").val()
        var ref_appro_entrepot = $("#appro_entrepot_text").attr("ref")
        var appro_type = $(".appro_caption").text()
        var appro_produit_text = $("#appro_produit_text").val()
        var ref_appro_produit = $("#appro_produit_text").attr("ref")
        var appro_indice = $("#appro_indice").val() == "" ? "-" : $("#appro_indice").val()
        var appro_quantite = $("#appro_quantite").val()
        var appro_fournisseur = $("#appro_fournisseur").val()
        appro_fournisseur = appro_fournisseur.filter(function(element) {
            return element !== ""; // Retourne true si l'élément n'est pas une chaîne vide
        });
        var appro_expireeLe = $("#appro_expireeLe").val() ;
        var appro_prix_achat = $("#appro_prix_achat").val()
        var appro_charge = $("#appro_charge").val()
        var appro_calcul = $("#appro_calcul").val()
        var appro_marge = $("#appro_marge").val()
        var appro_prix_revient = $("#appro_prix_revient").val()
        var appro_prix_vente = $("#appro_prix_vente").val()
        var appro_montant_total = $("#appro_montant_total").val()
        var appro_montant_total = $(".appro_montant_total").val()
        
        var item = `
                <tr>
                    <td class="align-middle">
                        <input type="hidden" name="enr_ref_entrepot[]" class="enr_ref_entrepot" value="`+ref_appro_entrepot+`">
                        `+appro_entrepot_text+`
                    </td>
                    <td class="align-middle">
                        `+appro_type+`
                        <input type="hidden" name="enr_appro_type[]" class="enr_appro_type" value="`+appro_type+`">
                    </td>
                    <td class="align-middle">
                        <input type="hidden" name="enr_ref_appro_produit[]" class="enr_ref_appro_produit" value="`+ref_appro_produit+`">
                        `+appro_produit_text+`
                    </td>
                    <td class="align-middle">
                        `+appro_indice+`
                        <input type="hidden" name="enr_appro_indice[]" class="enr_appro_indice" value="`+(appro_indice == "-" ? "" : appro_indice)+`">
                    </td>
                    <td class="align-middle">
                        `+appro_fournisseur.length+`
                        <input type="hidden" name="enr_appro_fournisseur[]" class="enr_appro_fournisseur" value="`+appro_fournisseur+`">
                    </td>
                    <td class="align-middle">
                        `+appro_expireeLe+`
                        <input type="hidden" name="enr_appro_expireeLe[]" class="enr_appro_expireeLe" value="`+appro_expireeLe+`">
                    </td>
                    <td class="align-middle">
                        `+appro_quantite+`
                        <input type="hidden" name="enr_appro_quantite[]" class="enr_appro_quantite" value="`+appro_quantite+`">
                    </td>
                    <td class="align-middle">
                        `+appro_prix_achat+`
                        <input type="hidden" name="enr_appro_prix_achat[]" class="enr_appro_prix_achat" value="`+appro_prix_achat+`">
                    </td>
                    <td class="align-middle">
                        `+appro_charge+`
                        <input type="hidden" name="enr_appro_charge[]" class="enr_appro_charge" value="`+appro_charge+`">
                    </td>
                    <td class="align-middle">
                        `+appro_prix_revient+`
                        <input type="hidden" name="enr_appro_prix_revient[]" class="enr_appro_prix_revient" value="`+appro_prix_revient+`">
                    </td>
                    <td class="align-middle">
                        `+(appro_calcul == 1 ? "Montant" : "%" )+`
                        <input type="hidden" name="enr_appro_calcul[]" class="enr_appro_calcul" value="`+appro_calcul+`">
                    </td>
                    <td class="align-middle">
                        `+appro_marge+`
                        <input type="hidden" name="enr_appro_marge[]" class="enr_appro_marge" value="`+appro_marge+`">
                    </td>
                    <td class="align-middle">
                        `+appro_prix_vente+`
                        <input type="hidden" name="enr_appro_prix_vente[]" class="enr_appro_prix_vente" value="`+appro_prix_vente+`">
                    </td>
                    <td class="text-right align-middle enr_appro_montant_total">`+appro_montant_total+`</td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn-outline-danger delete_appro btn btn-sm font-smaller"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            `
            $("#appro_total_general").text(parseFloat($("#appro_total_general").text())+parseFloat(appro_montant_total))
        $(".elem_appro").append(item) 
        deleteLigneAppro()
        var recordArray = [
            "#appro_search_entrepot",
            "#appro_search_produit",
            "#appro_prix_produit",
            "#appro_indice",
            "#appro_quantite",
            "#appro_prix_achat",
            "#appro_charge",
            "#appro_calcul",
            "#appro_marge",
            "#appro_prix_revient",
            "#appro_fournisseur",
            "#appro_expireeLe",
            "#appro_prix_vente",
            ".appro_montant_total"
        ]

        recordArray.forEach(elem => {
            $(elem).val("")
            $(elem).trigger("chosen:updated"); 
        })
    })

    // submit appro 
    $("#formAppro").submit(function(event){
        event.preventDefault()
        var data = $(this).serialize();
        $.confirm({
            title: "Confirmation",
            content:"Vous êtes sûre de vouloir enregistrer ?",
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
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.stock_save_approvisionnement,
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

    // debout out 

    $(".appro_search_produit").chosen().change(function() {
        var selectedText = $(this).find("option:selected").text();
        var ref = $(this).val()
        $("#appro_produit_text").val(selectedText) 
        $("#appro_produit_text").attr("ref", ref) 
    });

    function approSearchEntrepot()
    {
        $("#appro_search_entrepot").change(function(){
            var self = $(this)
            var selectedText = $(this).find("option:selected").text();
            var ref = $(this).val()
            $("#appro_entrepot_text").val(selectedText)
            $("#appro_entrepot_text").attr("ref", ref)
            if($(".appro_caption").text() == "Default")
            {
                $.alert({
                    title:"Type non défini",
                    content:"Veuiller sélectionner un type d'approvisionnemnt",
                    type:"orange"
                })
                self.val("")
                self.trigger("chosen:updated");
                return false ;
            }

            if($(".appro_caption").text() != "Existant")
                return false ;

            var realinstance = instance.loading()
            $.ajax({
                url: routes.stock_find_produit_in_entrepot,
                type:'post',
                cache: false,
                data: {idE:self.val()} ,
                dataType:'json',
                success: function(resp)
                {
                    realinstance.close()
                    if(resp.vide)
                    {
                        $.confirm({
                            title: "Entrepot vide",
                            content:"Voulez-vous ajouter de nouveau produit dans cet entrepôt ?",
                            type:"blue",
                            theme:"modern",
                            buttons:{
                                btn1:{
                                    text: 'Non',
                                    action: function(){
                                        $("#appro_search_produit").html('<option value=""></option>')
                                        $("#appro_search_produit").trigger("chosen:updated"); 
                                        $.alert({
                                            title:"Entrepot",
                                            content:"Veuiller changer d'entrepot",
                                            type:"orange"
                                        })
                                    }
                                },
                                btn2:{
                                    text: 'Oui',
                                    btnClass: 'btn-blue',
                                    keys: ['enter', 'shift'],
                                    action: function(){
                                        $("#appro_indice").removeAttr("readonly") ;
                                        $(".appro_caption").text("Nouveau")
                                        $("#appro_prix_produit").attr("disabled","true")
                                        $("#appro_search_produit").html(produitEntrepots)
                                        $("#appro_search_produit").trigger("chosen:updated"); 
                                    }
                                }
                            }
                        })
                    }
                    else
                    {
                        $("#appro_indice").attr("readonly","true") ;
                        $(".appro_caption").text("Existant")
                        $("#appro_prix_produit").removeAttr("disabled")
                        
                        var options = '<option value=""></option>'
                        for (let i = 0; i < resp.produitEntrepots.length; i++) {
                            const elementP = resp.produitEntrepots[i];
                            options += '<option value="'+elementP.id+'">'+elementP.codeProduit+' | '+elementP.nom+' | stock : '+elementP.stock+'</option>'
                        }

                        $("#appro_search_produit").html(options) ;
                        $("#appro_search_produit").trigger("chosen:updated"); 
                    }
                }
            })
        })
    }
    approSearchEntrepot()

    $("#appro_search_produit").change(function(){
        var appro_caption = $(".appro_caption").text()
        if(appro_caption == "Existant" )
        {
            var realinstance = instance.loading()
            var idE = $("#appro_search_entrepot").val()
            var idP = $(this).val()
            $.ajax({
                url: routes.stock_get_prix_produitE, 
                type:'post',
                cache:false,
                data:{idE:idE,idP:idP},
                dataType:'json',
                success: function(resp){
                    realinstance.close()
                    if(resp.length > 1)
                    {
                        var optionsPrix = '<option value=""></option>'
                        for (let i = 0; i < resp.length; i++) {
                            const element = resp[i];
                            optionsPrix += '<option value="'+element.id+'">'+element.prixVente+' | '+element.indice+'</option>'
                        }
                        $("#appro_prix_produit").html(optionsPrix)
                    }
                    else
                    {
                        var optionsPrix = '<option value="'+resp[0].id+'">'+resp[0].prixVente+' | '+resp[0].indice+'</option>' ;
                        $("#appro_prix_produit").html(optionsPrix)
                        $("#appro_prix_produit").change()
                    }
                    
                }
            })
        }
    })

    $("#appro_prix_produit").change(function(){
        var self = $(this)
        var realinstance = instance.loading()
        $.ajax({
            url: routes.stock_details_variation_prix,
            type:'post',
            cache: false,
            data:{idVar:self.val()},
            dataType:'json',
            success: function(resp){
                realinstance.close()
                var setNumberArray = [
                    "#appro_prix_achat",
                    "#appro_charge",
                    "#appro_marge",
                    "#appro_prix_revient",
                    "#appro_prix_vente"
                ]

                setNumberArray.forEach(elem => {
                    $(elem).attr("readonly","true")
                })
                
                var selectorsVar = [
                    "#appro_prix_achat",
                    "#appro_charge",
                    "#appro_marge",
                    "#appro_prix_revient",
                    "#appro_prix_vente",
                    "#appro_fournisseur",
                    "#appro_expireeLe",
                    "#appro_indice",
                    "#appro_calcul"
                ]

                var selectorValue = [
                    resp.prixAchat,
                    resp.charge,
                    resp.marge,
                    resp.prixRevient,
                    resp.prixVente,
                    resp.fournisseur,
                    resp.expireeLe,
                    $("#appro_prix_produit option:selected").text().split(" | ")[1],
                    resp.calcul,
                ]
                
                for (let i = 0; i < selectorsVar.length; i++) {
                    const element = selectorsVar[i];
                    $(element).val(selectorValue[i])
                }

                $("#appro_fournisseur").trigger("chosen:updated"); 
                $(".appro_montant_total").val($("#appro_prix_vente").val())
            }
        })
    })

    $("#appro_fournisseur").change(function(){
        var tableau = $(this).val()
        tableau = tableau.filter(function(element) {
            return element !== ""; // Retourne true si l'élément n'est pas une chaîne vide
        });
        $(this).val(tableau) 
        console.log( $(this).val())
    })

    function approCalculPrix()
    {
        var prix_achat = $("#appro_prix_achat").val() != "" ? $("#appro_prix_achat").val() : 0
        var charge = $("#appro_charge").val() != "" ? $("#appro_charge").val() : 0
        var marge = $("#appro_marge").val() != "" ? $("#appro_marge").val() : 0
        var quantite = $("#appro_quantite").val() != "" ? $("#appro_quantite").val() : 0
        var prix_revient = $("#appro_prix_revient")
        var prix_vente = $("#appro_prix_vente")

        prix_revient.val(parseFloat(prix_achat) + parseFloat(charge))
        prix_vente.val(parseFloat(prix_revient.val()) + parseFloat(marge))

        var total_partiel = parseFloat(quantite) * parseFloat(prix_vente.val())
        $(".appro_montant_total").val(total_partiel)
    }

    var numberArray = [
        "#appro_prix_achat",
        "#appro_charge",
        "#appro_marge",
        "#appro_prix_revient",
        "#appro_prix_vente",
        "#appro_quantite"
    ]
    numberArray.forEach(elem => {
        $(elem).keyup(function(){
            approCalculPrix()
        })

        $(elem).change(function(){
            approCalculPrix()
        })
    })




})